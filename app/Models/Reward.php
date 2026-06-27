<?php

namespace App\Models;

use App\Enums\LoyaltyRewardType;
use App\Enums\RewardStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    protected $fillable = [
        'member_id',
        'subscription_id',
        'loyalty_rule_id',
        'type',
        'value',
        'status',
        'granted_at',
        'activated_at',
        'notes',
    ];

    protected $casts = [
        'type' => LoyaltyRewardType::class,
        'status' => RewardStatus::class,
        'granted_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function loyaltyRule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRule::class);
    }
}
