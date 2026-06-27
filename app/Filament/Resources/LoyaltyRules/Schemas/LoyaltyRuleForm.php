<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoyaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('trigger_type')
                    ->required()
                    ->options(self::triggerTypeOptions()),
                Select::make('reward_type')
                    ->required()
                    ->options(self::rewardTypeOptions()),
                TextInput::make('reward_value')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('min_hours')
                    ->label('Minimum hours')
                    ->numeric()
                    ->step('0.0001')
                    ->minValue(0)
                    ->helperText('Used by total-hours loyalty rules.'),
                TextInput::make('period_months')
                    ->label('Period months')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Optional rolling period for hours or visits. Leave empty for lifetime.'),
                TextInput::make('min_subscription_months')
                    ->label('Minimum subscription months')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Used by subscription-months loyalty rules.'),
                TextInput::make('min_visits')
                    ->label('Minimum visits')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Used by visit-count loyalty rules.'),
            ]);
    }

    private static function triggerTypeOptions(): array
    {
        return collect(LoyaltyTriggerType::cases())
            ->mapWithKeys(fn (LoyaltyTriggerType $type) => [$type->value => str_replace('_', ' ', ucfirst($type->value))])
            ->all();
    }

    private static function rewardTypeOptions(): array
    {
        return collect(LoyaltyRewardType::cases())
            ->mapWithKeys(fn (LoyaltyRewardType $type) => [$type->value => str_replace('_', ' ', ucfirst($type->value))])
            ->all();
    }
}
