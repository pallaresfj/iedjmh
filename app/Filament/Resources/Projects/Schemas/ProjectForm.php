<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use App\Support\Categories\CategoryScope;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Proyecto')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Project::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),
                                Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship(
                                        'categories',
                                        'name',
                                        function (Builder $query): void {
                                            CategoryScope::applySubcategoryScope($query, CategoryScope::PROJECTS);
                                        },
                                    )
                                    ->helperText(fn (): string => CategoryScope::helperText(CategoryScope::PROJECTS, 'Proyectos'))
                                    ->disabled(fn (): bool => ! CategoryScope::hasParentCategory(CategoryScope::PROJECTS))
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
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
                            ]),
                        Tab::make('Contenido')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Textarea::make('summary')
                                    ->label('Resumen')
                                    ->placeholder('Una o dos frases cortas')
                                    ->rows(3),
                                RichEditor::make('description')
                                    ->label('Descripcion')
                                    ->placeholder('Describe el proyecto, sus objetivos, acciones y resultados esperados.')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'h2',
                                        'h3',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'link',
                                        'undo',
                                        'redo',
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('projects/editor'),
                                TextInput::make('external_url')
                                    ->label('URL de referencia')
                                    ->placeholder('https://dominio.com/recurso o /storage/documentos/archivo.pdf')
                                    ->helperText('Acepta URL externa (http/https) o ruta interna que inicie con "/".')
                                    ->maxLength(2048)
                                    ->rule(static function (): Closure {
                                        return function (string $attribute, mixed $value, Closure $fail): void {
                                            if ($value === null || $value === '') {
                                                return;
                                            }

                                            if (! is_string($value) || ! static::isValidReferenceUrl(trim($value))) {
                                                $fail('La URL debe ser absoluta (http/https) o una ruta interna iniciando con "/".');
                                            }
                                        };
                                    }),
                            ]),
                        Tab::make('Multimedia')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('cover_image_path')
                                    ->label('Imagen principal')
                                    ->image()
                                    ->disk('public')
                                    ->directory('projects'),
                                FileUpload::make('gallery_image_paths')
                                    ->label('Galeria de imagenes')
                                    ->helperText('Maximo 5 imagenes en total, incluyendo la portada.')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->disk('public')
                                    ->directory('projects/gallery')
                                    ->maxFiles(fn (Get $get): int => filled($get('cover_image_path')) ? 4 : 5)
                                    ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                        if (! is_array($value)) {
                                            return;
                                        }

                                        $filesCount = count(array_filter($value, fn (mixed $path): bool => is_string($path) && trim($path) !== ''));
                                        $maxFiles = filled($get('cover_image_path')) ? 4 : 5;

                                        if ($filesCount > $maxFiles) {
                                            $fail('La galeria supera el limite permitido para el proyecto.');
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

    private static function isValidReferenceUrl(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        if (Str::startsWith($value, '/')) {
            return true;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
