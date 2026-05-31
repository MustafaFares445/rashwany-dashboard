<?php

namespace App\Services;

use App\Enums\PackageType;
use App\Models\Package;

class PackageService
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function create(array $data, ?int $actorId = null, ?string $ipAddress = null): Package
    {
        $payload = $this->normalizeData($data);
        $package = Package::create($payload);

        $this->audit->log(
            action: 'package_created',
            entityType: 'package',
            entityId: $package->id,
            newValues: $package->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $package;
    }

    public function update(Package $package, array $data, ?int $actorId = null, ?string $ipAddress = null): Package
    {
        $before = $package->replicate();
        $payload = $this->normalizeData($data, $package);

        $package->update($payload);

        $this->audit->log(
            action: 'package_updated',
            entityType: 'package',
            entityId: $package->id,
            oldValues: $before->toArray(),
            newValues: $package->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $package;
    }

    private function normalizeData(array $data, ?Package $package = null): array
    {
        $type = $data['type'] ?? $package?->type?->value ?? $package?->type;

        if (is_string($type) && in_array($type, [
            PackageType::UnlimitedWeekly->value,
            PackageType::UnlimitedMonthly->value,
        ], true)) {
            $data['included_hours'] = null;
        }

        if (is_string($type) && $type === PackageType::Hourly->value) {
            $data['duration_unit'] = $data['duration_unit'] ?? 'hour';
            $data['duration_value'] = $data['duration_value'] ?? 1;
        }

        return $data;
    }
}

