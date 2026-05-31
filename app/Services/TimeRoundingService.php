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
        $rawMinutes = max(0, $checkInAt->diffInMinutes($checkOutAt, false));
        $roundedMinutes = $this->roundMinutes($rawMinutes);

        return [
            'raw_minutes' => $rawMinutes,
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
