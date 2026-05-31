<?php

namespace App\Models;

use App\Enums\CorrectionRequestStatus;
use App\Enums\CorrectionRequestType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $fillable = [
        'member_id',
        'session_id',
        'type',
        'requested_check_in_at',
        'requested_check_out_at',
        'message',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'type' => CorrectionRequestType::class,
        'status' => CorrectionRequestStatus::class,
        'requested_check_in_at' => 'datetime',
        'requested_check_out_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
