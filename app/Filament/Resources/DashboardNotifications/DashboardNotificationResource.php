<?php

namespace App\Filament\Resources\DashboardNotifications;

use App\Filament\Resources\DashboardNotifications\Pages\ListDashboardNotifications;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Notifications\DatabaseNotification;

class DashboardNotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Dashboard Notifications';

    protected static ?string $pluralModelLabel = 'Dashboard Notifications';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('data.title')
                    ->label('Title')
                    ->searchable(),
                TextColumn::make('data.body')
                    ->label('Message')
                    ->wrap(),
                TextColumn::make('notifiable.name')
                    ->label('Admin')
                    ->toggleable(),
                TextColumn::make('read_at')
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDashboardNotifications::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
