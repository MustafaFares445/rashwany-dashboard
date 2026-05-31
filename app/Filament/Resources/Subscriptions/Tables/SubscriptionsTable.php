<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('package.name')
                    ->label('Package')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime(),
                TextColumn::make('remaining_hours')
                    ->label('Remaining')
                    ->sortable(),
                TextColumn::make('due_amount')
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
