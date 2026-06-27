<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                TextInput::make('threshold_hours')
                    ->label('Royal point hours')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Used by Total Hours rules. The award becomes pending when the member reaches this number of hours.'),
                TextInput::make('threshold_visits')
                    ->label('Royal point visits')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->helperText('Used by Visit Count rules.'),
                TextInput::make('threshold_subscription_months')
                    ->label('Royal point subscription months')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->helperText('Used by Subscription Months rules.'),
                TextInput::make('period_months')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->helperText('Optional look-back period for attendance-based rules.'),
                Select::make('reward_type')
                    ->required()
                    ->options(self::rewardTypeOptions()),
                TextInput::make('reward_value')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    private static function triggerTypeOptions(): array
    {
        return collect(LoyaltyTriggerType::cases())
            ->mapWithKeys(fn (LoyaltyTriggerType $type) => [$type->value => str($type->value)->replace('_', ' ')->headline()->toString()])
            ->all();
    }

    private static function rewardTypeOptions(): array
    {
        return collect(LoyaltyRewardType::cases())
            ->mapWithKeys(fn (LoyaltyRewardType $type) => [$type->value => str($type->value)->replace('_', ' ')->headline()->toString()])
            ->all();
    }
}
