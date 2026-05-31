<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->sortable(),
                TextColumn::make('actor_type')
                    ->badge(),
                TextColumn::make('actor_id'),
                TextColumn::make('reason')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('entity_type')
                    ->options([
                        'member' => 'Member',
                        'package' => 'Package',
                        'subscription' => 'Subscription',
                        'payment' => 'Payment',
                        'correction_request' => 'Correction Request',
                        'setting' => 'Setting',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

