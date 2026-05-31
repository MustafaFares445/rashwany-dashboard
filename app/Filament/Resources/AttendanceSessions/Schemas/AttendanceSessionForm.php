<?php

namespace App\Filament\Resources\AttendanceSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttendanceSessionForm
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
                        TextInput::make('status')
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('check_in_at')
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('check_out_at')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('raw_duration_minutes')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('billable_duration_minutes')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
