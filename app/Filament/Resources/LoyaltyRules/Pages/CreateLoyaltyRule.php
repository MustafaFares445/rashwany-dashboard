<?php

namespace App\Filament\Resources\LoyaltyRules\Pages;

use App\Filament\Resources\LoyaltyRules\LoyaltyRuleResource;
use App\Services\LoyaltyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLoyaltyRule extends CreateRecord
{
    protected static string $resource = LoyaltyRuleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(LoyaltyService::class)->createRule(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

