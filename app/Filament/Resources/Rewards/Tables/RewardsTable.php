<?php

namespace App\Filament\Resources\Rewards\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('loyaltyRule.name')
                    ->label('Rule')
                    ->toggleable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('value'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('granted_at')
                    ->dateTime(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'granted' => 'Granted',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}

