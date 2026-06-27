<?php

namespace App\Filament\Resources\LoyaltyRules\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_type')
                    ->badge(),
                TextColumn::make('threshold_hours')
                    ->label('Hours point')
                    ->toggleable(),
                TextColumn::make('threshold_visits')
                    ->label('Visits point')
                    ->toggleable(),
                TextColumn::make('threshold_subscription_months')
                    ->label('Months point')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_months')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reward_type')
                    ->badge(),
                TextColumn::make('reward_value'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
