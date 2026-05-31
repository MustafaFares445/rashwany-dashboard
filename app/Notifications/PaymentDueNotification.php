<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Member $member,
        private readonly float $dueAmount,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Member Payment Due Alert')
            ->line('A member currently has an outstanding due amount.')
            ->line('Member: '.$this->member->name.' (#'.$this->member->id.')')
            ->line('Due Amount: '.number_format($this->dueAmount, 2).' USD');
    }
}

