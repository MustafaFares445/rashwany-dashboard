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
                TextColumn::make('min_hours')
                    ->label('Min hours')
                    ->toggleable(),
                TextColumn::make('period_months')
                    ->label('Period months')
                    ->toggleable(),
                TextColumn::make('min_subscription_months')
                    ->label('Min subscription months')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_visits')
                    ->label('Min visits')
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
