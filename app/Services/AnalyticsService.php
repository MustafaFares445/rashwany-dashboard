<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Enums\SessionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\DailyReportSnapshot;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;

class AnalyticsService
{
    public function generateDailySnapshot(?string $date = null): DailyReportSnapshot
    {
        $snapshotDate = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();

        $sessionsCount = AttendanceSession::query()
            ->whereDate('created_at', $snapshotDate)
            ->count();

        $openSessionsCount = AttendanceSession::query()
            ->where('status', SessionStatus::Open->value)
            ->count();

        $needsReviewSessionsCount = AttendanceSession::query()
            ->where('status', SessionStatus::NeedsReview->value)
            ->count();

        $revenuePaidTotal = (float) Payment::query()
            ->whereIn('status', [PaymentStatus::Paid->value, PaymentStatus::Partial->value])
            ->whereDate('created_at', $snapshotDate)
            ->sum('amount');

        $revenueDueTotal = (float) Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->sum('due_amount');

        $activeMembersCount = Member::query()
            ->where('status', MemberStatus::Active->value)
            ->count();

        $activeSubscriptionsCount = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->count();

        return DailyReportSnapshot::query()->updateOrCreate(
            ['snapshot_date' => $snapshotDate],
            [
                'sessions_count' => $sessionsCount,
                'open_sessions_count' => $openSessionsCount,
                'needs_review_sessions_count' => $needsReviewSessionsCount,
                'revenue_paid_total' => $revenuePaidTotal,
                'revenue_due_total' => $revenueDueTotal,
                'active_members_count' => $activeMembersCount,
                'active_subscriptions_count' => $activeSubscriptionsCount,
            ],
        );
    }
}
