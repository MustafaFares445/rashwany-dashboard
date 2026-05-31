<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('duration_unit')
                    ->label('Duration Unit'),
                TextColumn::make('duration_value')
                    ->label('Duration')
                    ->sortable(),
                TextColumn::make('included_hours')
                    ->label('Included Hours'),
                TextColumn::make('price')
                    ->money('USD'),
                IconColumn::make('is_active')
                    ->boolean(),
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
