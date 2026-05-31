<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Services\MemberService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(MemberService::class)->update(
            member: $record,
            data: $data,
            actorId: auth()->id(),
            ipAddress: request()->ip(),
        );
    }
}
