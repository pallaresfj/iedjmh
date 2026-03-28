<?php

namespace App\Filament\Resources\AreaPlans;

use App\Filament\Resources\AreaPlans\Pages\CreateAreaPlan;
use App\Filament\Resources\AreaPlans\Pages\EditAreaPlan;
use App\Filament\Resources\AreaPlans\Pages\ListAreaPlans;
use App\Filament\Resources\AreaPlans\Schemas\AreaPlanForm;
use App\Filament\Resources\AreaPlans\Tables\AreaPlansTable;
use App\Models\AreaPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AreaPlanResource extends Resource
{
    protected static ?string $model = AreaPlan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 41;

    protected static ?string $navigationLabel = 'Planes de Area';

    protected static ?string $modelLabel = 'Plan de Area';

    protected static ?string $pluralModelLabel = 'Planes de Area';

    public static function form(Schema $schema): Schema
    {
        return AreaPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AreaPlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAreaPlans::route('/'),
            'create' => CreateAreaPlan::route('/create'),
            'edit' => EditAreaPlan::route('/{record}/edit'),
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
