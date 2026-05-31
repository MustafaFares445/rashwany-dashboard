<?php

namespace App\Filament\Resources\CorrectionRequests\Pages;

use App\Filament\Resources\CorrectionRequests\CorrectionRequestResource;
use App\Services\CorrectionRequestService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCorrectionRequest extends EditRecord
{
    protected static string $resource = CorrectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(CorrectionRequestService::class)->update(
            request: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

