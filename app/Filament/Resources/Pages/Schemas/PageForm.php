<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use App\Support\PageMenuCatalog;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Pagina')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-document')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                                    ->maxLength(255),
                                Select::make('menu_binding')
                                    ->label('Vinculo de menu')
                                    ->options(PageMenuCatalog::formOptions())
                                    ->placeholder('Sin vinculo de menu')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->visible(fn (): bool => static::hasMenuBindingColumn())
                                    ->dehydrated(fn (): bool => static::hasMenuBindingColumn())
                                    ->rule(Rule::in(array_keys(PageMenuCatalog::formOptions())))
                                    ->live()
                                    ->afterStateHydrated(fn (Set $set, ?string $state) => static::syncSlugFromMenuBinding($set, $state))
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => static::syncSlugFromMenuBinding($set, $state))
                                    ->helperText('Asocia esta pagina con una ruta institucional predefinida del menu.'),
                                Placeholder::make('menu_binding_notice')
                                    ->label('Vinculo de menu')
                                    ->content('La columna menu_binding no existe en la base de datos. Ejecuta: php artisan migrate')
                                    ->visible(fn (): bool => ! static::hasMenuBindingColumn())
                                    ->columnSpanFull(),
                                TextInput::make('slug')
                                    ->required()
                                    ->dehydrated()
                                    ->disabled(fn (Get $get): bool => filled($get('menu_binding')))
                                    ->unique(Page::class, 'slug', ignoreRecord: true)
                                    ->helperText(fn (Get $get): ?string => filled($get('menu_binding'))
                                        ? 'Este slug es canonico del vinculo seleccionado y no puede editarse manualmente.'
                                        : null)
                                    ->maxLength(255),
                                Placeholder::make('menu_binding_public_path')
                                    ->label('URL publica')
                                    ->content(fn (Get $get): string => static::resolveMenuBindingPath($get('menu_binding')))
                                    ->visible(fn (Get $get): bool => filled($get('menu_binding')))
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
                            ]),
                        Tab::make('Contenido')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Textarea::make('summary')
                                    ->label('Resumen')
                                    ->rows(3),
                                RichEditor::make('content')
                                    ->label('Contenido')
                                    ->toolbarButtons([
                                        'h2',
                                        'h3',
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'link',
                                        'undo',
                                        'redo',
                                        'table',
                                        'attachFiles',
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('pages/editor'),
                            ]),
                    ]),
            ]);
    }

    private static function syncSlug(Get $get, Set $set, ?string $old, ?string $state): void
    {
        if (filled($get('menu_binding'))) {
            return;
        }

        $currentSlug = (string) ($get('slug') ?? '');

        if ($currentSlug !== Str::slug((string) $old)) {
            return;
        }

        $set('slug', Str::slug((string) $state));
    }

    private static function syncSlugFromMenuBinding(Set $set, ?string $menuBinding): void
    {
        if (! filled($menuBinding)) {
            return;
        }

        $slug = PageMenuCatalog::slugFor($menuBinding);

        if (! $slug) {
            return;
        }

        $set('slug', $slug);
    }

    private static function resolveMenuBindingPath(?string $menuBinding): string
    {
        if (! filled($menuBinding)) {
            return '-';
        }

        return PageMenuCatalog::pathFor($menuBinding) ?: '-';
    }

    private static function hasMenuBindingColumn(): bool
    {
        try {
            return DbSchema::hasColumn('pages', 'menu_binding');
        } catch (Throwable) {
            return false;
        }
    }
}
