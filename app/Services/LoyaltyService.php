<?php

namespace App\Services;

use App\Enums\LoyaltyRewardType;
use App\Enums\RewardStatus;
use App\Models\LoyaltyRule;
use App\Models\Reward;
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

