<?php

namespace App\Filament\Resources\Rewards\Tables;

use App\Enums\RewardStatus;
use App\Services\LoyaltyService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
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
                TextColumn::make('qualified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('granted_at')
                    ->dateTime(),
                TextColumn::make('activatedBy.name')
                    ->label('Activated By')
                    ->toggleable(),
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
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === RewardStatus::Pending)
                    ->action(function ($record): void {
                        app(LoyaltyService::class)->updateReward(
                            reward: $record,
                            data: ['status' => RewardStatus::Granted->value],
                            actorId: auth()->id(),
                            ipAddress: request()->ip(),
                        );

                        FilamentNotification::make()
                            ->title('Reward activated')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
