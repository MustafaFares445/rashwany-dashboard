<?php

namespace App\Filament\Resources\CorrectionRequests\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CorrectionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session_id')
                    ->label('Session'),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('requested_check_in_at')
                    ->dateTime(),
                TextColumn::make('requested_check_out_at')
                    ->dateTime(),
                TextColumn::make('reviewed_at')
                    ->dateTime(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}

