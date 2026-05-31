<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Services\MemberService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(MemberService::class)->create(
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
