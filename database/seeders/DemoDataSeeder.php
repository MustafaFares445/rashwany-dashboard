<?php

namespace Database\Seeders;

use App\Enums\CorrectionRequestStatus;
use App\Enums\CorrectionRequestType;
use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use App\Enums\MemberStatus;
use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\QrPurpose;
use App\Enums\RewardStatus;
use App\Enums\SessionStatus;
use App\Enums\SettingType;
use App\Enums\SubscriptionStatus;
use App\Models\AttendanceSession;
use App\Models\CorrectionRequest;
use App\Models\LoyaltyRule;
use App\Models\Member;
use App\Models\Package;
use App\Models\Payment;
use App\Models\QrCode;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if (! Hash::check('password', $admin->password)) {
            $admin->update(['password' => Hash::make('password')]);
        }

        $this->seedSettings();
        $packages = $this->seedPackages();
        $members = $this->seedMembers();
        $subscriptions = $this->seedSubscriptions($members, $packages);
        $this->seedQrCodes();
        $sessions = $this->seedSessions($members, $subscriptions);
        $this->seedPayments($members, $subscriptions, $admin->id);
        $this->seedCorrectionRequests($members, $sessions, $admin->id);
        $this->seedLoyalty($members, $subscriptions);
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'hour_rounding_interval_minutes', 'value' => '30', 'type' => SettingType::Integer->value, 'group' => 'time'],
            ['key' => 'hour_rounding_threshold_minutes', 'value' => '15', 'type' => SettingType::Integer->value, 'group' => 'time'],
            ['key' => 'open_session_review_after_hours', 'value' => '14', 'type' => SettingType::Integer->value, 'group' => 'time'],
            ['key' => 'max_normal_billable_session_hours', 'value' => '8', 'type' => SettingType::Integer->value, 'group' => 'time'],
            ['key' => 'allow_check_in_with_due_amount', 'value' => '1', 'type' => SettingType::Boolean->value, 'group' => 'payment'],
            ['key' => 'max_allowed_due_amount', 'value' => '25', 'type' => SettingType::Decimal->value, 'group' => 'payment'],
            ['key' => 'notify_admin_for_abnormal_sessions', 'value' => '1', 'type' => SettingType::Boolean->value, 'group' => 'notifications'],
            ['key' => 'notify_member_before_subscription_expiry_days', 'value' => '2', 'type' => SettingType::Integer->value, 'group' => 'notifications'],
            ['key' => 'default_currency', 'value' => 'USD', 'type' => SettingType::String->value, 'group' => 'general'],
            ['key' => 'auto_close_abnormal_sessions', 'value' => '0', 'type' => SettingType::Boolean->value, 'group' => 'time'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                Arr::only($setting, ['value', 'type', 'group']) + [
                    'description' => null,
                    'is_public' => false,
                ],
            );
        }
    }

    private function seedPackages(): array
    {
        $payload = [
            [
                'name' => 'Hourly Access',
                'type' => PackageType::Hourly->value,
                'duration_unit' => PackageDurationUnit::Hour->value,
                'duration_value' => 1,
                'included_hours' => null,
                'price' => 0.50,
                'renewal_type' => PackageRenewalType::Manual->value,
            ],
            [
                'name' => '20 Hours Weekly',
                'type' => PackageType::HoursWeekly->value,
                'duration_unit' => PackageDurationUnit::Week->value,
                'duration_value' => 1,
                'included_hours' => 20,
                'price' => 35,
                'renewal_type' => PackageRenewalType::Manual->value,
            ],
            [
                'name' => '50 Hours Monthly',
                'type' => PackageType::HoursMonthly->value,
                'duration_unit' => PackageDurationUnit::Month->value,
                'duration_value' => 1,
                'included_hours' => 50,
                'price' => 80,
                'renewal_type' => PackageRenewalType::Manual->value,
            ],
            [
                'name' => 'Weekly Unlimited',
                'type' => PackageType::UnlimitedWeekly->value,
                'duration_unit' => PackageDurationUnit::Week->value,
                'duration_value' => 1,
                'included_hours' => null,
                'price' => 45,
                'renewal_type' => PackageRenewalType::Manual->value,
            ],
        ];

        $packages = [];
        foreach ($payload as $item) {
            $packages[$item['name']] = Package::query()->updateOrCreate(
                ['name' => $item['name']],
                $item + [
                    'is_active' => true,
                    'settings_json' => null,
                ],
            );
        }

        return $packages;
    }

    private function seedMembers(): array
    {
        $payload = [
            ['name' => 'Ahmad Salem', 'phone' => '0999100001', 'email' => 'ahmad.salem@example.com', 'qr_identifier' => 'MEM-1001'],
            ['name' => 'Rana Khaled', 'phone' => '0999100002', 'email' => 'rana.khaled@example.com', 'qr_identifier' => 'MEM-1002'],
            ['name' => 'Yazan Nasser', 'phone' => '0999100003', 'email' => 'yazan.nasser@example.com', 'qr_identifier' => 'MEM-1003'],
            ['name' => 'Lama Omar', 'phone' => '0999100004', 'email' => 'lama.omar@example.com', 'qr_identifier' => 'MEM-1004'],
            ['name' => 'Sara Adel', 'phone' => '0999100005', 'email' => 'sara.adel@example.com', 'qr_identifier' => 'MEM-1005'],
        ];

        $members = [];
        foreach ($payload as $item) {
            $members[$item['name']] = Member::query()->updateOrCreate(
                ['phone' => $item['phone']],
                $item + [
                    'status' => MemberStatus::Active->value,
                    'birth_date' => now()->subYears(rand(22, 35))->toDateString(),
                    'notes' => 'Seeded demo member',
                ],
            );
        }

        return $members;
    }

    private function seedSubscriptions(array $members, array $packages): array
    {
        $payload = [
            [
                'member' => 'Ahmad Salem',
                'package' => '20 Hours Weekly',
                'status' => SubscriptionStatus::Active->value,
                'total_hours' => 20,
                'remaining_hours' => 12.5,
                'used_hours' => 7.5,
                'price' => 35,
                'paid_amount' => 35,
                'due_amount' => 0,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(5),
            ],
            [
                'member' => 'Rana Khaled',
                'package' => '50 Hours Monthly',
                'status' => SubscriptionStatus::Active->value,
                'total_hours' => 50,
                'remaining_hours' => 31,
                'used_hours' => 19,
                'price' => 80,
                'paid_amount' => 60,
                'due_amount' => 20,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
            ],
            [
                'member' => 'Yazan Nasser',
                'package' => 'Weekly Unlimited',
                'status' => SubscriptionStatus::Active->value,
                'total_hours' => null,
                'remaining_hours' => null,
                'used_hours' => 0,
                'price' => 45,
                'paid_amount' => 45,
                'due_amount' => 0,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(4),
            ],
            [
                'member' => 'Lama Omar',
                'package' => 'Hourly Access',
                'status' => SubscriptionStatus::Active->value,
                'total_hours' => null,
                'remaining_hours' => null,
                'used_hours' => 0,
                'price' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'starts_at' => now()->subDays(1),
                'ends_at' => null,
            ],
        ];

        $subscriptions = [];
        foreach ($payload as $item) {
            $member = $members[$item['member']];
            $package = $packages[$item['package']];

            $subscriptions[$item['member']] = Subscription::query()->updateOrCreate(
                ['member_id' => $member->id, 'status' => SubscriptionStatus::Active->value],
                [
                    'package_id' => $package->id,
                    'status' => $item['status'],
                    'starts_at' => $item['starts_at'],
                    'ends_at' => $item['ends_at'],
                    'total_hours' => $item['total_hours'],
                    'remaining_hours' => $item['remaining_hours'],
                    'used_hours' => $item['used_hours'],
                    'price' => $item['price'],
                    'paid_amount' => $item['paid_amount'],
                    'due_amount' => $item['due_amount'],
                    'auto_renew' => false,
                ],
            );
        }

        return $subscriptions;
    }

    private function seedQrCodes(): void
    {
        $codes = [
            ['name' => 'Main Gate Check-In', 'purpose' => QrPurpose::CheckIn->value, 'token' => 'seed-check-in-main'],
            ['name' => 'Main Gate Check-Out', 'purpose' => QrPurpose::CheckOut->value, 'token' => 'seed-check-out-main'],
        ];

        foreach ($codes as $code) {
            QrCode::query()->updateOrCreate(
                ['name' => $code['name']],
                [
                    'purpose' => $code['purpose'],
                    'location_id' => 'main-office',
                    'office_area_id' => 'gate-a',
                    'token_hash' => hash('sha256', $code['token']),
                    'is_active' => true,
                    'expires_at' => null,
                ],
            );
        }
    }

    private function seedSessions(array $members, array $subscriptions): array
    {
        $sessions = [];

        $sessions[] = AttendanceSession::query()->updateOrCreate(
            ['member_id' => $members['Ahmad Salem']->id, 'check_in_at' => now()->subHours(7)],
            [
                'subscription_id' => $subscriptions['Ahmad Salem']->id,
                'check_out_at' => now()->subHours(4),
                'raw_duration_minutes' => 180,
                'billable_duration_minutes' => 180,
                'rounded_from_at' => now()->subHours(7),
                'rounded_to_at' => now()->subHours(4),
                'status' => SessionStatus::Closed->value,
                'notes' => 'Normal completed session',
            ],
        );

        $sessions[] = AttendanceSession::query()->updateOrCreate(
            ['member_id' => $members['Rana Khaled']->id, 'check_in_at' => now()->subHours(3)],
            [
                'subscription_id' => $subscriptions['Rana Khaled']->id,
                'check_out_at' => null,
                'raw_duration_minutes' => null,
                'billable_duration_minutes' => null,
                'rounded_from_at' => null,
                'rounded_to_at' => null,
                'status' => SessionStatus::Open->value,
                'notes' => 'Currently inside',
            ],
        );

        return $sessions;
    }

    private function seedPayments(array $members, array $subscriptions, int $adminId): void
    {
        $payments = [
            [
                'member' => 'Ahmad Salem',
                'amount' => 35,
                'status' => PaymentStatus::Paid->value,
                'method' => PaymentMethod::Cash->value,
                'paid_at' => now()->subDays(2),
            ],
            [
                'member' => 'Rana Khaled',
                'amount' => 60,
                'status' => PaymentStatus::Partial->value,
                'method' => PaymentMethod::BankTransfer->value,
                'paid_at' => now()->subDays(9),
                'due_at' => now()->addDays(5),
            ],
        ];

        foreach ($payments as $item) {
            $member = $members[$item['member']];
            $subscription = $subscriptions[$item['member']];

            Payment::query()->updateOrCreate(
                ['member_id' => $member->id, 'subscription_id' => $subscription->id, 'amount' => $item['amount']],
                [
                    'currency' => 'USD',
                    'payment_method' => $item['method'],
                    'status' => $item['status'],
                    'paid_at' => $item['paid_at'],
                    'due_at' => $item['due_at'] ?? null,
                    'notes' => 'Seeded demo payment',
                    'created_by' => $adminId,
                ],
            );
        }
    }

    private function seedCorrectionRequests(array $members, array $sessions, int $adminId): void
    {
        $targetSession = $sessions[0];
        $member = $members['Ahmad Salem'];

        CorrectionRequest::query()->updateOrCreate(
            ['member_id' => $member->id, 'session_id' => $targetSession->id],
            [
                'type' => CorrectionRequestType::ForgotCheckOut->value,
                'requested_check_in_at' => $targetSession->check_in_at,
                'requested_check_out_at' => $targetSession->check_out_at?->copy()->addMinutes(30),
                'message' => 'Please add 30 minutes, forgot to check out.',
                'status' => CorrectionRequestStatus::Pending->value,
                'admin_note' => null,
                'reviewed_by' => $adminId,
                'reviewed_at' => null,
            ],
        );
    }

    private function seedLoyalty(array $members, array $subscriptions): void
    {
        $rule = LoyaltyRule::query()->updateOrCreate(
            ['name' => '100 Hours Bonus'],
            [
                'trigger_type' => LoyaltyTriggerType::TotalHours->value,
                'threshold_hours' => 100,
                'threshold_visits' => null,
                'threshold_subscription_months' => null,
                'period_months' => 2,
                'description' => 'Auto-detect members who reach 100 attendance hours within 2 months. Admin activation is required.',
                'reward_type' => LoyaltyRewardType::FreeHours->value,
                'reward_value' => '2',
                'is_active' => true,
            ],
        );

        $member = $members['Rana Khaled'];
        $member->rewards()->updateOrCreate(
            ['loyalty_rule_id' => $rule->id, 'type' => LoyaltyRewardType::FreeHours->value],
            [
                'subscription_id' => $subscriptions['Rana Khaled']->id,
                'value' => '2',
                'status' => RewardStatus::Pending->value,
                'granted_at' => null,
                'activated_at' => null,
                'notes' => 'Eligible soon based on usage. Admin activation is required.',
            ],
        );
    }
}
