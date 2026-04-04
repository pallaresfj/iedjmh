<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Document;
use App\Support\Categories\CategoryScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Documento')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-document-text')
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
                                    ->unique(Document::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),
                                Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship(
                                        'categories',
                                        'name',
                                        function (Builder $query): void {
                                            CategoryScope::applySubcategoryScope($query, CategoryScope::DOCUMENTS);
                                        },
                                    )
                                    ->helperText(fn (): string => CategoryScope::helperText(CategoryScope::DOCUMENTS, 'Documentos'))
                                    ->disabled(fn (): bool => ! CategoryScope::hasParentCategory(CategoryScope::DOCUMENTS))
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
                            ]),
                        Tab::make('Enlace y Publicacion')
                            ->icon('heroicon-o-paper-clip')
                            ->columns(2)
                            ->schema([
                                TextInput::make('external_url')
                                    ->label('Enlace Google Drive')
                                    ->helperText('Solo se permiten enlaces https://drive.google.com o https://docs.google.com')
                                    ->required()
                                    ->url()
                                    ->rule(function (): \Closure {
                                        return function (string $attribute, mixed $value, \Closure $fail): void {
                                            if (! is_string($value) || trim($value) === '') {
                                                $fail('El enlace de Google Drive es obligatorio.');

                                                return;
                                            }

                                            $normalized = trim($value);
                                            $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));
                                            $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
                                            $host = preg_replace('/^www\./', '', $host) ?? $host;

                                            if ($scheme !== 'https' || ! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
                                                $fail('El enlace debe ser HTTPS y pertenecer a Google Drive o Google Docs.');
                                            }
                                        };
                                    })
                                    ->maxLength(2048)
                                    ->columnSpanFull(),
                                TextInput::make('document_number')
                                    ->label('Numero de documento')
                                    ->maxLength(100),
                                DatePicker::make('document_date')
                                    ->label('Fecha del documento'),
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
}
