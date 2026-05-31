<?php

namespace App\Models;

use App\Enums\QrPurpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrCode extends Model
{
    protected $fillable = [
        'name',
        'purpose',
        'location_id',
        'office_area_id',
        'token_hash',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'purpose' => QrPurpose::class,
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }
}
