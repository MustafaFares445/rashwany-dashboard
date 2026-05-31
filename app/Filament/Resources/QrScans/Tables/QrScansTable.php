<?php

namespace App\Filament\Resources\QrScans\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QrScansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable(),
                TextColumn::make('qrCode.name')
                    ->label('QR Code'),
                TextColumn::make('purpose')
                    ->badge(),
                TextColumn::make('result')
                    ->badge(),
                TextColumn::make('failure_reason'),
                TextColumn::make('scanned_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
