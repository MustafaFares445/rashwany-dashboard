<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use App\Services\PackageService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PackageService::class)->create(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
