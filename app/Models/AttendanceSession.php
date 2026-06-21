<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'member_id',
        'subscription_id',
        'check_in_at',
        'check_out_at',
        'raw_duration_minutes',
        'billable_duration_minutes',
        'rounded_from_at',
        'rounded_to_at',
        'status',
        'check_in_scan_id',
        'check_out_scan_id',
        'correction_request_id',
        'created_by',
        'closed_by',
        'admin_updated_by',
        'admin_updated_at',
        'notes',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'rounded_from_at' => 'datetime',
        'rounded_to_at' => 'datetime',
        'admin_updated_at' => 'datetime',
        'status' => SessionStatus::class,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function checkInScan(): BelongsTo
    {
        return $this->belongsTo(QrScan::class, 'check_in_scan_id');
    }

    public function checkOutScan(): BelongsTo
    {
        return $this->belongsTo(QrScan::class, 'check_out_scan_id');
    }

    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectionRequest::class, 'correction_request_id');
    }

    public function adminUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_updated_by');
    }
}
