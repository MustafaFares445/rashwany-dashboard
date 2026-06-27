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
                TextInput::make('min_total_hours')
                    ->label('Minimum Total Hours')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.25')
                    ->helperText('Used by total-hours rules. The member qualifies when their tracked hours reach this value.'),
                TextInput::make('period_months')
                    ->label('Period In Months')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Optional. Limit total-hours or visit-count checks to the last N months.'),
                TextInput::make('min_subscription_months')
                    ->label('Minimum Subscription Months')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Used by subscription-month rules.'),
                TextInput::make('min_visit_count')
                    ->label('Minimum Visit Count')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->helperText('Used by visit-count rules.'),
                Select::make('reward_type')
                    ->required()
                    ->options(self::rewardTypeOptions()),
                TextInput::make('reward_value')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
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
