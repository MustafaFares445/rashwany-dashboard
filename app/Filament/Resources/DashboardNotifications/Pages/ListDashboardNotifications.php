<?php

namespace App\Filament\Resources\DashboardNotifications\Pages;

use App\Filament\Resources\DashboardNotifications\DashboardNotificationResource;
use Filament\Resources\Pages\ListRecords;

class ListDashboardNotifications extends ListRecords
{
    protected static string $resource = DashboardNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
