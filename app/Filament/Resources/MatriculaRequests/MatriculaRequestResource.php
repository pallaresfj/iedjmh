<?php

namespace App\Filament\Resources\MatriculaRequests;

use App\Filament\Resources\MatriculaRequests\Pages\EditMatriculaRequest;
use App\Filament\Resources\MatriculaRequests\Pages\ListMatriculaRequests;
use App\Filament\Resources\MatriculaRequests\Pages\ViewMatriculaRequest;
use App\Filament\Resources\MatriculaRequests\Schemas\MatriculaRequestForm;
use App\Filament\Resources\MatriculaRequests\Tables\MatriculaRequestsTable;
use App\Models\MatriculaRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MatriculaRequestResource extends Resource
{
    protected static ?string $model = MatriculaRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 91;

    protected static ?string $navigationLabel = 'Solicitudes de Matricula';

    protected static ?string $modelLabel = 'Solicitud de matricula';

    protected static ?string $pluralModelLabel = 'Solicitudes de matricula';

    public static function form(Schema $schema): Schema
    {
        return MatriculaRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MatriculaRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMatriculaRequests::route('/'),
            'view' => ViewMatriculaRequest::route('/{record}'),
            'edit' => EditMatriculaRequest::route('/{record}/edit'),
        ];
    }
}
