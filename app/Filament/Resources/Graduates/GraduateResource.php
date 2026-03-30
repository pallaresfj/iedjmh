<?php

namespace App\Filament\Resources\Graduates;

use App\Filament\Resources\Graduates\Pages\CreateGraduate;
use App\Filament\Resources\Graduates\Pages\EditGraduate;
use App\Filament\Resources\Graduates\Pages\ListGraduates;
use App\Filament\Resources\Graduates\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Graduates\Schemas\GraduateForm;
use App\Filament\Resources\Graduates\Tables\GraduatesTable;
use App\Models\Graduate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class GraduateResource extends Resource
{
    protected static ?string $model = Graduate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 45;

    protected static ?string $navigationLabel = 'Egresados';

    protected static ?string $modelLabel = 'Egresado';

    protected static ?string $pluralModelLabel = 'Egresados';

    public static function form(Schema $schema): Schema
    {
        return GraduateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GraduatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGraduates::route('/'),
            'create' => CreateGraduate::route('/create'),
            'edit' => EditGraduate::route('/{record}/edit'),
        ];
    }
}
