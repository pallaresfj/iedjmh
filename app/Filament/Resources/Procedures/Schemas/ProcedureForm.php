<?php

namespace App\Filament\Resources\Procedures\Schemas;

use App\Models\Procedure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProcedureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Tramite')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $old, ?string $state) => static::syncSlug($get, $set, $old, $state))
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Procedure::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),
                                Select::make('category_id')
                                    ->label('Categoria')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),
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
                                Textarea::make('summary')
                                    ->label('Resumen')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('requirements')
                                    ->label('Requisitos')
                                    ->rows(6)
                                    ->columnSpanFull(),
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
                        Tab::make('Atencion y Contacto')
                            ->icon('heroicon-o-phone')
                            ->columns(2)
                            ->schema([
                                TextInput::make('response_time')
                                    ->label('Tiempo de respuesta')
                                    ->maxLength(150),
                                TextInput::make('cost')
                                    ->label('Costo')
                                    ->maxLength(150),
                                TextInput::make('channel')
                                    ->label('Canal de atencion')
                                    ->maxLength(150),
                                Toggle::make('is_online')
                                    ->label('Disponible en linea')
                                    ->default(false),
                                TextInput::make('application_url')
                                    ->label('URL de tramite')
                                    ->url()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('contact_email')
                                    ->label('Correo de contacto')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->label('Telefono de contacto')
                                    ->maxLength(50),
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
