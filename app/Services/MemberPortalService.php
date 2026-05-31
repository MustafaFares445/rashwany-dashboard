<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberPortalService
{
    public function __construct(private readonly CorrectionRequestService $correctionRequests)
    {
    }

    public function profile(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'phone' => $member->phone,
            'email' => $member->email,
            'status' => $member->status?->value ?? $member->status,
            'qr_identifier' => $member->qr_identifier,
            'birth_date' => optional($member->birth_date)->toDateString(),
            'notes' => $member->notes,
        ];
    }

    public function dashboard(Member $member): array
    {
        $subscription = $this->subscription($member);
        $openSession = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->where('status', 'open')
            ->latest('check_in_at')
            ->first();

        $lastSession = AttendanceSession::query()
            ->where('member_id', $member->id)
            ->whereNotNull('check_out_at')
            ->latest('check_out_at')
            ->first();

        return [
            'member' => $this->profile($member),
            'subscription' => $subscription ? $this->formatSubscription($subscription) : null,
            'inside_now' => (bool) $openSession,
            'open_session' => $openSession ? $this->formatSession($openSession) : null,
            'last_session' => $lastSession ? $this->formatSession($lastSession) : null,
        ];
    }

    public function subscription(Member $member): ?Subscription
    {
        return Subscription::query()
            ->with('package')
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();
    }

    public function sessions(Member $member, int $perPage = 20): LengthAwarePaginator
    {
        return AttendanceSession::query()
            ->where('member_id', $member->id)
            ->latest('check_in_at')
            ->paginate($perPage);
    }

    public function payments(Member $member, int $perPage = 20): LengthAwarePaginator
    {
        return Payment::query()
            ->where('member_id', $member->id)
            ->latest('paid_at')
            ->paginate($perPage);
    }

    public function createCorrectionRequest(Member $member, array $data): array
    {
        $request = $this->correctionRequests->create([
            'member_id' => $member->id,
            'session_id' => $data['session_id'] ?? null,
            'type' => $data['type'],
            'requested_check_in_at' => $data['requested_check_in_at'] ?? null,
            'requested_check_out_at' => $data['requested_check_out_at'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        return [
            'id' => $request->id,
            'status' => $request->status?->value ?? $request->status,
        ];
    }

    private function formatSubscription(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'status' => $subscription->status?->value ?? $subscription->status,
            'package' => $subscription->package?->name,
            'starts_at' => optional($subscription->starts_at)->toDateTimeString(),
            'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
            'remaining_hours' => $subscription->remaining_hours,
            'used_hours' => $subscription->used_hours,
            'paid_amount' => $subscription->paid_amount,
            'due_amount' => $subscription->due_amount,
        ];
    }

    private function formatSession(AttendanceSession $session): array
    {
        return [
            'id' => $session->id,
            'status' => $session->status?->value ?? $session->status,
            'check_in_at' => optional($session->check_in_at)->toDateTimeString(),
            'check_out_at' => optional($session->check_out_at)->toDateTimeString(),
            'raw_duration_minutes' => $session->raw_duration_minutes,
            'billable_duration_minutes' => $session->billable_duration_minutes,
        ];
    }
}

