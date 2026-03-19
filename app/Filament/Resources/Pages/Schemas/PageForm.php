<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(Page::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Textarea::make('summary')
                    ->label('Resumen')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label('Contenido')
                    ->rows(10)
                    ->columnSpanFull(),
                TextInput::make('template')
                    ->label('Plantilla')
                    ->maxLength(100),
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
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
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

    private static function syncSlug(Get $get, Set $set, ?string $old, ?string $state): void
    {
        $currentSlug = (string) ($get('slug') ?? '');

        if ($currentSlug !== Str::slug((string) $old)) {
            return;
        }

        $set('slug', Str::slug((string) $state));
    }
}
