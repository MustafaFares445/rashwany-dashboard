<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\Rewards\RewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRewards extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = RewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
