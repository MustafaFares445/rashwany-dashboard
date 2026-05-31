<?php

namespace App\Models;

use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'duration_unit',
        'duration_value',
        'included_hours',
        'price',
        'renewal_type',
        'is_active',
        'settings_json',
    ];

    protected $casts = [
        'type' => PackageType::class,
        'duration_unit' => PackageDurationUnit::class,
        'renewal_type' => PackageRenewalType::class,
        'included_hours' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'settings_json' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
