<?php

namespace App\Filament\Resources\QrCodes\Schemas;

use App\Enums\QrPurpose;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('purpose')
                    ->required()
                    ->options(self::purposeOptions()),
                TextInput::make('token')
                    ->label('Token')
                    ->required(fn (string $context) => $context === 'create')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Store this token securely for QR generation.'),
                TextInput::make('location_id')
                    ->maxLength(255),
                TextInput::make('office_area_id')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                DateTimePicker::make('expires_at'),
            ]);
    }

    private static function purposeOptions(): array
    {
        return collect(QrPurpose::cases())
            ->mapWithKeys(fn (QrPurpose $purpose) => [$purpose->value => str_replace('_', ' ', ucfirst($purpose->value))])
            ->all();
    }
}
