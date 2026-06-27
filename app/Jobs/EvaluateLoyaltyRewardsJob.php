<?php

namespace App\Jobs;

use App\Services\LoyaltyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateLoyaltyRewardsJob implements ShouldQueue
{
    use Queueable;

    public function handle(LoyaltyService $loyalty): void
    {
        $loyalty->evaluateAllMembers();
    }
}
