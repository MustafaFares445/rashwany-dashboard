<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Subscription;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment')
                    ->columns(2)
                    ->schema([
                        Select::make('member_id')
                            ->relationship('member', 'name')
                            ->required()
                            ->searchable()
                            ->live(),
                        Select::make('subscription_id')
                            ->label('Subscription')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get): array {
                                return Subscription::query()
                                    ->with(['member:id,name', 'package:id,name'])
                                    ->when($get('member_id'), fn ($query, $memberId) => $query->where('member_id', $memberId))
                                    ->latest('id')
                                    ->get()
                                    ->mapWithKeys(fn (Subscription $subscription) => [
                                        $subscription->id => sprintf(
                                            '#%d | %s | %s | %s | Due: %.2f',
                                            $subscription->id,
                                            $subscription->member?->name ?? 'Unknown member',
                                            $subscription->package?->name ?? 'No package',
                                            ucfirst($subscription->status->value),
                                            (float) $subscription->due_amount
                                        ),
                                    ])
                                    ->all();
                            }),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0.01),
                        TextInput::make('currency')
                            ->required()
                            ->default('USD')
                            ->maxLength(10),
                        Select::make('payment_method')
                            ->required()
                            ->options(self::methodOptions()),
                        Select::make('status')
                            ->required()
                            ->options(self::statusOptions())
                            ->default(PaymentStatus::Paid->value),
                        DateTimePicker::make('paid_at'),
                        DateTimePicker::make('due_at'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function methodOptions(): array
    {
        return collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method) => [$method->value => str_replace('_', ' ', ucfirst($method->value))])
            ->all();
    }

    private static function statusOptions(): array
    {
        return collect(PaymentStatus::cases())
            ->mapWithKeys(fn (PaymentStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}

