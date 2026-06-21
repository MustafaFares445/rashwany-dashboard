<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\MemberStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('pin')
                    ->label('PIN')
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->helperText('Optional PIN for web-based check-in/check-out'),
                TextInput::make('email')
                    ->email()
                    ->nullable()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('qr_identifier')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('status')
                    ->required()
                    ->options(self::statusOptions()),
                DatePicker::make('birth_date'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    private static function statusOptions(): array
    {
        return collect(MemberStatus::cases())
            ->mapWithKeys(fn(MemberStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}
