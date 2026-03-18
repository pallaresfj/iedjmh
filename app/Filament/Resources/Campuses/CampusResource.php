<?php

namespace App\Filament\Resources\Campuses;

use App\Filament\Resources\Campuses\Pages\CreateCampus;
use App\Filament\Resources\Campuses\Pages\EditCampus;
use App\Filament\Resources\Campuses\Pages\ListCampuses;
use App\Filament\Resources\Campuses\Schemas\CampusForm;
use App\Filament\Resources\Campuses\Tables\CampusesTable;
use App\Models\Campus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CampusResource extends Resource
{
    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Institucion';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Sedes';

    protected static ?string $modelLabel = 'Sede';

    protected static ?string $pluralModelLabel = 'Sedes';

    public static function form(Schema $schema): Schema
    {
        return CampusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCampuses::route('/'),
            'create' => CreateCampus::route('/create'),
            'edit' => EditCampus::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
