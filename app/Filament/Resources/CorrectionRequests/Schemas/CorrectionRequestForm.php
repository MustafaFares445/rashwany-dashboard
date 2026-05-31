<?php

namespace App\Filament\Resources\CorrectionRequests\Schemas;

use App\Enums\CorrectionRequestStatus;
use App\Enums\CorrectionRequestType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CorrectionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Correction Request')
                    ->columns(2)
                    ->schema([
                        Select::make('member_id')
                            ->relationship('member', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('session_id')
                            ->relationship('session', 'id')
                            ->nullable()
                            ->searchable(),
                        Select::make('type')
                            ->required()
                            ->options(self::typeOptions()),
                        Select::make('status')
                            ->required()
                            ->options(self::statusOptions())
                            ->default(CorrectionRequestStatus::Pending->value),
                        DateTimePicker::make('requested_check_in_at'),
                        DateTimePicker::make('requested_check_out_at'),
                        Textarea::make('message')
                            ->columnSpanFull(),
                        Textarea::make('admin_note')
                            ->columnSpanFull(),
                        DateTimePicker::make('reviewed_at')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    private static function typeOptions(): array
    {
        return collect(CorrectionRequestType::cases())
            ->mapWithKeys(fn (CorrectionRequestType $type) => [$type->value => str_replace('_', ' ', ucfirst($type->value))])
            ->all();
    }

    private static function statusOptions(): array
    {
        return collect(CorrectionRequestStatus::cases())
            ->mapWithKeys(fn (CorrectionRequestStatus $status) => [$status->value => ucfirst($status->value)])
            ->all();
    }
}

