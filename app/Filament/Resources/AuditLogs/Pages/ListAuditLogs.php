<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = AuditLogResource::class;
}
