<?php

namespace App\Filament\Resources\CorrectionRequests\Pages;

use App\Enums\CorrectionRequestStatus;
use App\Filament\Resources\CorrectionRequests\CorrectionRequestResource;
use App\Services\CorrectionRequestService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCorrectionRequest extends CreateRecord
{
    protected static string $resource = CorrectionRequestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['status'] = $data['status'] ?? CorrectionRequestStatus::Pending->value;

        return app(CorrectionRequestService::class)->create(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

