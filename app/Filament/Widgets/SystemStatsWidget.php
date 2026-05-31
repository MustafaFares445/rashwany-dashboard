<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Enums\SessionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\Member;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $openSessions = AttendanceSession::query()
            ->where('status', SessionStatus::Open->value)
            ->count();

        $needsReviewSessions = AttendanceSession::query()
            ->where('status', SessionStatus::NeedsReview->value)
            ->count();

        $activeMembers = Member::query()
            ->where('status', MemberStatus::Active->value)
            ->count();

        $activeSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->count();

        $totalDue = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->sum('due_amount');

        return [
            Stat::make('Active members', $activeMembers),
            Stat::make('Open sessions', $openSessions),
            Stat::make('Needs review sessions', $needsReviewSessions),
            Stat::make('Active subscriptions', $activeSubscriptions),
            Stat::make('Total due amount', number_format((float) $totalDue, 2).' USD'),
        ];
    }
}
