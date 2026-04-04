<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use App\Support\Categories\CategoryScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostForm
{
    private const PUBLISH_ROLES = ['super_admin', 'soporte', 'administrador', 'editor'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Noticia')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-document-text')
                            ->columns(12)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->placeholder('Ej. Feria agropecuaria 2026 en la IED Jose Maria Herrera')
                                    ->helperText('Titulo claro y especifico de la noticia. Recomendado: 50 a 60 caracteres.')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 'full', 'lg' => 6]),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Post::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 'full', 'lg' => 6]),
                                Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship(
                                        'categories',
                                        'name',
                                        function (Builder $query): void {
                                            CategoryScope::applySubcategoryScope($query, CategoryScope::POSTS);
                                        },
                                    )
                                    ->helperText(fn (): string => CategoryScope::helperText(CategoryScope::POSTS, 'Noticias'))
                                    ->disabled(fn (): bool => ! CategoryScope::hasParentCategory(CategoryScope::POSTS))
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(['default' => 'full', 'lg' => 6]),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(function (): array {
                                        $options = [
                                            'draft' => 'Borrador',
                                            'archived' => 'Archivado',
                                        ];

                                        if (static::canPublish()) {
                                            $options['published'] = 'Publicado';
                                        }

                                        return $options;
                                    })
                                    ->required()
                                    ->default('draft')
                                    ->native(false)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                Toggle::make('is_featured')
                                    ->label('Destacar')
                                    ->default(false)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                Textarea::make('excerpt')
                                    ->label('Resumen corto')
                                    ->placeholder('Resume en 1 o 2 frases que paso, a quien impacta y por que es relevante.')
                                    ->helperText('Se usa como descripcion publica de apoyo. Recomendado: 140 a 160 caracteres.')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Contenido')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                RichEditor::make('content')
                                    ->label('Contenido')
                                    ->helperText('Usa formato enriquecido. El contenido se guarda como HTML.')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('posts/editor'),
                                FileUpload::make('cover_image_path')
                                    ->label('Imagen principal')
                                    ->helperText('Imagen destacada de la noticia. Recomendado: 1200x630 px (JPG o PNG).')
                                    ->image()
                                    ->disk('public')
                                    ->directory('posts'),
                            ]),
                    ]),
            ]);
    }

    private static function canPublish(): bool
    {
        $user = auth()->user();

        return $user !== null
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(self::PUBLISH_ROLES);
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
