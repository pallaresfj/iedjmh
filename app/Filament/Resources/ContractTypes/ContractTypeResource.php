<?php

namespace App\Filament\Resources\ContractTypes;

use App\Filament\Resources\ContractTypes\Pages\CreateContractType;
use App\Filament\Resources\ContractTypes\Pages\EditContractType;
use App\Filament\Resources\ContractTypes\Pages\ListContractTypes;
use App\Filament\Resources\ContractTypes\Schemas\ContractTypeForm;
use App\Filament\Resources\ContractTypes\Tables\ContractTypesTable;
use App\Models\ContractType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ContractTypeResource extends Resource
{
    protected static ?string $model = ContractType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Contratación';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Tipos de contrato';

    protected static ?string $modelLabel = 'Tipo de contrato';

    protected static ?string $pluralModelLabel = 'Tipos de contrato';

    public static function form(Schema $schema): Schema
    {
        return ContractTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractTypes::route('/'),
            'create' => CreateContractType::route('/create'),
            'edit' => EditContractType::route('/{record}/edit'),
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
