<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Enums\PackageDurationUnit;
use App\Enums\PackageRenewalType;
use App\Enums\PackageType;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->required()
                            ->options(self::typeOptions()),
                        Select::make('duration_unit')
                            ->required()
                            ->options(self::durationOptions()),
                        TextInput::make('duration_value')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('included_hours')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('price')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        Select::make('renewal_type')
                            ->required()
                            ->options(self::renewalOptions()),
                        Toggle::make('is_active')
                            ->default(true),
                        KeyValue::make('settings_json')
                            ->label('Settings'),
                    ]),
            ]);
    }

    private static function typeOptions(): array
    {
        return collect(PackageType::cases())
            ->mapWithKeys(fn (PackageType $type) => [$type->value => str_replace('_', ' ', ucfirst($type->value))])
            ->all();
    }

    private static function durationOptions(): array
    {
        return collect(PackageDurationUnit::cases())
            ->mapWithKeys(fn (PackageDurationUnit $unit) => [$unit->value => ucfirst($unit->value)])
            ->all();
    }

    private static function renewalOptions(): array
    {
        return collect(PackageRenewalType::cases())
            ->mapWithKeys(fn (PackageRenewalType $type) => [$type->value => ucfirst($type->value)])
            ->all();
    }
}
