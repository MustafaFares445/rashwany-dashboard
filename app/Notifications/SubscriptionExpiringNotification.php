<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Subscription $subscription)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Subscription Expiring Soon')
            ->line('A member subscription is close to expiry.')
            ->line('Subscription ID: '.$this->subscription->id)
            ->line('Member ID: '.$this->subscription->member_id)
            ->line('Ends At: '.optional($this->subscription->ends_at)->toDateTimeString());
    }
}

