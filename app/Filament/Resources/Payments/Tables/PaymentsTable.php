<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subscription.id')
                    ->label('Subscription')
                    ->formatStateUsing(function ($state, $record): string {
                        $subscription = $record->subscription;

                        if (! $subscription) {
                            return '-';
                        }

                        return sprintf(
                            '#%d | %s | Due: %.2f',
                            $subscription->id,
                            $subscription->package?->name ?? 'No package',
                            (float) $subscription->due_amount
                        );
                    }),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
