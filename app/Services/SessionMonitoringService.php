<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Notifications\AbnormalSessionDetectedNotification;
use Illuminate\Support\Facades\Notification;

class SessionMonitoringService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function detectAbnormalOpenSessions(): int
    {
        $thresholdHours = $this->settings->getInt('open_session_review_after_hours');
        if ($thresholdHours <= 0) {
            return 0;
        }

        $thresholdTime = now()->subHours($thresholdHours);
        $sessions = AttendanceSession::query()
            ->where('status', SessionStatus::Open->value)
            ->where('check_in_at', '<=', $thresholdTime)
            ->get();

        foreach ($sessions as $session) {
            $session->update([
                'status' => SessionStatus::NeedsReview->value,
            ]);

            if ($this->settings->getBool('notify_admin_for_abnormal_sessions')) {
                Notification::send(User::query()->get(), new AbnormalSessionDetectedNotification($session));
            }
        }

        return $sessions->count();
    }
}

