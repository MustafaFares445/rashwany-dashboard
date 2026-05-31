<?php

namespace App\Jobs;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireSubscriptionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(SubscriptionLifecycleService $subscriptions): void
    {
        $subscriptions->expireEndedSubscriptions();
    }
}

