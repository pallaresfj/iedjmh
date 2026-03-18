<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(Event::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('categories')
                    ->label('Categorias')
                    ->relationship('categories', 'name')
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
                TextInput::make('location')
                    ->label('Lugar')
                    ->maxLength(255),
                DateTimePicker::make('starts_at')
                    ->label('Inicia')
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label('Finaliza')
                    ->seconds(false),
                Toggle::make('is_all_day')
                    ->label('Todo el dia')
                    ->default(false),
                TextInput::make('registration_url')
                    ->label('URL de registro')
                    ->url()
                    ->maxLength(255),
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
            ]);
    }
}
