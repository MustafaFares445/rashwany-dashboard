<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Support\Str;

class MemberService
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function create(array $data, ?int $actorId = null, ?string $ipAddress = null): Member
    {
        $data['status'] = $data['status'] ?? MemberStatus::Active->value;
        $data['qr_identifier'] = $data['qr_identifier'] ?? $this->generateQrIdentifier();

        $member = Member::create($data);

        $this->audit->log(
            action: 'member_created',
            entityType: 'member',
            entityId: $member->id,
            newValues: $member->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $member;
    }

    public function update(Member $member, array $data, ?int $actorId = null, ?string $ipAddress = null): Member
    {
        $before = $member->replicate();

        $member->update($data);

        $this->audit->log(
            action: 'member_updated',
            entityType: 'member',
            entityId: $member->id,
            oldValues: $before->toArray(),
            newValues: $member->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $member;
    }

    public function setStatus(Member $member, MemberStatus $status, ?int $actorId = null, ?string $ipAddress = null): Member
    {
        return $this->update($member, ['status' => $status->value], $actorId, $ipAddress);
    }

    private function generateQrIdentifier(): string
    {
        return (string) Str::ulid();
    }
}

