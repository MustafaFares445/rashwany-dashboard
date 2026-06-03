<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Concerns\HasBusinessInsightsWidgets;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    use HasBusinessInsightsWidgets;

    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
