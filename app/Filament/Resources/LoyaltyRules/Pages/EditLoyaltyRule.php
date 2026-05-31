<?php

namespace App\Filament\Resources\LoyaltyRules\Pages;

use App\Filament\Resources\LoyaltyRules\LoyaltyRuleResource;
use App\Services\LoyaltyService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLoyaltyRule extends EditRecord
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(LoyaltyService::class)->updateRule(
            rule: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

