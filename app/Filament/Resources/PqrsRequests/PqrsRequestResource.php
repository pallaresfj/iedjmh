<?php

namespace App\Filament\Resources\PqrsRequests;

use App\Filament\Resources\PqrsRequests\Pages\CreatePqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\EditPqrsRequest;
use App\Filament\Resources\PqrsRequests\Pages\ListPqrsRequests;
use App\Filament\Resources\PqrsRequests\Pages\ViewPqrsRequest;
use App\Filament\Resources\PqrsRequests\Schemas\PqrsRequestForm;
use App\Filament\Resources\PqrsRequests\Tables\PqrsRequestsTable;
use App\Models\PqrsRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PqrsRequestResource extends Resource
{
    protected static ?string $model = PqrsRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'PQRSF';

    protected static ?string $modelLabel = 'PQRSF';

    protected static ?string $pluralModelLabel = 'PQRSF';

    public static function form(Schema $schema): Schema
    {
        return PqrsRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PqrsRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPqrsRequests::route('/'),
            'create' => CreatePqrsRequest::route('/create'),
            'view' => ViewPqrsRequest::route('/{record}'),
            'edit' => EditPqrsRequest::route('/{record}/edit'),
        ];
    }
}
