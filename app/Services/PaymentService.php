<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PaymentDueNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PaymentService
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function create(array $data, ?int $actorId = null, ?string $ipAddress = null): Payment
    {
        return DB::transaction(function () use ($data, $actorId, $ipAddress) {
            $payment = Payment::create($data);

            $this->applyPaymentToSubscription($payment);
            $this->notifyDueIfNeeded($payment);

            $this->audit->log(
                action: 'payment_created',
                entityType: 'payment',
                entityId: $payment->id,
                newValues: $payment->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            return $payment;
        });
    }

    public function update(Payment $payment, array $data, ?int $actorId = null, ?string $ipAddress = null): Payment
    {
        return DB::transaction(function () use ($payment, $data, $actorId, $ipAddress) {
            $before = $payment->replicate();

            $payment->update($data);

            $this->syncSubscriptionForUpdate($before, $payment);
            $this->notifyDueIfNeeded($payment);

            $this->audit->log(
                action: 'payment_updated',
                entityType: 'payment',
                entityId: $payment->id,
                oldValues: $before->toArray(),
                newValues: $payment->toArray(),
                actorId: $actorId,
                ipAddress: $ipAddress,
            );

            return $payment;
        });
    }

    private function applyPaymentToSubscription(Payment $payment): void
    {
        if (! $payment->subscription) {
            return;
        }

        $delta = $this->appliedAmount($payment->status, (float) $payment->amount);
        if ($delta === 0.0) {
            return;
        }

        $this->applySubscriptionDelta($payment->subscription, $delta);
    }

    private function syncSubscriptionForUpdate(Payment $before, Payment $after): void
    {
        $oldApplied = $this->appliedAmount($before->status, (float) $before->amount);
        $newApplied = $this->appliedAmount($after->status, (float) $after->amount);

        $beforeSubscription = $before->subscription_id ? Subscription::query()->find($before->subscription_id) : null;
        $afterSubscription = $after->subscription_id ? Subscription::query()->find($after->subscription_id) : null;

        if ($before->subscription_id === $after->subscription_id) {
            if (! $afterSubscription) {
                return;
            }

            $delta = $newApplied - $oldApplied;
            if ($delta === 0.0) {
                return;
            }

            $this->applySubscriptionDelta($afterSubscription, $delta);

            return;
        }

        if ($beforeSubscription && $oldApplied !== 0.0) {
            $this->applySubscriptionDelta($beforeSubscription, -$oldApplied);
        }

        if ($afterSubscription && $newApplied !== 0.0) {
            $this->applySubscriptionDelta($afterSubscription, $newApplied);
        }
    }

    private function applySubscriptionDelta(Subscription $subscription, float $delta): void
    {
        $subscription->paid_amount = max(0, round(((float) $subscription->paid_amount) + $delta, 2));
        $subscription->due_amount = max(0, round(((float) $subscription->price) - $subscription->paid_amount, 2));
        $subscription->save();
    }

    private function appliedAmount(PaymentStatus $status, float $amount): float
    {
        return match ($status) {
            PaymentStatus::Paid, PaymentStatus::Partial => $amount,
            PaymentStatus::Refunded => -$amount,
            default => 0.0,
        };
    }

    private function notifyDueIfNeeded(Payment $payment): void
    {
        if (! $payment->member || ! $payment->subscription) {
            return;
        }

        $dueAmount = (float) $payment->subscription->due_amount;
        if ($dueAmount <= 0) {
            return;
        }

        Notification::send(
            User::query()->get(),
            new PaymentDueNotification($payment->member, $dueAmount),
        );
    }
}
