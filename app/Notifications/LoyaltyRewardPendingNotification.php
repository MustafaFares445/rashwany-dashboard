<?php

namespace App\Notifications;

use App\Models\Reward;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoyaltyRewardPendingNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Reward $reward)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->reward->loadMissing(['member', 'loyaltyRule']);

        $memberName = $this->reward->member?->name ?? 'Member #'.$this->reward->member_id;
        $ruleName = $this->reward->loyaltyRule?->name ?? 'Loyalty rule #'.$this->reward->loyalty_rule_id;

        return [
            'title' => 'Loyalty bonus pending approval',
            'message' => $memberName.' reached '.$ruleName.'. Review and activate the bonus from Rewards.',
            'reward_id' => $this->reward->id,
            'member_id' => $this->reward->member_id,
            'member_name' => $memberName,
            'loyalty_rule_id' => $this->reward->loyalty_rule_id,
            'loyalty_rule_name' => $ruleName,
            'reward_type' => $this->reward->type?->value ?? (string) $this->reward->type,
            'reward_value' => $this->reward->value,
            'status' => $this->reward->status?->value ?? (string) $this->reward->status,
        ];
    }
}
