<?php

namespace App\Filament\Resources\Rewards\Tables;

use App\Enums\RewardStatus;
use App\Services\LoyaltyService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
                TextColumn::make('subscription_id')
                    ->label('Subscription')
                    ->toggleable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('value'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('granted_at')
                    ->dateTime(),
                TextColumn::make('activated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('activate_award')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === RewardStatus::Pending)
                    ->action(fn ($record) => app(LoyaltyService::class)->updateReward(
                        reward: $record,
                        data: ['status' => RewardStatus::Granted->value],
                        actorId: auth()->id(),
                        ipAddress: request()->ip(),
                    )),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
