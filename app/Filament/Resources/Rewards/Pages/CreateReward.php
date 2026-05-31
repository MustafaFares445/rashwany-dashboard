<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use App\Services\LoyaltyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReward extends CreateRecord
{
    protected static string $resource = RewardResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(LoyaltyService::class)->createReward(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

