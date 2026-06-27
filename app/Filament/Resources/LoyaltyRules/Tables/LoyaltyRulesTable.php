<?php

namespace App\Filament\Resources\LoyaltyRules\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('min_total_hours')
                    ->label('Min Hours')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('period_months')
                    ->label('Period Months')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('min_subscription_months')
                    ->label('Subscription Months')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('min_visit_count')
                    ->label('Visit Count')
                    ->placeholder('-')
                    ->toggleable(),
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
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
