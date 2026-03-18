<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostForm
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
                    ->unique(Post::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('categories')
                    ->label('Categorias')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Textarea::make('excerpt')
                    ->label('Resumen corto')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label('Contenido')
                    ->rows(10)
                    ->columnSpanFull(),
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
                Toggle::make('is_featured')
                    ->label('Destacar en portada')
                    ->default(false),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                FileUpload::make('cover_image_path')
                    ->label('Imagen principal')
                    ->image()
                    ->directory('posts')
                    ->columnSpanFull(),
                TextInput::make('seo_title')
                    ->label('SEO titulo')
                    ->maxLength(255),
                Textarea::make('seo_description')
                    ->label('SEO descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
                FileUpload::make('seo_image_path')
                    ->label('SEO imagen')
                    ->image()
                    ->directory('seo')
                    ->columnSpanFull(),
            ]);
    }
}
