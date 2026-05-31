<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\PaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        return app(PaymentService::class)->create(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}

