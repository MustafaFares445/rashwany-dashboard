<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Support\Facades\Notification;

class SubscriptionLifecycleService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function expireEndedSubscriptions(): int
    {
        return Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update([
                'status' => SubscriptionStatus::Expired->value,
            ]);
    }

    public function notifyExpiringSubscriptions(): int
    {
        $days = max(1, $this->settings->getInt('notify_member_before_subscription_expiry_days'));
        $endWindow = now()->copy()->addDays($days);

        $subscriptions = Subscription::query()
            ->with('member')
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), $endWindow])
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $admins = User::query()->get();
        foreach ($subscriptions as $subscription) {
            Notification::send($admins, new SubscriptionExpiringNotification($subscription));
        }

        return $subscriptions->count();
    }
}

