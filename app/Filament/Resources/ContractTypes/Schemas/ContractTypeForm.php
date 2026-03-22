<?php

namespace App\Filament\Resources\ContractTypes\Schemas;

use App\Models\ContractType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContractTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ContractType::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ])
                    ->default('published')
                    ->required()
                    ->native(false),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->required()
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
