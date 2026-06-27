<?php

namespace App\Notifications;

use App\Enums\RewardStatus;
use App\Models\Reward;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoyaltyRewardQualifiedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Reward $reward)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $reward = $this->reward->loadMissing(['member', 'loyaltyRule']);

        return [
            'title' => 'Loyalty bonus needs activation',
            'body' => sprintf(
                '%s qualified for %s. Review and activate the reward from the Rewards page.',
                $reward->member?->name ?? 'A member',
                $reward->loyaltyRule?->name ?? 'a loyalty rule',
            ),
            'icon' => 'heroicon-o-gift',
            'reward_id' => $reward->id,
            'member_id' => $reward->member_id,
            'loyalty_rule_id' => $reward->loyalty_rule_id,
            'status' => $reward->status instanceof RewardStatus ? $reward->status->value : $reward->status,
        ];
    }
}
