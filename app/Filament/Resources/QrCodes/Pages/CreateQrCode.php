<?php

namespace App\Filament\Resources\QrCodes\Pages;

use App\Filament\Resources\QrCodes\QrCodeResource;
use App\Services\QrCodeService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQrCode extends CreateRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(QrCodeService::class)->create($data);
    }
}
