<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Tables\MembersTable;
use App\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['activeSubscription.package'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
