<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use App\Enums\LoyaltyRewardType;
use App\Enums\LoyaltyTriggerType;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoyaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rule')
                    ->columns(2)
                    ->schema([
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
                        KeyValue::make('condition_json')
                            ->label('Conditions')
                            ->columnSpanFull(),
                    ]),
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

