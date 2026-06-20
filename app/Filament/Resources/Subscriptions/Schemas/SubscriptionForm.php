<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
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
                Select::make('package_id')
                    ->relationship('package', 'name')
                    ->required()
                    ->searchable(),
                Select::make('status')
                    ->required()
                    ->options(self::statusOptions()),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                Toggle::make('auto_renew'),
                TextInput::make('price')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('paid_amount')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('total_hours')
                    ->numeric()
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('remaining_hours')
                    ->numeric()
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('used_hours')
                    ->numeric()
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('due_amount')
                    ->numeric()
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    private static function statusOptions(): array
    {
        return collect(SubscriptionStatus::cases())
            ->mapWithKeys(fn (SubscriptionStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}
