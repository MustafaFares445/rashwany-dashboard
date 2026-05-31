<?php

namespace App\Notifications;

use App\Models\CorrectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorrectionRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly CorrectionRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Correction Request Updated')
            ->line('A correction request status was updated.')
            ->line('Request ID: '.$this->request->id)
            ->line('Member ID: '.$this->request->member_id)
            ->line('Status: '.($this->request->status?->value ?? $this->request->status));
    }
}

