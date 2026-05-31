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
        'condition_json',
        'reward_type',
        'reward_value',
        'is_active',
    ];

    protected $casts = [
        'trigger_type' => LoyaltyTriggerType::class,
        'condition_json' => 'array',
        'reward_type' => LoyaltyRewardType::class,
        'is_active' => 'boolean',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}

