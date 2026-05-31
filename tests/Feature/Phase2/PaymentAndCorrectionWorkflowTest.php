<?php

namespace Tests\Feature\Phase2;

use App\Enums\CorrectionRequestStatus;
use App\Enums\CorrectionRequestType;
use App\Enums\MemberStatus;
use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SessionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\CorrectionRequest;
use App\Models\Member;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CorrectionRequestService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndCorrectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_service_updates_subscription_paid_and_due_amounts(): void
    {
        [$member, $subscription] = $this->createMemberWithHourSubscription();

        $payment = app(PaymentService::class)->create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'amount' => 40,
            'currency' => 'USD',
            'payment_method' => PaymentMethod::Cash->value,
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now(),
        ], actorId: 10, ipAddress: '127.0.0.1');

        $subscription->refresh();

        $this->assertSame('40.00', $subscription->paid_amount);
        $this->assertSame('60.00', $subscription->due_amount);

        app(PaymentService::class)->update($payment, [
            'status' => PaymentStatus::Refunded->value,
        ], actorId: 10, ipAddress: '127.0.0.1');

        $subscription->refresh();

        $this->assertSame('0.00', $subscription->paid_amount);
        $this->assertSame('100.00', $subscription->due_amount);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_created',
            'entity_type' => 'payment',
            'entity_id' => $payment->id,
        ]);
    }

    public function test_approving_correction_request_adjusts_session_and_subscription_usage(): void
    {
        [$member, $subscription] = $this->createMemberWithHourSubscription();
        $reviewer = User::factory()->create();

        $subscription->update([
            'used_hours' => 1,
            'remaining_hours' => 19,
        ]);

        $session = AttendanceSession::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'check_in_at' => now()->subHours(2),
            'check_out_at' => now()->subHour(),
            'raw_duration_minutes' => 60,
            'billable_duration_minutes' => 60,
            'rounded_from_at' => now()->subHours(2),
            'rounded_to_at' => now()->subHour(),
            'status' => SessionStatus::Closed->value,
        ]);

        $request = CorrectionRequest::create([
            'member_id' => $member->id,
            'session_id' => $session->id,
            'type' => CorrectionRequestType::ForgotCheckOut->value,
            'requested_check_in_at' => now()->subHours(2),
            'requested_check_out_at' => now(),
            'message' => 'Forgot to check out on time.',
            'status' => CorrectionRequestStatus::Pending->value,
        ]);

        app(CorrectionRequestService::class)->update($request, [
            'status' => CorrectionRequestStatus::Approved->value,
            'admin_note' => 'Approved after review.',
        ], actorId: $reviewer->id, ipAddress: '127.0.0.1');

        $session->refresh();
        $subscription->refresh();

        $this->assertSame(SessionStatus::Corrected->value, $session->status->value);
        $this->assertSame(120, $session->billable_duration_minutes);
        $this->assertSame($request->id, $session->correction_request_id);
        $this->assertSame('2.00', $subscription->used_hours);
        $this->assertSame('18.00', $subscription->remaining_hours);
        $this->assertTrue(AuditLog::query()->where('action', 'correction_request_updated')->exists());
    }

    private function createMemberWithHourSubscription(): array
    {
        $member = Member::create([
            'name' => 'Phase2 Member',
            'phone' => (string) fake()->numberBetween(100000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'qr_identifier' => (string) fake()->uuid(),
            'status' => MemberStatus::Active->value,
        ]);

        $package = Package::create([
            'name' => 'Phase2 Package',
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
            'paid_amount' => 0,
            'due_amount' => 100,
            'auto_renew' => false,
        ]);

        return [$member, $subscription];
    }
}
