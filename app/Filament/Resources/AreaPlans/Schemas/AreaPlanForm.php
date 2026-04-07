<?php

namespace App\Filament\Resources\AreaPlans\Schemas;

use App\Models\AreaPlan;
use App\Support\PublicIcon;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
                    ->label('Icono (MS / Font Awesome)')
                    ->required()
                    ->default('ms:menu_book')
                    ->maxLength(80)
                    ->rule('regex:'.PublicIcon::validationRegex())
                    ->helperText('Formatos: ms:menu_book, fa:solid:house. Legacy Material (menu_book) permitido temporalmente.'),
                Select::make('responsibleTeachers')
                    ->label('Docentes responsables')
                    ->required()
                    ->relationship(
                        name: 'responsibleTeachers',
                        titleAttribute: 'full_name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->where('staff_group', 'teacher')
                            ->where('status', 'published')
                            ->orderBy('full_name'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->saveRelationshipsUsing(static function (Select $component, AreaPlan $record, mixed $state): void {
                        $teacherIds = collect(Arr::wrap($state))
                            ->map(static fn (mixed $id): int => (int) $id)
                            ->filter(static fn (int $id): bool => $id > 0)
                            ->unique()
                            ->values();

                        $syncPayload = $teacherIds
                            ->mapWithKeys(static fn (int $id, int $index): array => [
                                $id => ['sort_order' => $index],
                            ])
                            ->all();

                        $record->responsibleTeachers()->sync($syncPayload);
                    })
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
