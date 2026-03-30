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
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
            ->columns(1)
            ->components([
                Tabs::make('Banner')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-rectangle-group')
                            ->columns(2)
                            ->schema([
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
                                    ->label('Ante titulo')
                                    ->maxLength(255)
                                    ->columnSpan(fn (): int|string => static::hasPageIdColumn() ? 1 : 'full'),
                                Textarea::make('description')
                                    ->label('Descripcion')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                FileUpload::make('image_path')
                                    ->label('Imagen')
                                    ->helperText('Fondo recomendado: 1920 x 800 px (proporcion 12:5). Minimo: 1440 x 600 px. Ubica el motivo principal en zona centro-derecha para evitar que el texto y overlay lo tapen.')
                                    ->image()
                                    ->directory('banners')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Enlace y Programacion')
                            ->icon('heroicon-o-link')
                            ->columns(2)
                            ->schema([
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
                                Toggle::make('is_permanent')
                                    ->label('Permanente')
                                    ->dehydrated(false)
                                    ->default(true)
                                    ->live()
                                    ->afterStateHydrated(fn (Get $get, Set $set) => $set('is_permanent', blank($get('ends_at'))))
                                    ->afterStateUpdated(function (Set $set, ?bool $state): void {
                                        if ($state === true) {
                                            $set('ends_at', null);
                                        }
                                    }),
                                DateTimePicker::make('ends_at')
                                    ->label('Mostrar hasta')
                                    ->seconds(false)
                                    ->hidden(fn (Get $get): bool => (bool) $get('is_permanent'))
                                    ->dehydrated(fn (Get $get): bool => ! (bool) $get('is_permanent'))
                                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                                        if (blank($state)) {
                                            $set('is_permanent', true);
                                        }
                                    }),
                            ]),
                    ]),
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
