<?php

namespace App\Services;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use App\Enums\MemberStatus;
use App\Enums\RewardStatus;
use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\LoyaltyRule;
use App\Models\Member;
use App\Models\Reward;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\LoyaltyRewardQualifiedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class LoyaltyService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly SubscriptionService $subscriptions,
    ) {
    }

    public function createRule(array $data, ?int $actorId = null, ?string $ipAddress = null): LoyaltyRule
    {
        $rule = LoyaltyRule::create($data);

        $this->audit->log(
            action: 'loyalty_rule_created',
            entityType: 'loyalty_rule',
            entityId: $rule->id,
            newValues: $rule->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $rule;
    }

    public function updateRule(LoyaltyRule $rule, array $data, ?int $actorId = null, ?string $ipAddress = null): LoyaltyRule
    {
        $before = $rule->replicate();
        $rule->update($data);

        $this->audit->log(
            action: 'loyalty_rule_updated',
            entityType: 'loyalty_rule',
            entityId: $rule->id,
            oldValues: $before->toArray(),
            newValues: $rule->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $rule;
    }

    public function createReward(array $data, ?int $actorId = null, ?string $ipAddress = null): Reward
    {
        return DB::transaction(function () use ($data, $actorId, $ipAddress) {
            $data['status'] = $data['status'] ?? RewardStatus::Pending->value;
            $reward = Reward::create($data);

            if ($reward->status === RewardStatus::Granted) {
                $reward->forceFill(['activated_by' => $actorId])->save();
                $this->applyGrantedReward($reward);
            }

            $this->audit->log(
                action: 'reward_created',
                entityType: 'reward',
                entityId: $reward->id,
                newValues: $reward->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            return $reward;
        });
    }

    public function updateReward(Reward $reward, array $data, ?int $actorId = null, ?string $ipAddress = null): Reward
    {
        return DB::transaction(function () use ($reward, $data, $actorId, $ipAddress) {
            $before = $reward->replicate();
            $wasGranted = $reward->status === RewardStatus::Granted;

            $reward->update($data);

            if (! $wasGranted && $reward->status === RewardStatus::Granted) {
                $reward->forceFill(['activated_by' => $actorId])->save();
                $this->applyGrantedReward($reward);
            }

            $this->audit->log(
                action: 'reward_updated',
                entityType: 'reward',
                entityId: $reward->id,
                oldValues: $before->toArray(),
                newValues: $reward->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            return $reward;
        });
    }

    public function evaluateAllMembers(): int
    {
        $created = 0;

        Member::query()
            ->where('status', MemberStatus::Active->value)
            ->orderBy('id')
            ->each(function (Member $member) use (&$created): void {
                $created += $this->evaluateMemberRewards($member);
            });

        return $created;
    }

    public function evaluateMemberRewards(Member $member): int
    {
        $created = 0;

        $rules = LoyaltyRule::query()
            ->where('is_active', true)
            ->where('trigger_type', '!=', LoyaltyTriggerType::Manual->value)
            ->get();

        foreach ($rules as $rule) {
            if (! $this->memberMatchesRule($member, $rule)) {
                continue;
            }

            $reward = $this->createPendingRewardForRule($member, $rule);

            if (! $reward->wasRecentlyCreated) {
                continue;
            }

            $created++;
            $this->notifyAdminsAboutPendingReward($reward);
        }

        return $created;
    }

    private function createPendingRewardForRule(Member $member, LoyaltyRule $rule): Reward
    {
        return Reward::query()->firstOrCreate(
            [
                'member_id' => $member->id,
                'loyalty_rule_id' => $rule->id,
            ],
            [
                'type' => $rule->reward_type?->value ?? $rule->reward_type,
                'value' => $rule->reward_value,
                'status' => RewardStatus::Pending->value,
                'qualified_at' => now(),
                'notes' => 'Automatically detected. Pending admin activation.',
            ],
        );
    }

    private function memberMatchesRule(Member $member, LoyaltyRule $rule): bool
    {
        return match ($rule->trigger_type) {
            LoyaltyTriggerType::TotalHours => $this->matchesTotalHoursRule($member, $rule),
            LoyaltyTriggerType::SubscriptionMonths => $this->matchesSubscriptionMonthsRule($member, $rule),
            LoyaltyTriggerType::VisitCount => $this->matchesVisitCountRule($member, $rule),
            LoyaltyTriggerType::Birthday => $this->matchesBirthdayRule($member),
            LoyaltyTriggerType::Manual => false,
        };
    }

    private function matchesTotalHoursRule(Member $member, LoyaltyRule $rule): bool
    {
        $minimumHours = (float) $rule->min_total_hours;

        if ($minimumHours <= 0) {
            return false;
        }

        $query = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereIn('status', [SessionStatus::Closed->value, SessionStatus::Corrected->value])
            ->whereNotNull('check_out_at');

        if ($rule->period_months) {
            $query->where('check_out_at', '>=', now()->subMonths((int) $rule->period_months));
        }

        $totalSeconds = (int) $query
            ->selectRaw('COALESCE(SUM(COALESCE(billable_duration_seconds, raw_duration_seconds, 0)), 0) as total_seconds')
            ->value('total_seconds');

        return round($totalSeconds / 3600, 4) >= $minimumHours;
    }

    private function matchesSubscriptionMonthsRule(Member $member, LoyaltyRule $rule): bool
    {
        $minimumMonths = (int) $rule->min_subscription_months;

        if ($minimumMonths <= 0) {
            return false;
        }

        $firstSubscriptionStart = Subscription::query()
            ->where('member_id', $member->id)
            ->min('starts_at');

        if (! $firstSubscriptionStart) {
            return false;
        }

        return Carbon::parse($firstSubscriptionStart)->diffInMonths(now()) >= $minimumMonths;
    }

    private function matchesVisitCountRule(Member $member, LoyaltyRule $rule): bool
    {
        $minimumVisits = (int) $rule->min_visit_count;

        if ($minimumVisits <= 0) {
            return false;
        }

        $query = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereIn('status', [SessionStatus::Closed->value, SessionStatus::Corrected->value])
            ->whereNotNull('check_out_at');

        if ($rule->period_months) {
            $query->where('check_out_at', '>=', now()->subMonths((int) $rule->period_months));
        }

        return $query->count() >= $minimumVisits;
    }

    private function matchesBirthdayRule(Member $member): bool
    {
        if (! $member->birth_date) {
            return false;
        }

        $today = now();

        return (int) $member->birth_date->month === (int) $today->month
            && (int) $member->birth_date->day === (int) $today->day;
    }

    private function notifyAdminsAboutPendingReward(Reward $reward): void
    {
        $admins = User::query()->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new LoyaltyRewardQualifiedNotification($reward));
    }

    private function applyGrantedReward(Reward $reward): void
    {
        if (! $reward->granted_at) {
            $reward->forceFill(['granted_at' => now()])->save();
        }

        if ($reward->type !== LoyaltyRewardType::FreeHours) {
            return;
        }

        $hours = (float) $reward->value;
        if ($hours <= 0) {
            return;
        }

        $subscription = $this->subscriptions->getActiveSubscription($reward->member);
        if (! $subscription || ! $subscription->package || ! $this->subscriptions->isHourBased($subscription->package)) {
            return;
        }

        // Free hours credit is treated as negative usage.
        $this->subscriptions->adjustUsage($subscription, -$hours);
    }
}
