<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Services\SubscriptionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SubscriptionService::class)->create($data);
    }
}
