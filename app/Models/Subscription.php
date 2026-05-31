<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'member_id',
        'package_id',
        'status',
        'starts_at',
        'ends_at',
        'total_hours',
        'remaining_hours',
        'used_hours',
        'price',
        'paid_amount',
        'due_amount',
        'auto_renew',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
        'used_hours' => 'decimal:2',
        'price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
