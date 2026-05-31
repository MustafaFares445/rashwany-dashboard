<?php

namespace App\Filament\Resources\Members\Tables;

use App\Enums\MemberStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state instanceof MemberStatus ? $state->value : (string) $state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('activeSubscription.due_amount')
                    ->label('Due Amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('qr_identifier')
                    ->label('QR ID')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
