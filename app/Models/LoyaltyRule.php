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
        'threshold_hours',
        'threshold_visits',
        'threshold_subscription_months',
        'period_months',
        'description',
        'reward_type',
        'reward_value',
        'is_active',
    ];

    protected $casts = [
        'trigger_type' => LoyaltyTriggerType::class,
        'threshold_hours' => 'decimal:2',
        'threshold_visits' => 'integer',
        'threshold_subscription_months' => 'integer',
        'period_months' => 'integer',
        'reward_type' => LoyaltyRewardType::class,
        'is_active' => 'boolean',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}
