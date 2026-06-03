<?php

namespace App\Filament\Resources\AttendanceSessions\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\AttendanceSessions\AttendanceSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceSessions extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = AttendanceSessionResource::class;
}
