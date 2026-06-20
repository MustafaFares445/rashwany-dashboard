<?php

namespace App\Filament\Resources\AttendanceSessions\Schemas;

use App\Enums\SessionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AttendanceSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('member.name')
                    ->label('Member')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->required()
                    ->options(self::statusOptions()),
                DateTimePicker::make('check_in_at')
                    ->required(),
                DateTimePicker::make('check_out_at'),
                TextInput::make('raw_duration_minutes')
                    ->label('Raw duration')
                    ->formatStateUsing(fn (?int $state): string => self::formatDuration($state))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('billable_duration_minutes')
                    ->label('Billable duration')
                    ->formatStateUsing(fn (?int $state): string => self::formatDuration($state))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('adminUpdatedBy.name')
                    ->label('Last updated by admin')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('admin_updated_at')
                    ->label('Admin updated at')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    private static function statusOptions(): array
    {
        return collect(SessionStatus::cases())
            ->mapWithKeys(fn (SessionStatus $status) => [$status->value => str_replace('_', ' ', ucfirst($status->value))])
            ->all();
    }

    private static function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return 'Still open';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return $remainingMinutes.' min';
        }

        if ($remainingMinutes === 0) {
            return $hours.' hr';
        }

        return $hours.' hr '.$remainingMinutes.' min';
    }
}
