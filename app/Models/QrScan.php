<?php

namespace App\Models;

use App\Enums\QrPurpose;
use App\Enums\QrScanResult;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class QrScan extends Model
{
    protected $fillable = [
        'member_id',
        'qr_code_id',
        'purpose',
        'result',
        'failure_reason',
        'scanned_at',
        'ip_address',
        'device_info',
        'location_id',
        'raw_payload',
    ];

    protected $casts = [
        'purpose' => QrPurpose::class,
        'result' => QrScanResult::class,
        'scanned_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
