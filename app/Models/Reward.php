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
        'loyalty_rule_id',
        'type',
        'value',
        'status',
        'granted_at',
        'notes',
    ];

    protected $casts = [
        'type' => LoyaltyRewardType::class,
        'status' => RewardStatus::class,
        'granted_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loyaltyRule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRule::class);
    }
}

