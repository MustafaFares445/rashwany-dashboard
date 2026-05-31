<?php

use App\Jobs\DetectAbnormalOpenSessionsJob;
use App\Jobs\ExpireSubscriptionsJob;
use App\Jobs\GenerateDailyReportSnapshotJob;
use App\Jobs\NotifyExpiringSubscriptionsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new DetectAbnormalOpenSessionsJob())->hourly();
Schedule::job(new ExpireSubscriptionsJob())->hourly();
Schedule::job(new NotifyExpiringSubscriptionsJob())->dailyAt('08:00');
Schedule::job(new GenerateDailyReportSnapshotJob())->dailyAt('23:59');
