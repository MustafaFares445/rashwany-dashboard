<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Enums\LoyaltyRewardType;
use App\Enums\RewardStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reward')
                    ->columns(2)
                    ->schema([
                        Select::make('member_id')
                            ->relationship('member', 'name')
                            ->required()
                            ->searchable(),
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
                            ->default(RewardStatus::Pending->value),
                        DateTimePicker::make('granted_at')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function rewardTypeOptions(): array
    {
        return collect(LoyaltyRewardType::cases())
            ->mapWithKeys(fn (LoyaltyRewardType $type) => [$type->value => str_replace('_', ' ', ucfirst($type->value))])
            ->all();
    }

    private static function statusOptions(): array
    {
        return collect(RewardStatus::cases())
            ->mapWithKeys(fn (RewardStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}

