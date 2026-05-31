<?php

namespace App\Filament\Resources\CorrectionRequests;

use App\Filament\Resources\CorrectionRequests\Pages\CreateCorrectionRequest;
use App\Filament\Resources\CorrectionRequests\Pages\EditCorrectionRequest;
use App\Filament\Resources\CorrectionRequests\Pages\ListCorrectionRequests;
use App\Filament\Resources\CorrectionRequests\Schemas\CorrectionRequestForm;
use App\Filament\Resources\CorrectionRequests\Tables\CorrectionRequestsTable;
use App\Models\CorrectionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestResource extends Resource
{
    protected static ?string $model = CorrectionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return CorrectionRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CorrectionRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCorrectionRequests::route('/'),
            'create' => CreateCorrectionRequest::route('/create'),
            'edit' => EditCorrectionRequest::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}

