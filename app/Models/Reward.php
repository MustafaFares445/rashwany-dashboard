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
        'qualified_at',
        'granted_at',
        'activated_by',
        'notes',
    ];

    protected $casts = [
        'type' => LoyaltyRewardType::class,
        'status' => RewardStatus::class,
        'qualified_at' => 'datetime',
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

    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
