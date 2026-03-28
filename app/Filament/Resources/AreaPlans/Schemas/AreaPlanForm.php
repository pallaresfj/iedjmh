<?php

namespace App\Filament\Resources\AreaPlans\Schemas;

use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AreaPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('area_name')
                    ->label('Area academica')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('Icono (Material Symbols)')
                    ->required()
                    ->default('menu_book')
                    ->maxLength(80)
                    ->rule('regex:/^[a-z0-9_]+$/')
                    ->helperText('Usa nombres de Material Symbols en minuscula. Ejemplo: calculate, science, agriculture.'),
                Textarea::make('responsible_teachers')
                    ->label('Docentes responsables')
                    ->required()
                    ->rows(3)
                    ->helperText('Escribe los nombres separados por coma.')
                    ->columnSpanFull(),
                TextInput::make('plan_url')
                    ->label('Enlace del plan')
                    ->required()
                    ->placeholder('https://dominio.com/plan.pdf o /storage/documentos/plan.pdf')
                    ->maxLength(2048)
                    ->rule(static function (): Closure {
                        return function (string $attribute, mixed $value, Closure $fail): void {
                            if (! is_string($value) || ! static::isValidPlanUrl(trim($value))) {
                                $fail('El enlace debe ser una URL http/https o una ruta interna iniciando con "/".');
                            }
                        };
                    }),
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

    private static function isValidPlanUrl(string $value): bool
    {
        if ($value === '') {
            return false;
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
