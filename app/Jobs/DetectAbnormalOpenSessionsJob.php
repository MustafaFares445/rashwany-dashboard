<?php

namespace App\Jobs;

use App\Services\SessionMonitoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DetectAbnormalOpenSessionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(SessionMonitoringService $monitoring): void
    {
        $monitoring->detectAbnormalOpenSessions();
    }
}

