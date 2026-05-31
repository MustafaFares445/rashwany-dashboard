<?php

namespace App\Filament\Resources\QrScans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QrScanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('member.name')
                            ->label('Member')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('purpose')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('result')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('failure_reason')
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('scanned_at')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('ip_address')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
