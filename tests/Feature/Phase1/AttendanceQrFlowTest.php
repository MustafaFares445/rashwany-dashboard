<?php

namespace Tests\Feature\Phase1;

use App\Enums\MemberStatus;
use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use App\Enums\QrPurpose;
use App\Enums\SessionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\Member;
use App\Models\Package;
use App\Models\QrCode;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceQrFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_check_in_and_check_out_and_hours_are_deducted(): void
    {
        $member = Member::create([
            'name' => 'Member One',
            'phone' => '0999000001',
            'email' => 'member1@example.test',
            'qr_identifier' => 'MEMBER-1',
            'status' => MemberStatus::Active->value,
        ]);

        $package = Package::create([
            'name' => 'Weekly 20 Hours',
            'type' => PackageType::HoursWeekly->value,
            'duration_unit' => PackageDurationUnit::Week->value,
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
            'ends_at' => now()->addWeek(),
            'total_hours' => 20,
            'remaining_hours' => 20,
            'used_hours' => 0,
            'price' => 100,
            'paid_amount' => 100,
            'due_amount' => 0,
            'auto_renew' => false,
        ]);

        QrCode::create([
            'name' => 'Check In',
            'purpose' => QrPurpose::CheckIn->value,
            'token_hash' => hash('sha256', 'token-check-in'),
            'is_active' => true,
        ]);

        QrCode::create([
            'name' => 'Check Out',
            'purpose' => QrPurpose::CheckOut->value,
            'token_hash' => hash('sha256', 'token-check-out'),
            'is_active' => true,
        ]);

        $checkInAt = now()->subMinutes(76);

        $this->postJson('/api/qr-scan', [
            'member_id' => $member->id,
            'qr_token' => 'token-check-in',
            'scanned_at' => $checkInAt->toDateTimeString(),
        ])->assertOk()->assertJson([
            'result' => 'success',
            'failure_reason' => null,
        ]);

        $this->postJson('/api/qr-scan', [
            'member_id' => $member->id,
            'qr_token' => 'token-check-out',
            'scanned_at' => now()->toDateTimeString(),
        ])->assertOk()->assertJson([
            'result' => 'success',
            'failure_reason' => null,
        ]);

        $session = AttendanceSession::query()->latest('id')->firstOrFail();
        $subscription->refresh();

        $this->assertSame(SessionStatus::Closed->value, $session->status->value);
        $this->assertSame(76, $session->raw_duration_minutes);
        $this->assertSame(90, $session->billable_duration_minutes);
        $this->assertSame('1.50', $subscription->used_hours);
        $this->assertSame('18.50', $subscription->remaining_hours);
    }

    public function test_check_in_is_rejected_when_member_has_no_active_subscription(): void
    {
        $member = Member::create([
            'name' => 'Member Two',
            'phone' => '0999000002',
            'email' => 'member2@example.test',
            'qr_identifier' => 'MEMBER-2',
            'status' => MemberStatus::Active->value,
        ]);

        QrCode::create([
            'name' => 'Check In',
            'purpose' => QrPurpose::CheckIn->value,
            'token_hash' => hash('sha256', 'token-check-in-only'),
            'is_active' => true,
        ]);

        $this->postJson('/api/qr-scan', [
            'member_id' => $member->id,
            'qr_token' => 'token-check-in-only',
        ])->assertStatus(422)->assertJson([
            'result' => 'rejected',
            'failure_reason' => 'subscription_expired',
        ]);
    }
}

