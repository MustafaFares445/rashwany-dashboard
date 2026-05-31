<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use App\Services\LoyaltyService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditReward extends EditRecord
{
    protected static string $resource = RewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(LoyaltyService::class)->updateReward(
            reward: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

