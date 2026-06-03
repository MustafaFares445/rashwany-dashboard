<?php

namespace App\Filament\Resources\LoyaltyRules\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\LoyaltyRules\LoyaltyRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyRules extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = LoyaltyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
