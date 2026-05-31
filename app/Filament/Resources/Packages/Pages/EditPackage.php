<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use App\Services\PackageService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PackageService::class)->update(
            package: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
