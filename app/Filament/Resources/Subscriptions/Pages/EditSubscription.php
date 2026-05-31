<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Services\SubscriptionService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SubscriptionService::class)->update($record, $data);
    }
}
