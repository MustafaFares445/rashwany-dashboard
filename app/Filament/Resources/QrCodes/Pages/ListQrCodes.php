<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\QrCodes\QrCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQrCodes extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
