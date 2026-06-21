<?php

namespace App\Services;

use App\Enums\PackageType;
use App\Enums\SubscriptionStatus;
use App\Enums\PackageRenewalType;
use App\Enums\PackageDurationUnit;
use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Package;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function create(array $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            $member = Member::query()->findOrFail($data['member_id']);
            $package = Package::query()->findOrFail($data['package_id']);

            $startsAt = isset($data['starts_at'])
                ? Carbon::parse($data['starts_at'])
                : now();

            $status = $data['status'] ?? SubscriptionStatus::Active->value;

            if ($status === SubscriptionStatus::Active->value) {
                Subscription::query()
                    ->where('member_id', $member->id)
                    ->where('status', SubscriptionStatus::Active->value)
                    ->update([
                        'status' => SubscriptionStatus::Expired->value,
                        'ends_at' => now(),
                    ]);
            }

            $price = $data['price'] ?? $package->price ?? 0;
            $paidAmount = $data['paid_amount'] ?? 0;
            $dueAmount = max(0, $price - $paidAmount);

            $totalHours = $data['total_hours'] ?? $this->defaultTotalHours($package);
            $remainingHours = $data['remaining_hours'] ?? $totalHours;
            $usedHours = $data['used_hours'] ?? 0;

            $subscription = Subscription::create([
                'member_id' => $member->id,
                'package_id' => $package->id,
                'status' => $status,
                'starts_at' => $startsAt,
                'ends_at' => $data['ends_at'] ?? $this->calculateEndsAt($package, $startsAt),
                'total_hours' => $totalHours,
                'remaining_hours' => $remainingHours,
                'used_hours' => $usedHours,
                'price' => $price,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'auto_renew' => $data['auto_renew'] ?? (
                    ($package->renewal_type?->value ?? $package->renewal_type) === PackageRenewalType::Automatic->value
                ),
            ]);

            $this->audit->log(
                action: 'subscription_created',
                entityType: 'subscription',
                entityId: $subscription->id,
                newValues: $subscription->toArray(),
            );

            return $subscription;
        });
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $before = $subscription->replicate();

        if (($data['status'] ?? null) === SubscriptionStatus::Active->value
            && $subscription->status !== SubscriptionStatus::Active
        ) {
            Subscription::query()
                ->where('member_id', $subscription->member_id)
                ->where('status', SubscriptionStatus::Active->value)
                ->where('id', '!=', $subscription->id)
                ->update([
                    'status' => SubscriptionStatus::Expired->value,
                    'ends_at' => now(),
                ]);
        }

        $price = $data['price'] ?? $subscription->price;
        $paidAmount = $data['paid_amount'] ?? $subscription->paid_amount;
        $data['due_amount'] = max(0, $price - $paidAmount);

        $subscription->update($data);

        $this->audit->log(
            action: 'subscription_updated',
            entityType: 'subscription',
            entityId: $subscription->id,
            oldValues: $before->toArray(),
            newValues: $subscription->toArray(),
        );

        return $subscription;
    }

    public function getActiveSubscription(Member $member, ?Carbon $at = null): ?Subscription
    {
        $at = $at ?? now();

        return Subscription::query()
            ->where('member_id', $member->id)
            ->where('status', SubscriptionStatus::Active->value)
            ->where(function ($query) use ($at) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            })
            ->latest('starts_at')
            ->first();
    }

    public function ensureCanCheckIn(Member $member, ?Carbon $at = null): ?Subscription
    {
        if ($member->status === MemberStatus::Blocked) {
            return null;
        }

        if ($member->status === MemberStatus::Inactive) {
            return null;
        }

        return $this->getActiveSubscription($member, $at);
    }

    public function isHourBased(Package $package): bool
    {
        return in_array($package->type, [
            PackageType::Hourly,
            PackageType::HoursWeekly,
            PackageType::HoursMonthly,
        ], true);
    }

    public function isUnlimited(Package $package): bool
    {
        return in_array($package->type, [
            PackageType::UnlimitedWeekly,
            PackageType::UnlimitedMonthly,
        ], true);
    }

    public function applyUsage(Subscription $subscription, float $hours): Subscription
    {
        $hours = round(max(0, $hours), 4);

        $subscription->used_hours = round(((float) $subscription->used_hours) + $hours, 4);

        if ($subscription->remaining_hours !== null) {
            $subscription->remaining_hours = max(0, round(((float) $subscription->remaining_hours) - $hours, 4));
        }

        $subscription->save();

        return $subscription;
    }

    public function adjustUsage(Subscription $subscription, float $deltaHours): Subscription
    {
        $deltaHours = round($deltaHours, 4);
        $subscription->used_hours = max(0, round(((float) $subscription->used_hours) + $deltaHours, 4));

        if ($subscription->remaining_hours !== null) {
            $remaining = round(((float) $subscription->remaining_hours) - $deltaHours, 4);

            if ($subscription->total_hours !== null) {
                $remaining = min($subscription->total_hours, $remaining);
            }

            $subscription->remaining_hours = max(0, $remaining);
        }

        $subscription->save();

        return $subscription;
    }

    private function defaultTotalHours(Package $package): ?float
    {
        if (! $this->isHourBased($package)) {
            return null;
        }

        return $package->included_hours !== null ? (float) $package->included_hours : null;
    }

    private function calculateEndsAt(Package $package, Carbon $startsAt): ?Carbon
    {
        if (! $package->duration_unit || ! $package->duration_value) {
            return null;
        }

        $durationUnit = $package->duration_unit instanceof PackageDurationUnit
            ? $package->duration_unit->value
            : $package->duration_unit;

        return match ($durationUnit) {
            'hour' => $startsAt->copy()->addHours((int) $package->duration_value),
            'week' => $startsAt->copy()->addWeeks((int) $package->duration_value),
            'month' => $startsAt->copy()->addMonths((int) $package->duration_value),
            default => null,
        };
    }
}
