<?php

namespace Tests\Feature\Phase3;

use App\Enums\LoyaltyRewardType;
use App\Enums\MemberStatus;
use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use App\Enums\RewardStatus;
use App\Enums\SessionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\DailyReportSnapshot;
use App\Models\LoyaltyRule;
use App\Models\Member;
use App\Models\Package;
use App\Models\Subscription;
use App\Services\AnalyticsService;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyAndMemberPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_granting_free_hours_reward_credits_member_subscription(): void
    {
        [$member, $subscription] = $this->createMemberWithSubscription();

        $subscription->update([
            'used_hours' => 2,
            'remaining_hours' => 18,
        ]);

        $rule = LoyaltyRule::create([
            'name' => '100h usage reward',
            'trigger_type' => 'total_hours',
            'reward_type' => 'free_hours',
            'reward_value' => '2',
            'is_active' => true,
        ]);

        $reward = app(LoyaltyService::class)->createReward([
            'member_id' => $member->id,
            'loyalty_rule_id' => $rule->id,
            'type' => LoyaltyRewardType::FreeHours->value,
            'value' => '2',
            'status' => RewardStatus::Granted->value,
        ]);

        $subscription->refresh();
        $reward->refresh();

        $this->assertSame('0.00', $subscription->used_hours);
        $this->assertSame('20.00', $subscription->remaining_hours);
        $this->assertNotNull($reward->granted_at);
    }

    public function test_member_dashboard_endpoint_returns_subscription_and_open_session(): void
    {
        [$member, $subscription] = $this->createMemberWithSubscription();

        AttendanceSession::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'check_in_at' => now()->subHour(),
            'status' => SessionStatus::Open->value,
        ]);

        $this->getJson('/api/member/dashboard?member_id='.$member->id)
            ->assertOk()
            ->assertJsonPath('member.id', $member->id)
            ->assertJsonPath('inside_now', true)
            ->assertJsonPath('subscription.id', $subscription->id)
            ->assertJsonPath('open_session.status', SessionStatus::Open->value);
    }

    public function test_daily_snapshot_generation_stores_analytics_metrics(): void
    {
        [$member, $subscription] = $this->createMemberWithSubscription();

        AttendanceSession::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'check_in_at' => now()->subHours(2),
            'check_out_at' => now()->subHour(),
            'raw_duration_minutes' => 60,
            'billable_duration_minutes' => 60,
            'status' => SessionStatus::Closed->value,
        ]);

        app(AnalyticsService::class)->generateDailySnapshot();

        $snapshot = DailyReportSnapshot::query()->firstOrFail();

        $this->assertSame(now()->toDateString(), $snapshot->snapshot_date->toDateString());
        $this->assertSame(1, $snapshot->active_members_count);
        $this->assertSame(1, $snapshot->active_subscriptions_count);
    }

    private function createMemberWithSubscription(): array
    {
        $member = Member::create([
            'name' => 'Phase3 Member',
            'phone' => '0933000111',
            'email' => 'phase3@example.test',
            'qr_identifier' => 'PHASE3-QR',
            'status' => MemberStatus::Active->value,
        ]);

        $package = Package::create([
            'name' => 'Monthly 20 Hours',
            'type' => PackageType::HoursMonthly->value,
            'duration_unit' => PackageDurationUnit::Month->value,
            'duration_value' => 1,
            'included_hours' => 20,
            'price' => 100,
            'renewal_type' => PackageRenewalType::Manual->value,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'total_hours' => 20,
            'remaining_hours' => 20,
            'used_hours' => 0,
            'price' => 100,
            'paid_amount' => 100,
            'due_amount' => 0,
            'auto_renew' => false,
        ]);

        return [$member, $subscription];
    }
}
