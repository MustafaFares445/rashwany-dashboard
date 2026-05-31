<?php

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'type' => SettingType::class,
        'is_public' => 'boolean',
    ];
}
