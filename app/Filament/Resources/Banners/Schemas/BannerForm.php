<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Models\Banner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BannerForm
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
                    ->unique(Banner::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('subtitle')
                    ->label('Subtitulo')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(4)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->label('Imagen')
                    ->image()
                    ->directory('banners')
                    ->columnSpanFull(),
                TextInput::make('cta_label')
                    ->label('Texto del boton')
                    ->maxLength(100),
                TextInput::make('cta_url')
                    ->label('URL del boton')
                    ->url()
                    ->maxLength(255),
                Select::make('target')
                    ->label('Destino del enlace')
                    ->options([
                        '_self' => 'Misma ventana',
                        '_blank' => 'Nueva ventana',
                    ])
                    ->default('_self')
                    ->required()
                    ->native(false),
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
                DateTimePicker::make('starts_at')
                    ->label('Mostrar desde')
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label('Mostrar hasta')
                    ->seconds(false),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }
}
