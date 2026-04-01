<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use App\Support\Categories\CategoryScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('question')
                    ->label('Pregunta')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->required()
                    ->unique(Faq::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('Categoria')
                    ->relationship(
                        'category',
                        'name',
                        function (Builder $query): void {
                            CategoryScope::applySubcategoryScope($query, CategoryScope::FAQS);
                        },
                    )
                    ->helperText(fn (): string => CategoryScope::helperText(CategoryScope::FAQS, 'Preguntas frecuentes'))
                    ->disabled(fn (): bool => ! CategoryScope::hasParentCategory(CategoryScope::FAQS))
                    ->searchable()
                    ->preload(),
                Textarea::make('answer')
                    ->label('Respuesta')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ])
                    ->required()
                    ->default('published')
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
