<?php

namespace App\Filament\Resources\CorrectionRequests\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\CorrectionRequests\CorrectionRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCorrectionRequests extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = CorrectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
