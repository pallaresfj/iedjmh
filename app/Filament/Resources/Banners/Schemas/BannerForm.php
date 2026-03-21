<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Models\Banner;
use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\Str;
use Throwable;

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
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                    ->maxLength(255),
                TextInput::make('slug')
                    ->unique(Banner::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('page_id')
                    ->label('Pagina vinculada')
                    ->relationship(
                        name: 'page',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn ($query) => $query->orderBy('title'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Page $record): string => "{$record->title} ({$record->slug})")
                    ->searchable(['title', 'slug'])
                    ->preload()
                    ->native(false)
                    ->visible(fn (): bool => static::hasPageIdColumn())
                    ->dehydrated(fn (): bool => static::hasPageIdColumn())
                    ->helperText('Opcional. Si se define, el banner se mostrara en la parte superior de esa pagina institucional.'),
                Placeholder::make('page_binding_notice')
                    ->label('Pagina vinculada')
                    ->content('La columna banners.page_id no existe en la base de datos. Ejecuta: php artisan migrate')
                    ->visible(fn (): bool => ! static::hasPageIdColumn())
                    ->columnSpanFull(),
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
                    ->helperText('Recomendado: 1600 x 900 px (minimo 1200 x 675). Mantener el contenido importante centrado para evitar recortes.')
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

    private static function syncSlug(Get $get, Set $set, ?string $old, ?string $state): void
    {
        $currentSlug = (string) ($get('slug') ?? '');

        if ($currentSlug !== Str::slug((string) $old)) {
            return;
        }

        $set('slug', Str::slug((string) $state));
    }

    private static function hasPageIdColumn(): bool
    {
        try {
            return DbSchema::hasColumn('banners', 'page_id');
        } catch (Throwable) {
            return false;
        }
    }
}
