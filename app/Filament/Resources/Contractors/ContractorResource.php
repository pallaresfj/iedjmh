<?php

namespace App\Filament\Resources\Contractors;

use App\Filament\Resources\Contractors\Pages\CreateContractor;
use App\Filament\Resources\Contractors\Pages\EditContractor;
use App\Filament\Resources\Contractors\Pages\ListContractors;
use App\Filament\Resources\Contractors\Schemas\ContractorForm;
use App\Filament\Resources\Contractors\Tables\ContractorsTable;
use App\Models\Contractor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ContractorResource extends Resource
{
    protected static ?string $model = Contractor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|UnitEnum|null $navigationGroup = 'Contratación';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationLabel = 'Contratistas';

    protected static ?string $modelLabel = 'Contratista';

    protected static ?string $pluralModelLabel = 'Contratistas';

    public static function form(Schema $schema): Schema
    {
        return ContractorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractors::route('/'),
            'create' => CreateContractor::route('/create'),
            'edit' => EditContractor::route('/{record}/edit'),
        ];
    }
}
