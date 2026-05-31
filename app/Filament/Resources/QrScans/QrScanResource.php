<?php

namespace App\Filament\Resources\QrScans;

use App\Filament\Resources\QrScans\Pages\ListQrScans;
use App\Filament\Resources\QrScans\Schemas\QrScanForm;
use App\Filament\Resources\QrScans\Tables\QrScansTable;
use App\Models\QrScan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QrScanResource extends Resource
{
    protected static ?string $model = QrScan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return QrScanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QrScansTable::configure($table);
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
            'index' => ListQrScans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
