<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->placeholder('Ej. Feria agropecuaria 2026 en la IED Jose Maria Herrera')
                    ->helperText('Titulo claro y especifico de la noticia. Recomendado: 50 a 60 caracteres.')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(Post::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('categories')
                    ->label('Categorias')
                    ->relationship(
                        name: 'categories',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): void {
                            $newsParentCategoryId = static::resolveNewsParentCategoryId();

                            if ($newsParentCategoryId === null) {
                                $query->whereRaw('1 = 0');

                                return;
                            }

                            $query
                                ->where('parent_id', $newsParentCategoryId)
                                ->orderBy('sort_order')
                                ->orderBy('name');
                        },
                    )
                    ->helperText(fn (): string => static::resolveNewsParentCategoryId() === null
                        ? 'Crea primero la categoria padre "Noticia" (o "Noticias") y sus categorias hijas.'
                        : 'Solo se muestran categorias hijas de la categoria padre "Noticia".')
                    ->disabled(fn (): bool => static::resolveNewsParentCategoryId() === null)
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Textarea::make('excerpt')
                    ->label('Resumen corto')
                    ->placeholder('Resume en 1 o 2 frases que paso, a quien impacta y por que es relevante.')
                    ->helperText('Se usa como descripcion publica de apoyo. Recomendado: 140 a 160 caracteres.')
                    ->rows(3)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Contenido')
                    ->helperText('Usa formato enriquecido. El contenido se guarda como HTML.')
                    ->columnSpanFull(),
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
                    ->native(false),
                DateTimePicker::make('published_at')
                    ->label('Publicado en')
                    ->visible(fn (): bool => static::canPublish())
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
                    ->helperText('Imagen destacada de la noticia. Recomendado: 1200x630 px (JPG o PNG).')
                    ->image()
                    ->directory('posts')
                    ->columnSpanFull(),
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

    private static function resolveNewsParentCategoryId(): ?int
    {
        static $resolved = false;
        static $categoryId = null;

        if ($resolved) {
            return $categoryId;
        }

        $resolved = true;

        $categoryId = Category::query()
            ->whereNull('parent_id')
            ->whereIn('slug', ['noticia', 'noticias'])
            ->value('id');

        if ($categoryId !== null) {
            return $categoryId;
        }

        $categoryId = Category::query()
            ->whereNull('parent_id')
            ->whereIn('name', ['Noticia', 'Noticias'])
            ->value('id');

        return $categoryId;
    }
}
