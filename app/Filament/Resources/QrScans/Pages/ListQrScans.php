<?php

namespace App\Filament\Resources\QrScans\Pages;

use App\Filament\Resources\QrScans\QrScanResource;
use Filament\Resources\Pages\ListRecords;

class ListQrScans extends ListRecords
{
    protected static string $resource = QrScanResource::class;
}
