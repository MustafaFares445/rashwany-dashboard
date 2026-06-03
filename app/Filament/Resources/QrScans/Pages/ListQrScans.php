<?php

namespace App\Filament\Resources\QrScans\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\QrScans\QrScanResource;
use Filament\Resources\Pages\ListRecords;

class ListQrScans extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = QrScanResource::class;
}
