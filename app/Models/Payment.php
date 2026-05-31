<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'member_id',
        'subscription_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'due_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
