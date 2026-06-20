<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Enums\SettingType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->required()
                    ->options(self::typeOptions()),
                TextInput::make('value')
                    ->columnSpanFull(),
                TextInput::make('group'),
                Toggle::make('is_public'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    private static function typeOptions(): array
    {
        return collect(SettingType::cases())
            ->mapWithKeys(fn (SettingType $type) => [$type->value => ucfirst($type->value)])
            ->all();
    }
}
