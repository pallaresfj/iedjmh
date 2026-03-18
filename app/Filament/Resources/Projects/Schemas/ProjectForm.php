<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(Project::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('categories')
                    ->label('Categorias')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Textarea::make('summary')
                    ->label('Resumen')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(8)
                    ->columnSpanFull(),
                DatePicker::make('starts_on')
                    ->label('Fecha de inicio'),
                DatePicker::make('ends_on')
                    ->label('Fecha de finalizacion'),
                Toggle::make('is_featured')
                    ->label('Destacar en portada')
                    ->default(false),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ])
                    ->required()
                    ->default('draft')
                    ->native(false),
                DateTimePicker::make('published_at')
                    ->label('Publicado en')
                    ->seconds(false),
                FileUpload::make('cover_image_path')
                    ->label('Imagen principal')
                    ->image()
                    ->directory('projects')
                    ->columnSpanFull(),
            ]);
    }
}
