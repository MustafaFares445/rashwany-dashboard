<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodes\QrCodeResource;
use App\Services\QrCodeService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQrCode extends EditRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(QrCodeService::class)->update($record, $data);
    }
}
