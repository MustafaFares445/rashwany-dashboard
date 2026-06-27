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
use App\Notifications\LoyaltyRewardPendingNotification;
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

        if ($rule->is_active) {
            $this->evaluateRule($rule);
        }

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

        if ($rule->is_active) {
            $this->evaluateRule($rule);
        }

        return $rule;
    }

    public function createReward(array $data, ?int $actorId = null, ?string $ipAddress = null): Reward
    {
        return DB::transaction(function () use ($data, $actorId, $ipAddress) {
            $data['status'] = $data['status'] ?? RewardStatus::Pending->value;
            $reward = Reward::create($data);

            if ($reward->status === RewardStatus::Granted) {
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

    public function evaluateRule(LoyaltyRule $rule): int
    {
        if (! $rule->is_active) {
            return 0;
        }

        $created = 0;

        Member::query()
            ->where('status', MemberStatus::Active->value)
            ->chunkById(100, function ($members) use ($rule, &$created): void {
                foreach ($members as $member) {
                    $created += $this->evaluateMember($member, $rule);
                }
            });

        return $created;
    }

    public function evaluateMember(Member $member, ?LoyaltyRule $onlyRule = null): int
    {
        $created = 0;

        $rules = $onlyRule
            ? collect([$onlyRule])
            : LoyaltyRule::query()->where('is_active', true)->get();

        foreach ($rules as $rule) {
            if (! $rule->is_active) {
                continue;
            }

            if (! $this->memberQualifiesForRule($member, $rule)) {
                continue;
            }

            if ($this->memberHasOpenRewardForRule($member, $rule)) {
                continue;
            }

            $reward = $this->createReward([
                'member_id' => $member->id,
                'loyalty_rule_id' => $rule->id,
                'type' => $this->rewardTypeValue($rule),
                'value' => $rule->reward_value,
                'status' => RewardStatus::Pending->value,
                'notes' => $this->buildEligibilityNote($rule),
            ]);

            $this->notifyAdminsAboutPendingReward($reward);
            $created++;
        }

        return $created;
    }

    private function memberQualifiesForRule(Member $member, LoyaltyRule $rule): bool
    {
        return match ($this->triggerTypeValue($rule)) {
            LoyaltyTriggerType::TotalHours->value => $this->memberTotalHours($member, $rule) >= (float) $rule->min_hours,
            LoyaltyTriggerType::SubscriptionMonths->value => $this->memberSubscriptionMonths($member) >= (int) $rule->min_subscription_months,
            LoyaltyTriggerType::VisitCount->value => $this->memberVisitCount($member, $rule) >= (int) $rule->min_visits,
            LoyaltyTriggerType::Birthday->value => $this->isMemberBirthdayToday($member),
            default => false,
        };
    }

    private function memberHasOpenRewardForRule(Member $member, LoyaltyRule $rule): bool
    {
        return Reward::query()
            ->where('member_id', $member->id)
            ->where('loyalty_rule_id', $rule->id)
            ->whereIn('status', [
                RewardStatus::Pending->value,
                RewardStatus::Granted->value,
            ])
            ->exists();
    }

    private function memberTotalHours(Member $member, LoyaltyRule $rule): float
    {
        $query = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereIn('status', [
                SessionStatus::Closed->value,
                SessionStatus::Corrected->value,
            ]);

        $this->applyRulePeriod($query, $rule);

        $seconds = (int) $query->sum(DB::raw('COALESCE(billable_duration_seconds, raw_duration_seconds, billable_duration_minutes * 60, raw_duration_minutes * 60, 0)'));

        return round($seconds / 3600, 4);
    }

    private function memberVisitCount(Member $member, LoyaltyRule $rule): int
    {
        $query = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereIn('status', [
                SessionStatus::Closed->value,
                SessionStatus::Corrected->value,
            ]);

        $this->applyRulePeriod($query, $rule);

        return $query->count();
    }

    private function applyRulePeriod($query, LoyaltyRule $rule): void
    {
        if (! $rule->period_months) {
            return;
        }

        $query->where('check_out_at', '>=', now()->subMonths((int) $rule->period_months));
    }

    private function memberSubscriptionMonths(Member $member): int
    {
        $firstSubscriptionStart = Subscription::query()
            ->where('member_id', $member->id)
            ->min('starts_at');

        if (! $firstSubscriptionStart) {
            return 0;
        }

        return (int) Carbon::parse($firstSubscriptionStart)->diffInMonths(now());
    }

    private function isMemberBirthdayToday(Member $member): bool
    {
        return $member->birth_date
            && $member->birth_date->month === now()->month
            && $member->birth_date->day === now()->day;
    }

    private function buildEligibilityNote(LoyaltyRule $rule): string
    {
        return sprintf(
            'Auto-created from loyalty rule "%s". Pending admin activation.',
            $rule->name,
        );
    }

    private function notifyAdminsAboutPendingReward(Reward $reward): void
    {
        $admins = User::query()->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new LoyaltyRewardPendingNotification($reward));
    }

    private function triggerTypeValue(LoyaltyRule $rule): string
    {
        return $rule->trigger_type instanceof LoyaltyTriggerType
            ? $rule->trigger_type->value
            : (string) $rule->trigger_type;
    }

    private function rewardTypeValue(LoyaltyRule $rule): string
    {
        return $rule->reward_type instanceof LoyaltyRewardType
            ? $rule->reward_type->value
            : (string) $rule->reward_type;
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
