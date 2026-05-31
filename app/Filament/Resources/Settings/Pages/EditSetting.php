<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Services\SettingsService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SettingsService::class)->update(
            setting: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
