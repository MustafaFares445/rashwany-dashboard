<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\PaymentService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PaymentService::class)->update(
            payment: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

