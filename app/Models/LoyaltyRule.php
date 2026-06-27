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
        'min_hours',
        'period_months',
        'min_subscription_months',
        'min_visits',
        'reward_type',
        'reward_value',
        'is_active',
    ];

    protected $casts = [
        'trigger_type' => LoyaltyTriggerType::class,
        'min_hours' => 'decimal:4',
        'period_months' => 'integer',
        'min_subscription_months' => 'integer',
        'min_visits' => 'integer',
        'reward_type' => LoyaltyRewardType::class,
        'is_active' => 'boolean',
    ];

    public function setConditionJsonAttribute(array|string|null $value): void
    {
        $conditions = is_array($value)
            ? $value
            : (json_decode((string) $value, true) ?: []);

        $this->attributes['min_hours'] = $conditions['min_hours'] ?? null;
        $this->attributes['period_months'] = $conditions['period_months'] ?? null;
        $this->attributes['min_subscription_months'] = $conditions['min_subscription_months'] ?? null;
        $this->attributes['min_visits'] = $conditions['min_visits'] ?? null;
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}
