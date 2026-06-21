<?php

namespace App\Filament\Resources\AttendanceSessions\Pages;

use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Services\SubscriptionService;
use App\Services\TimeRoundingService;
use Carbon\Carbon;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditAttendanceSession extends EditRecord
{
    protected static string $resource = AttendanceSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $oldBillableMinutes = (int) ($record->billable_duration_minutes ?? 0);

            $record->update($this->prepareSessionUpdateData($data));

            $newBillableMinutes = (int) ($record->billable_duration_minutes ?? 0);
            $this->syncSubscriptionUsage($record, $oldBillableMinutes, $newBillableMinutes);

            return $record;
        });
    }

    private function prepareSessionUpdateData(array $data): array
    {
        $data['admin_updated_by'] = auth()->id();
        $data['admin_updated_at'] = now();

        if (empty($data['check_in_at']) || empty($data['check_out_at'])) {
            $data['raw_duration_minutes'] = null;
            $data['billable_duration_minutes'] = null;
            $data['rounded_from_at'] = null;
            $data['rounded_to_at'] = null;

            return $data;
        }

        $checkInAt = Carbon::parse($data['check_in_at']);
        $checkOutAt = Carbon::parse($data['check_out_at']);

        $durations = app(TimeRoundingService::class)->calculateDurations($checkInAt, $checkOutAt);

        $data['raw_duration_minutes'] = $durations['raw_minutes'];
        $data['billable_duration_minutes'] = $durations['rounded_minutes'];
        $data['rounded_from_at'] = $checkInAt;
        $data['rounded_to_at'] = $durations['rounded_to_at'];

        return $data;
    }

    private function syncSubscriptionUsage(Model $record, int $oldBillableMinutes, int $newBillableMinutes): void
    {
        if (! $record->subscription) {
            return;
        }

        $subscriptions = app(SubscriptionService::class);

        if (! $subscriptions->isHourBased($record->subscription->package)) {
            return;
        }

        $deltaHours = round(($newBillableMinutes - $oldBillableMinutes) / 60, 2);

        if ($deltaHours === 0.0) {
            return;
        }

        $subscriptions->adjustUsage($record->subscription, $deltaHours);
    }
}
