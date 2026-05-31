<?php

namespace App\Jobs;

use App\Services\AnalyticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDailyReportSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function handle(AnalyticsService $analytics): void
    {
        $analytics->generateDailySnapshot();
    }
}

