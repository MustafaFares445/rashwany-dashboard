<?php

namespace App\Services;

use App\Enums\AuditActorType;
use App\Models\AuditLog;

class AuditLogService
{
    public function log(
        string $action,
        string $entityType,
        int $entityId,
        array $oldValues = [],
        array $newValues = [],
        ?int $actorId = null,
        AuditActorType $actorType = AuditActorType::Admin,
        ?string $reason = null,
        ?string $ipAddress = null,
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actorId,
            'actor_type' => $actorType->value,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values_json' => $oldValues,
            'new_values_json' => $newValues,
            'reason' => $reason,
            'ip_address' => $ipAddress,
        ]);
    }
}
