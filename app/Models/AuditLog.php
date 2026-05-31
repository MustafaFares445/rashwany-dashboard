<?php

namespace App\Models;

use App\Enums\AuditActorType;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'entity_type',
        'entity_id',
        'old_values_json',
        'new_values_json',
        'reason',
        'ip_address',
    ];

    protected $casts = [
        'actor_type' => AuditActorType::class,
        'old_values_json' => 'array',
        'new_values_json' => 'array',
    ];
}
