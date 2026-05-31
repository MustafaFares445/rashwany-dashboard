<?php

namespace App\Services;

use App\Enums\CorrectionRequestStatus;
use App\Enums\SessionStatus;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Notifications\CorrectionRequestStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CorrectionRequestService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly TimeRoundingService $rounding,
        private readonly SubscriptionService $subscriptions,
    ) {
    }

    public function create(array $data, ?int $actorId = null, ?string $ipAddress = null): CorrectionRequest
    {
        return DB::transaction(function () use ($data, $actorId, $ipAddress) {
            $request = CorrectionRequest::create($data);

            $this->audit->log(
                action: 'correction_request_created',
                entityType: 'correction_request',
                entityId: $request->id,
                newValues: $request->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            return $request;
        });
    }

    public function update(CorrectionRequest $request, array $data, ?int $actorId = null, ?string $ipAddress = null): CorrectionRequest
    {
        return DB::transaction(function () use ($request, $data, $actorId, $ipAddress) {
            $before = $request->replicate();

            if (isset($data['status']) && in_array($data['status'], [
                CorrectionRequestStatus::Approved->value,
                CorrectionRequestStatus::Rejected->value,
            ], true)) {
                $data['reviewed_at'] = $data['reviewed_at'] ?? now();
                $data['reviewed_by'] = $data['reviewed_by'] ?? $actorId;
            }

            $request->update($data);

            if ($this->shouldApplyCorrection($before, $request)) {
                $this->applyCorrection($request);
            }

            $this->audit->log(
                action: 'correction_request_updated',
                entityType: 'correction_request',
                entityId: $request->id,
                oldValues: $before->toArray(),
                newValues: $request->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            if ($this->isFinalStatusChange($before, $request)) {
                Notification::send(
                    User::query()->get(),
                    new CorrectionRequestStatusNotification($request),
                );
            }

            return $request;
        });
    }

    private function shouldApplyCorrection(CorrectionRequest $before, CorrectionRequest $after): bool
    {
        return $before->status !== CorrectionRequestStatus::Approved
            && $after->status === CorrectionRequestStatus::Approved
            && $after->session;
    }

    private function isFinalStatusChange(CorrectionRequest $before, CorrectionRequest $after): bool
    {
        if ($before->status === $after->status) {
            return false;
        }

        return in_array($after->status, [
            CorrectionRequestStatus::Approved,
            CorrectionRequestStatus::Rejected,
        ], true);
    }

    private function applyCorrection(CorrectionRequest $request): void
    {
        $session = $request->session;
        if (! $session || ! $request->requested_check_in_at || ! $request->requested_check_out_at) {
            return;
        }

        $durations = $this->rounding->calculateDurations(
            $request->requested_check_in_at,
            $request->requested_check_out_at,
        );

        $oldBillableMinutes = (int) ($session->billable_duration_minutes ?? 0);
        $newBillableMinutes = (int) $durations['rounded_minutes'];

        $session->update([
            'check_in_at' => $request->requested_check_in_at,
            'check_out_at' => $request->requested_check_out_at,
            'raw_duration_minutes' => $durations['raw_minutes'],
            'billable_duration_minutes' => $newBillableMinutes,
            'rounded_from_at' => $request->requested_check_in_at,
            'rounded_to_at' => $durations['rounded_to_at'],
            'status' => SessionStatus::Corrected->value,
            'correction_request_id' => $request->id,
        ]);

        if ($session->subscription && $this->subscriptions->isHourBased($session->subscription->package)) {
            $deltaHours = round(($newBillableMinutes - $oldBillableMinutes) / 60, 2);

            if ($deltaHours !== 0.0) {
                $this->subscriptions->adjustUsage($session->subscription, $deltaHours);
            }
        }
    }
}
