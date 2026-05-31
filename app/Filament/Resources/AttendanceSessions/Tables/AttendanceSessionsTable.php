<?php

namespace App\Filament\Resources\AttendanceSessions\Tables;

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
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out_at')
                    ->dateTime(),
                TextColumn::make('raw_duration_minutes')
                    ->label('Raw (min)'),
                TextColumn::make('billable_duration_minutes')
                    ->label('Billable (min)'),
                TextColumn::make('subscription.package.name')
                    ->label('Package'),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
