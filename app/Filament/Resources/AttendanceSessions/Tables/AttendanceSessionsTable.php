<?php

namespace App\Filament\Resources\AttendanceSessions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('check_in_at')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                TextColumn::make('check_out_at')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Still open'),
                TextColumn::make('raw_duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn (?int $state): string => self::formatDuration($state))
                    ->placeholder('Still open'),
                TextColumn::make('admin_updated_at')
                    ->label('Admin update')
                    ->formatStateUsing(fn ($state): string => $state ? 'Updated by admin' : '—')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'warning' : 'gray'),
                TextColumn::make('subscription.package.name')
                    ->label('Package'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Update'),
            ])
            ->toolbarActions([]);
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
