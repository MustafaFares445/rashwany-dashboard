<?php

namespace App\Services;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use App\Enums\RewardStatus;
use App\Filament\Resources\Rewards\RewardResource;
use App\Models\AttendanceSession;
use App\Models\LoyaltyRule;
use App\Models\Member;
use App\Models\Reward;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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
            $data = $this->attachActiveSubscription($data);

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

            $data = $this->attachActiveSubscription($data + ['member_id' => $reward->member_id], $reward);

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

    public function evaluateMember(Member $member, ?Subscription $subscription = null): void
    {
        LoyaltyRule::query()
            ->where('is_active', true)
            ->get()
            ->each(function (LoyaltyRule $rule) use ($member, $subscription): void {
                if (! $this->memberMatchesRule($member, $rule)) {
                    return;
                }

                $alreadyDetected = Reward::query()
                    ->where('member_id', $member->id)
                    ->where('loyalty_rule_id', $rule->id)
                    ->exists();

                if ($alreadyDetected) {
                    return;
                }

                $reward = Reward::create([
                    'member_id' => $member->id,
                    'subscription_id' => $subscription?->id ?? $this->subscriptions->getActiveSubscription($member)?->id,
                    'loyalty_rule_id' => $rule->id,
                    'type' => $rule->reward_type->value,
                    'value' => $rule->reward_value,
                    'status' => RewardStatus::Pending->value,
                    'notes' => 'Auto-detected by loyalty rule. Admin activation is required before applying this award.',
                ]);

                $this->notifyAdminsAboutPendingReward($reward);

                $this->audit->log(
                    action: 'reward_auto_detected',
                    entityType: 'reward',
                    entityId: $reward->id,
                    newValues: $reward->toArray(),
                );
            });
    }

    private function attachActiveSubscription(array $data, ?Reward $reward = null): array
    {
        if (! empty($data['subscription_id'])) {
            return $data;
        }

        $memberId = $data['member_id'] ?? $reward?->member_id;
        if (! $memberId) {
            return $data;
        }

        $member = Member::query()->find($memberId);
        if (! $member) {
            return $data;
        }

        $data['subscription_id'] = $this->subscriptions->getActiveSubscription($member)?->id;

        return $data;
    }

    private function memberMatchesRule(Member $member, LoyaltyRule $rule): bool
    {
        return match ($rule->trigger_type) {
            LoyaltyTriggerType::TotalHours => (float) $rule->threshold_hours > 0 && $this->memberTotalHours($member, $rule) >= (float) $rule->threshold_hours,
            LoyaltyTriggerType::VisitCount => (int) $rule->threshold_visits > 0 && $this->memberVisitCount($member, $rule) >= (int) $rule->threshold_visits,
            LoyaltyTriggerType::SubscriptionMonths => (int) $rule->threshold_subscription_months > 0 && $this->memberSubscriptionMonths($member) >= (int) $rule->threshold_subscription_months,
            default => false,
        };
    }

    private function memberTotalHours(Member $member, LoyaltyRule $rule): float
    {
        if (! $rule->threshold_hours || (float) $rule->threshold_hours <= 0) {
            return 0.0;
        }

        $seconds = $this->closedSessionsQuery($member, $rule)
            ->sum(DB::raw('COALESCE(billable_duration_seconds, raw_duration_seconds, billable_duration_minutes * 60, raw_duration_minutes * 60, 0)'));

        return round(((float) $seconds) / 3600, 4);
    }

    private function memberVisitCount(Member $member, LoyaltyRule $rule): int
    {
        if (! $rule->threshold_visits || (int) $rule->threshold_visits <= 0) {
            return 0;
        }

        return $this->closedSessionsQuery($member, $rule)->count();
    }

    private function memberSubscriptionMonths(Member $member): int
    {
        $firstSubscription = $member->subscriptions()
            ->whereNotNull('starts_at')
            ->oldest('starts_at')
            ->first();

        if (! $firstSubscription?->starts_at) {
            return 0;
        }

        return (int) $firstSubscription->starts_at->diffInMonths(now());
    }

    private function closedSessionsQuery(Member $member, LoyaltyRule $rule)
    {
        $query = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereNotNull('check_out_at');

        if ($rule->period_months && (int) $rule->period_months > 0) {
            $query->where('check_out_at', '>=', now()->subMonths((int) $rule->period_months));
        }

        return $query;
    }

    private function notifyAdminsAboutPendingReward(Reward $reward): void
    {
        $users = User::query()->get();
        if ($users->isEmpty()) {
            return;
        }

        $reward->loadMissing(['member', 'loyaltyRule', 'subscription']);

        Notification::make()
            ->title('Reward waiting for activation')
            ->body(sprintf(
                '%s reached %s. Activate the pending award to apply it to subscription #%s.',
                $reward->member?->name ?? 'A member',
                $reward->loyaltyRule?->name ?? 'a loyalty rule',
                $reward->subscription_id ?? 'N/A',
            ))
            ->icon('heroicon-o-gift')
            ->warning()
            ->actions([
                Action::make('view_reward')
                    ->label('View reward')
                    ->url(RewardResource::getUrl('edit', ['record' => $reward])),
            ])
            ->sendToDatabase($users);
    }

    private function applyGrantedReward(Reward $reward): void
    {
        $subscription = $reward->subscription ?? $this->subscriptions->getActiveSubscription($reward->member);

        if (! $reward->granted_at || ! $reward->activated_at || ($subscription && ! $reward->subscription_id)) {
            $reward->forceFill([
                'subscription_id' => $reward->subscription_id ?? $subscription?->id,
                'granted_at' => $reward->granted_at ?? now(),
                'activated_at' => $reward->activated_at ?? now(),
            ])->save();
        }

        if ($reward->type !== LoyaltyRewardType::FreeHours) {
            return;
        }

        $hours = (float) $reward->value;
        if ($hours <= 0) {
            return;
        }

        if (! $subscription || ! $subscription->package || ! $this->subscriptions->isHourBased($subscription->package)) {
            return;
        }

        // Free hours credit is treated as negative usage.
        $this->subscriptions->adjustUsage($subscription, -$hours);
    }
}
