<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\QrPurpose;
use App\Enums\QrScanResult;
use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\Member;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\Subscription;
use Carbon\Carbon;

class AttendanceService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly SubscriptionService $subscriptions,
        private readonly TimeRoundingService $rounding,
    ) {}

    public function processPhoneAndPin(string $phone, string $pin): array
    {
        $member = Member::query()->where('phone', $phone)->first();

        if (! $member) {
            return $this->buildErrorResponse('Member not found', 'invalid_phone');
        }

        if ($member->pin !== $pin) {
            return $this->buildErrorResponse('Invalid PIN', 'invalid_pin');
        }

        if ($member->status === MemberStatus::Blocked || $member->status === MemberStatus::Inactive) {
            return $this->buildErrorResponse('Member is blocked or inactive', 'member_blocked');
        }

        $scannedAt = now();
        $openSession = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->where('status', SessionStatus::Open->value)
            ->latest('check_in_at')
            ->first();

        if ($openSession) {
            return $this->handlePhoneAndPinCheckOut($member, $openSession, $scannedAt);
        }

        return $this->handlePhoneAndPinCheckIn($member, $scannedAt);
    }

    private function handlePhoneAndPinCheckIn(Member $member, Carbon $scannedAt): array
    {
        $subscription = $this->subscriptions->getActiveSubscription($member, $scannedAt);

        if (! $subscription) {
            return $this->buildErrorResponse('No active subscription', 'subscription_expired');
        }

        if ($this->subscriptions->isHourBased($subscription->package)) {
            if ($subscription->remaining_hours !== null && (float) $subscription->remaining_hours <= 0) {
                return $this->buildErrorResponse('No remaining hours', 'no_remaining_hours');
            }
        }

        if (! $this->canCheckInWithDueAmount($subscription)) {
            return $this->buildErrorResponse('Outstanding balance exceeds limit', 'unpaid_limit_exceeded');
        }

        $session = AttendanceSession::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'check_in_at' => $scannedAt,
            'status' => SessionStatus::Open->value,
        ]);

        return $this->buildSuccessResponse($member, $session, 'checked_in');
    }

    private function handlePhoneAndPinCheckOut(Member $member, AttendanceSession $session, Carbon $scannedAt): array
    {
        $durations = $this->rounding->calculateDurations($session->check_in_at, $scannedAt);
        $rawMinutes = $durations['raw_minutes'];
        $roundedMinutes = $durations['rounded_minutes'];
        $roundedToAt = $durations['rounded_to_at'];

        $reviewThresholdMinutes = $this->settings->getInt('open_session_review_after_hours') * 60;
        if ($reviewThresholdMinutes > 0 && $rawMinutes >= $reviewThresholdMinutes) {
            $session->update([
                'check_out_at' => $scannedAt,
                'raw_duration_minutes' => $rawMinutes,
                'billable_duration_minutes' => null,
                'rounded_from_at' => $session->check_in_at,
                'rounded_to_at' => $roundedToAt,
                'status' => SessionStatus::NeedsReview->value,
            ]);

            return $this->buildErrorResponse('Session duration exceeds threshold and needs review', 'abnormal_session');
        }

        $session->update([
            'check_out_at' => $scannedAt,
            'raw_duration_minutes' => $rawMinutes,
            'billable_duration_minutes' => $roundedMinutes,
            'rounded_from_at' => $session->check_in_at,
            'rounded_to_at' => $roundedToAt,
            'status' => SessionStatus::Closed->value,
        ]);

        if ($session->subscription) {
            $billableHours = round($roundedMinutes / 60, 2);

            if ($this->subscriptions->isHourBased($session->subscription->package)) {
                $this->subscriptions->applyUsage($session->subscription, $billableHours);
            }
        }

        return $this->buildSuccessResponse($member, $session, 'checked_out', $rawMinutes, $roundedMinutes);
    }

    public function processScan(Member $member, ?QrCode $qrCode, array $payload): array
    {
        $scannedAt = $this->resolveScanTime($payload['scanned_at'] ?? null);

        if (! $qrCode || ! $qrCode->is_active || ($qrCode->expires_at && $qrCode->expires_at->isPast())) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'invalid_qr');
        }

        if ($member->status === MemberStatus::Blocked || $member->status === MemberStatus::Inactive) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'member_blocked');
        }

        return match ($this->resolvePurpose($qrCode)) {
            'check_in' => $this->processCheckIn($member, $qrCode, $payload, $scannedAt),
            'check_out' => $this->processCheckOut($member, $qrCode, $payload, $scannedAt),
            default => $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'invalid_qr'),
        };
    }

    private function processCheckIn(Member $member, QrCode $qrCode, array $payload, Carbon $scannedAt): array
    {
        $subscription = $this->subscriptions->getActiveSubscription($member, $scannedAt);

        if (! $subscription) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'subscription_expired');
        }

        if ($this->subscriptions->isHourBased($subscription->package)) {
            if ($subscription->remaining_hours !== null && (float) $subscription->remaining_hours <= 0) {
                return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'no_remaining_hours');
            }
        }

        if (! $this->canCheckInWithDueAmount($subscription)) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'unpaid_limit_exceeded');
        }

        $openSession = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->where('status', SessionStatus::Open->value)
            ->first();

        if ($openSession) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'already_checked_in');
        }

        $scan = $this->recordScan($member, $qrCode, $payload, $scannedAt, QrScanResult::Success);

        $session = AttendanceSession::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'check_in_at' => $scannedAt,
            'status' => SessionStatus::Open->value,
            'check_in_scan_id' => $scan->id,
        ]);

        return $this->successScan($scan, $session);
    }

    private function processCheckOut(Member $member, QrCode $qrCode, array $payload, Carbon $scannedAt): array
    {
        $session = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->where('status', SessionStatus::Open->value)
            ->latest('check_in_at')
            ->first();

        if (! $session) {
            return $this->rejectScan($member, $qrCode, $payload, $scannedAt, 'no_open_session');
        }

        $durations = $this->rounding->calculateDurations($session->check_in_at, $scannedAt);
        $rawMinutes = $durations['raw_minutes'];
        $roundedMinutes = $durations['rounded_minutes'];
        $roundedToAt = $durations['rounded_to_at'];

        $reviewThresholdMinutes = $this->settings->getInt('open_session_review_after_hours') * 60;
        if ($reviewThresholdMinutes > 0 && $rawMinutes >= $reviewThresholdMinutes) {
            $scan = $this->recordScan($member, $qrCode, $payload, $scannedAt, QrScanResult::NeedsReview, 'abnormal_session');

            $session->update([
                'check_out_at' => $scannedAt,
                'raw_duration_minutes' => $rawMinutes,
                'billable_duration_minutes' => null,
                'rounded_from_at' => $session->check_in_at,
                'rounded_to_at' => $roundedToAt,
                'status' => SessionStatus::NeedsReview->value,
                'check_out_scan_id' => $scan->id,
            ]);

            return $this->needsReviewScan($scan, $session);
        }

        $scan = $this->recordScan($member, $qrCode, $payload, $scannedAt, QrScanResult::Success);

        $session->update([
            'check_out_at' => $scannedAt,
            'raw_duration_minutes' => $rawMinutes,
            'billable_duration_minutes' => $roundedMinutes,
            'rounded_from_at' => $session->check_in_at,
            'rounded_to_at' => $roundedToAt,
            'status' => SessionStatus::Closed->value,
            'check_out_scan_id' => $scan->id,
        ]);

        if ($session->subscription) {
            $billableHours = round($roundedMinutes / 60, 2);

            if ($this->subscriptions->isHourBased($session->subscription->package)) {
                $this->subscriptions->applyUsage($session->subscription, $billableHours);
            }
        }

        return $this->successScan($scan, $session);
    }

    private function recordScan(
        Member $member,
        ?QrCode $qrCode,
        array $payload,
        Carbon $scannedAt,
        QrScanResult $result,
        ?string $failureReason = null,
    ): QrScan {
        return QrScan::create([
            'member_id' => $member->id,
            'qr_code_id' => $qrCode?->id,
            'purpose' => $qrCode ? $this->resolvePurpose($qrCode) : ($payload['purpose'] ?? 'check_in'),
            'result' => $result->value,
            'failure_reason' => $failureReason,
            'scanned_at' => $scannedAt,
            'ip_address' => $payload['ip_address'] ?? null,
            'device_info' => $payload['device_info'] ?? null,
            'location_id' => $payload['location_id'] ?? null,
            'raw_payload' => $payload,
        ]);
    }

    private function rejectScan(Member $member, ?QrCode $qrCode, array $payload, Carbon $scannedAt, string $reason): array
    {
        $scan = $this->recordScan($member, $qrCode, $payload, $scannedAt, QrScanResult::Rejected, $reason);

        return [
            'scan' => $scan,
            'session' => null,
            'result' => QrScanResult::Rejected,
            'failure_reason' => $reason,
        ];
    }

    private function successScan(QrScan $scan, AttendanceSession $session): array
    {
        return [
            'scan' => $scan,
            'session' => $session,
            'result' => QrScanResult::Success,
            'failure_reason' => null,
        ];
    }

    private function needsReviewScan(QrScan $scan, AttendanceSession $session): array
    {
        return [
            'scan' => $scan,
            'session' => $session,
            'result' => QrScanResult::NeedsReview,
            'failure_reason' => 'abnormal_session',
        ];
    }

    private function resolveScanTime(?string $scanTime): Carbon
    {
        return $scanTime ? Carbon::parse($scanTime) : now();
    }

    private function resolvePurpose(QrCode $qrCode): string
    {
        return $qrCode->purpose instanceof QrPurpose
            ? $qrCode->purpose->value
            : (string) $qrCode->purpose;
    }

    private function canCheckInWithDueAmount(Subscription $subscription): bool
    {
        $dueAmount = (float) $subscription->due_amount;
        $allowDue = $this->settings->getBool('allow_check_in_with_due_amount');
        $maxAllowed = $this->settings->getDecimal('max_allowed_due_amount');

        if (! $allowDue && $dueAmount > 0) {
            return false;
        }

        if ($allowDue && $maxAllowed > 0 && $dueAmount > $maxAllowed) {
            return false;
        }

        return true;
    }

    private function buildErrorResponse(string $message, string $reason): array
    {
        return [
            'success' => false,
            'message' => $message,
            'reason' => $reason,
        ];
    }

    private function buildSuccessResponse(Member $member, AttendanceSession $session, string $status, ?int $rawMinutes = null, ?int $roundedMinutes = null): array
    {
        $remainingHours = null;
        if ($session->subscription) {
            $session->subscription->refresh();
            $remainingHours = $session->subscription->remaining_hours;
        }

        $response = [
            'success' => true,
            'message' => $status === 'checked_in' ? 'Successfully checked in' : 'Successfully checked out',
            'status' => $status,
            'member' => [
                'name' => $member->name,
                'phone' => $member->phone,
            ],
            'remaining_hours' => $remainingHours,
            'session' => [
                'check_in_at' => $session->check_in_at->toIso8601String(),
            ],
        ];

        if ($status === 'checked_out') {
            $response['session']['check_out_at'] = $session->check_out_at->toIso8601String();
            $response['duration_worked_minutes'] = $roundedMinutes;
            $response['duration_worked_hours'] = round($roundedMinutes / 60, 2);
        }

        return $response;
    }
}
