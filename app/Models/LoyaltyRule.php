<?php

namespace App\Models;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyRule extends Model
{
    protected $fillable = [
        'name',
        'trigger_type',
        'min_total_hours',
        'period_months',
        'min_subscription_months',
        'min_visit_count',
        'reward_type',
        'reward_value',
        'is_active',
    ];

    protected $casts = [
        'trigger_type' => LoyaltyTriggerType::class,
        'min_total_hours' => 'decimal:2',
        'period_months' => 'integer',
        'min_subscription_months' => 'integer',
        'min_visit_count' => 'integer',
        'reward_type' => LoyaltyRewardType::class,
        'is_active' => 'boolean',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}
