<?php

namespace App\Filament\Resources\Graduates\RelationManagers;

use App\Rules\GoogleDriveUrl;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos del egresado';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('type_label')
                    ->label('Tipo')
                    ->maxLength(255),
                TextInput::make('drive_url')
                    ->label('URL Google Drive')
                    ->required()
                    ->maxLength(2048)
                    ->rule(new GoogleDriveUrl)
                    ->url()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_official')
                    ->label('Documento oficial')
                    ->default(false),
                Toggle::make('is_visible')
                    ->label('Visible para el egresado')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                TextColumn::make('type_label')
                    ->label('Tipo')
                    ->placeholder('General'),
                IconColumn::make('is_official')
                    ->label('Oficial')
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

