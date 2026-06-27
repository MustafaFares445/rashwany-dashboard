<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Enums\LoyaltyRewardType;
use App\Enums\RewardStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('member_id')
                    ->relationship('member', 'name')
                    ->required()
                    ->searchable(),
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->label('Linked subscription')
                    ->nullable()
                    ->searchable()
                    ->helperText('Auto-filled from the member active subscription when empty.'),
                Select::make('loyalty_rule_id')
                    ->relationship('loyaltyRule', 'name')
                    ->nullable()
                    ->searchable(),
                Select::make('type')
                    ->required()
                    ->options(self::rewardTypeOptions()),
                TextInput::make('value')
                    ->maxLength(255),
                Select::make('status')
                    ->required()
                    ->options(self::statusOptions())
                    ->default(RewardStatus::Pending->value)
                    ->helperText('Pending awards are not applied until an admin changes the status to Granted.'),
                DateTimePicker::make('granted_at')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('activated_at')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    private static function rewardTypeOptions(): array
    {
        return collect(LoyaltyRewardType::cases())
            ->mapWithKeys(fn (LoyaltyRewardType $type) => [$type->value => str($type->value)->replace('_', ' ')->headline()->toString()])
            ->all();
    }

    private static function statusOptions(): array
    {
        return collect(RewardStatus::cases())
            ->mapWithKeys(fn (RewardStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}
