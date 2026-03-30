<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
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

class EventForm
{
    private const PUBLISH_ROLES = ['super_admin', 'soporte', 'administrador', 'editor'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Evento')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-calendar-days')
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
                                    ->unique(Event::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),
                                Select::make('categories')
                                    ->label('Categorias')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
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
                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                        Tab::make('Detalles del Evento')
                            ->icon('heroicon-o-map-pin')
                            ->columns(2)
                            ->schema([
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
                                    ->maxLength(255)
                                    ->columnSpanFull(),
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
