<?php

namespace App\Services;

use Carbon\Carbon;

class TimeRoundingService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function calculateDurations(Carbon $checkInAt, Carbon $checkOutAt): array
    {
        $rawSeconds = max(0, $checkInAt->diffInSeconds($checkOutAt, false));
        $rawMinutes = (int) floor($rawSeconds / 60);
        $roundedMinutes = $this->roundMinutes($rawMinutes);

        return [
            'raw_seconds' => $rawSeconds,
            'raw_minutes' => $rawMinutes,
            'rounded_seconds' => $roundedMinutes * 60,
            'rounded_minutes' => $roundedMinutes,
            'rounded_to_at' => $checkInAt->copy()->addMinutes($roundedMinutes),
        ];
    }

    public function roundMinutes(int $rawMinutes): int
    {
        $interval = max(1, $this->settings->getInt('hour_rounding_interval_minutes'));
        $threshold = max(0, $this->settings->getInt('hour_rounding_threshold_minutes'));

        $remainder = $rawMinutes % $interval;
        $rounded = $rawMinutes - $remainder;

        if ($remainder >= $threshold) {
            $rounded += $interval;
        }

        return $rounded;
    }
}
