<?php

namespace App\Notifications;

use App\Models\AttendanceSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbnormalSessionDetectedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AttendanceSession $session)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Abnormal Open Session Detected')
            ->line('A session exceeded the open-session review threshold.')
            ->line('Session ID: '.$this->session->id)
            ->line('Member ID: '.$this->session->member_id)
            ->line('Check-in: '.optional($this->session->check_in_at)->toDateTimeString());
    }
}

