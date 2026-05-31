<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Services\SettingsService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SettingsService::class)->create(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
