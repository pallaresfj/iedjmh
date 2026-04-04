<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Datos generales')
                    ->columns(12)
                    ->schema([
                        TextInput::make('process_code')
                            ->label('ID proceso')
                            ->helperText('Si lo dejas vacio, se genera automaticamente con formato FSE-XXX-YYYY.')
                            ->unique(Contract::class, 'process_code', ignoreRecord: true)
                            ->maxLength(20)
                            ->columnSpan(['default' => 'full', 'lg' => 4]),
                        TextInput::make('fiscal_year')
                            ->label('Vigencia')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set, mixed $state) => static::syncProcessCode($get, $set, $state))
                            ->columnSpan(['default' => 'full', 'lg' => 2]),
                        Select::make('contract_type_id')
                            ->label('Tipo de contrato')
                            ->relationship(
                                name: 'contractType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->where('status', 'published')
                                    ->orderBy('sort_order')
                                    ->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(['default' => 'full', 'lg' => 6]),
                        TextInput::make('official_budget')
                            ->label('Presupuesto oficial')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        Select::make('status')
                            ->label('Estado de publicacion')
                            ->options(Contract::STATUS_OPTIONS)
                            ->default('draft')
                            ->helperText(fn (Get $get): ?string => static::publicationRequirementsHint((string) $get('process_status')))
                            ->required()
                            ->native(false)
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        DateTimePicker::make('published_at')
                            ->label('Publicado en')
                            ->seconds(false)
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        Select::make('process_status')
                            ->label('Estado del proceso')
                            ->options(Contract::PROCESS_STATUS_OPTIONS)
                            ->default('en_curso')
                            ->required()
                            ->native(false)
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        Textarea::make('object')
                            ->label('Objeto del contrato')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        DatePicker::make('publication_date')
                            ->label('Fecha de publicacion')
                            ->live()
                            ->maxDate(fn (Get $get): ?string => static::minDateFromValues([
                                $get('offers_deadline_date'),
                                $get('evaluation_date'),
                                $get('award_date'),
                            ]))
                            ->rule(static function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $publicationDate = static::parseDateValue($value);

                                    if (! $publicationDate) {
                                        return;
                                    }

                                    $comparisons = [
                                        'cierre de ofertas' => $get('offers_deadline_date'),
                                        'evaluacion' => $get('evaluation_date'),
                                        'adjudicacion' => $get('award_date'),
                                    ];

                                    foreach ($comparisons as $label => $comparisonValue) {
                                        $comparisonDate = static::parseDateValue($comparisonValue);

                                        if ($comparisonDate && $publicationDate->gt($comparisonDate)) {
                                            $fail("La fecha de publicacion no puede ser posterior a la fecha de {$label}.");

                                            return;
                                        }
                                    }
                                };
                            })
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        DatePicker::make('offers_deadline_date')
                            ->label('Fecha limite de ofertas')
                            ->live()
                            ->minDate(fn (Get $get): ?string => static::datePickerValue($get('publication_date')))
                            ->maxDate(fn (Get $get): ?string => static::minDateFromValues([
                                $get('evaluation_date'),
                                $get('award_date'),
                            ]))
                            ->rule(static function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $offersDeadlineDate = static::parseDateValue($value);

                                    if (! $offersDeadlineDate) {
                                        return;
                                    }

                                    $publicationDate = static::parseDateValue($get('publication_date'));

                                    if (! $publicationDate) {
                                        $fail('Para definir la fecha limite de ofertas debes registrar primero la fecha de publicacion.');

                                        return;
                                    }

                                    if ($offersDeadlineDate->lt($publicationDate)) {
                                        $fail('La fecha limite de ofertas no puede ser anterior a la fecha de publicacion.');

                                        return;
                                    }

                                    $comparisons = [
                                        'evaluacion' => $get('evaluation_date'),
                                        'adjudicacion' => $get('award_date'),
                                    ];

                                    foreach ($comparisons as $label => $comparisonValue) {
                                        $comparisonDate = static::parseDateValue($comparisonValue);

                                        if ($comparisonDate && $offersDeadlineDate->gt($comparisonDate)) {
                                            $fail("La fecha limite de ofertas no puede ser posterior a la fecha de {$label}.");

                                            return;
                                        }
                                    }
                                };
                            })
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        DatePicker::make('evaluation_date')
                            ->label('Fecha de evaluacion')
                            ->live()
                            ->minDate(fn (Get $get): ?string => static::datePickerValue($get('offers_deadline_date')))
                            ->maxDate(fn (Get $get): ?string => static::datePickerValue($get('award_date')))
                            ->rule(static function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $evaluationDate = static::parseDateValue($value);

                                    if (! $evaluationDate) {
                                        return;
                                    }

                                    $offersDeadlineDate = static::parseDateValue($get('offers_deadline_date'));

                                    if (! $offersDeadlineDate) {
                                        $fail('Para definir la fecha de evaluacion debes registrar primero la fecha limite de ofertas.');

                                        return;
                                    }

                                    if ($evaluationDate->lt($offersDeadlineDate)) {
                                        $fail('La fecha de evaluacion no puede ser anterior a la fecha limite de ofertas.');

                                        return;
                                    }

                                    $awardDate = static::parseDateValue($get('award_date'));

                                    if ($awardDate && $evaluationDate->gt($awardDate)) {
                                        $fail('La fecha de evaluacion no puede ser posterior a la fecha de adjudicacion.');
                                    }
                                };
                            })
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        DatePicker::make('award_date')
                            ->label('Fecha de adjudicacion')
                            ->live()
                            ->minDate(fn (Get $get): ?string => static::datePickerValue($get('evaluation_date')))
                            ->rule(static function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $awardDate = static::parseDateValue($value);

                                    if (! $awardDate) {
                                        return;
                                    }

                                    $evaluationDate = static::parseDateValue($get('evaluation_date'));

                                    if (! $evaluationDate) {
                                        $fail('Para definir la fecha de adjudicacion debes registrar primero la fecha de evaluacion.');

                                        return;
                                    }

                                    if ($awardDate->lt($evaluationDate)) {
                                        $fail('La fecha de adjudicacion no puede ser anterior a la fecha de evaluacion.');
                                    }
                                };
                            })
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        TextInput::make('secop_ii_url')
                            ->label('Enlace SECOP II')
                            ->placeholder('https://...')
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
                            })
                            ->columnSpanFull(),
                        Repeater::make('participants')
                            ->label('Oferentes / Contratistas')
                            ->helperText('Para publicar un proceso adjudicado debes registrar participantes y marcar exactamente un ganador.')
                            ->relationship('participants')
                            ->extraAttributes(static function (Repeater $component): array {
                                $statePath = $component->getStatePath();

                                return [
                                    'x-data' => '{ participantsAllCollapsed: false }',
                                    'x-on:repeater-collapse.window' => "\$event.detail === '{$statePath}' && (participantsAllCollapsed = true)",
                                    'x-on:repeater-expand.window' => "\$event.detail === '{$statePath}' && (participantsAllCollapsed = false)",
                                ];
                            }, merge: true)
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->addActionLabel('Agregar contratista')
                            ->collapseAction(fn (Action $action): Action => $action
                                ->label('Contraer')
                                ->icon('heroicon-o-chevron-up')
                                ->color('gray')
                                ->button())
                            ->expandAction(fn (Action $action): Action => $action
                                ->label('Expandir')
                                ->icon('heroicon-o-chevron-down')
                                ->color('gray')
                                ->button())
                            ->collapseAllAction(fn (Action $action): Action => $action
                                ->label('Contraer todo')
                                ->icon('heroicon-o-chevron-up')
                                ->color('gray')
                                ->extraAttributes([
                                    'x-show' => '! participantsAllCollapsed',
                                    'x-cloak' => '',
                                ], merge: true)
                                ->button())
                            ->expandAllAction(fn (Action $action): Action => $action
                                ->label('Expandir todo')
                                ->icon('heroicon-o-chevron-down')
                                ->color('gray')
                                ->extraAttributes([
                                    'x-show' => 'participantsAllCollapsed',
                                    'x-cloak' => '',
                                ], merge: true)
                                ->button())
                            ->addAction(static function (Action $action, Repeater $component): Action {
                                return $action
                                    ->schema([
                                        Select::make('contractor_id')
                                            ->label('Contratista recurrente')
                                            ->searchable()
                                            ->options(fn (): array => static::activeContractorOptions($component->getRawState()))
                                            ->required()
                                            ->native(false)
                                            ->helperText('Solo se muestran contratistas que no hayan sido agregados en este proceso.'),
                                    ])
                                    ->modalHeading('Agregar contratista')
                                    ->modalDescription('Selecciona un contratista recurrente para precargar sus datos en la lista de oferentes.')
                                    ->modalSubmitActionLabel('Agregar')
                                    ->action(function (array $data, mixed $component): void {
                                        if (! $component instanceof Repeater) {
                                            return;
                                        }

                                        static::appendParticipantFromCatalogSelection($component, (int) ($data['contractor_id'] ?? 0));
                                    });
                            })
                            ->itemLabel(function (array $state): ?string {
                                $name = static::normalizeStringValue($state['name'] ?? null);
                                $nit = static::normalizeStringValue($state['nit'] ?? null);

                                if ($name === '' && $nit === '') {
                                    return 'Contratista';
                                }

                                return trim("{$name} {$nit}");
                            })
                            ->columns(12)
                            ->schema([
                                Hidden::make('contractor_id'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                TextInput::make('nit')
                                    ->label('NIT')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                TextInput::make('social_object')
                                    ->label('Objeto social')
                                    ->required()
                                    ->maxLength(2048)
                                    ->columnSpan(['default' => 'full', 'lg' => 4]),
                                TextInput::make('evaluation_score')
                                    ->label('Puntaje')
                                    ->numeric()
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                Toggle::make('is_awarded')
                                    ->label('Adjudicado')
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpan(['default' => 'full', 'lg' => 1]),
                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(['default' => 'full', 'lg' => 1]),
                            ])
                            ->rule(static function (): Closure {
                                return function (string $attribute, mixed $value, Closure $fail): void {
                                    static::validateParticipantsCollection($value, $fail);
                                };
                            })
                            ->columnSpanFull(),
                        TextInput::make('contractor_name')
                            ->label('Contratista ganador (sincronizado)')
                            ->helperText('Este campo se sincroniza automaticamente con el participante marcado como adjudicado.')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->columnSpan(['default' => 'full', 'lg' => 4]),
                        TextInput::make('contractor_nit')
                            ->label('NIT ganador (sincronizado)')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(100)
                            ->columnSpan(['default' => 'full', 'lg' => 3]),
                        Textarea::make('contractor_social_object')
                            ->label('Objeto social ganador (sincronizado)')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2)
                            ->columnSpan(['default' => 'full', 'lg' => 5]),
                    ])
                    ->columnSpanFull(),

                Fieldset::make('Repositorio documental')
                    ->schema([
                        Repeater::make('documents')
                            ->label('Documentos del proceso')
                            ->helperText(fn (Get $get): ?string => static::publicationRequirementsHint((string) ($get('process_status') ?? $get('../process_status'))))
                            ->relationship('documents')
                            ->extraAttributes(static function (Repeater $component): array {
                                $statePath = $component->getStatePath();

                                return [
                                    'x-data' => '{ docsAllCollapsed: false }',
                                    'x-on:repeater-collapse.window' => "\$event.detail === '{$statePath}' && (docsAllCollapsed = true)",
                                    'x-on:repeater-expand.window' => "\$event.detail === '{$statePath}' && (docsAllCollapsed = false)",
                                ];
                            }, merge: true)
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Agregar documento')
                            ->collapseAction(fn (Action $action): Action => $action
                                ->label('Contraer')
                                ->icon('heroicon-o-chevron-up')
                                ->color('gray')
                                ->button())
                            ->expandAction(fn (Action $action): Action => $action
                                ->label('Expandir')
                                ->icon('heroicon-o-chevron-down')
                                ->color('gray')
                                ->button())
                            ->collapseAllAction(fn (Action $action): Action => $action
                                ->label('Contraer todo')
                                ->icon('heroicon-o-chevron-up')
                                ->color('gray')
                                ->extraAttributes([
                                    'x-show' => '! docsAllCollapsed',
                                    'x-cloak' => '',
                                ], merge: true)
                                ->button())
                            ->expandAllAction(fn (Action $action): Action => $action
                                ->label('Expandir todo')
                                ->icon('heroicon-o-chevron-down')
                                ->color('gray')
                                ->extraAttributes([
                                    'x-show' => 'docsAllCollapsed',
                                    'x-cloak' => '',
                                ], merge: true)
                                ->button())
                            ->itemLabel(function (array $state): ?string {
                                $type = (string) ($state['document_type'] ?? '');
                                $title = trim((string) ($state['title'] ?? ''));

                                if ($title !== '') {
                                    return $title;
                                }

                                return ContractDocument::labelForType($type);
                            })
                            ->columns(12)
                            ->schema([
                                Select::make('stage')
                                    ->label('Etapa')
                                    ->options(ContractDocument::STAGE_OPTIONS)
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => static::syncDocumentTypeFromStage($get, $set, $state))
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                Select::make('document_type')
                                    ->label('Tipo de documento')
                                    ->options(fn (Get $get): array => ContractDocument::documentTypeOptionsForStage(static::normalizeStringValue($get('stage'))))
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(['default' => 'full', 'lg' => 3]),
                                TextInput::make('title')
                                    ->label('Titulo del documento')
                                    ->placeholder('Obligatorio cuando el tipo es "Otro"')
                                    ->visible(fn (Get $get): bool => $get('document_type') === 'otro')
                                    ->dehydratedWhenHidden()
                                    ->dehydrateStateUsing(fn (Get $get, mixed $state): string => static::resolveDocumentTitle($get, $state))
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 'full', 'lg' => 2]),
                                TextInput::make('external_url')
                                    ->label('URL externa')
                                    ->placeholder('https://... o /storage/...')
                                    ->required()
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
                                    })
                                    ->columnSpan(fn (Get $get): array => [
                                        'default' => 'full',
                                        'lg' => $get('document_type') === 'otro' ? 4 : 6,
                                    ]),
                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(['default' => 'full', 'lg' => 1]),
                            ])
                            ->rule(static function (): Closure {
                                return function (string $attribute, mixed $value, Closure $fail): void {
                                    static::validateDocumentsCollection($value, $fail);
                                };
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function syncProcessCode(Get $get, Set $set, mixed $fiscalYear): void
    {
        $year = (int) $fiscalYear;

        if ($year < 2020 || $year > 2100) {
            return;
        }

        if (trim((string) ($get('process_code') ?? '')) !== '') {
            return;
        }

        $set('process_code', Contract::nextProcessCode($year));
    }

    private static function syncDocumentTypeFromStage(Get $get, Set $set, ?string $stage): void
    {
        $documentType = static::normalizeStringValue($get('document_type'));

        if ($documentType === '') {
            return;
        }

        if (! ContractDocument::isDocumentTypeAllowedForStage($stage, $documentType)) {
            $set('document_type', null);
        }
    }

    /**
     * @return array<int, string>
     */
    private static function activeContractorOptions(mixed $participantsState = null): array
    {
        [$selectedContractorIds, $selectedNits] = static::extractSelectedParticipantSignatures($participantsState);

        return Contractor::query()
            ->where('is_active', true)
            ->when($selectedContractorIds !== [], fn (Builder $query): Builder => $query->whereNotIn('id', $selectedContractorIds))
            ->orderBy('name')
            ->get()
            ->filter(function (Contractor $contractor) use ($selectedNits): bool {
                $contractorNit = static::normalizeNit($contractor->nit);

                if ($contractorNit === '') {
                    return true;
                }

                return ! in_array($contractorNit, $selectedNits, true);
            })
            ->mapWithKeys(fn (Contractor $contractor): array => [
                $contractor->id => "{$contractor->name} ({$contractor->nit})",
            ])
            ->all();
    }

    private static function appendParticipantFromCatalogSelection(Repeater $component, int $contractorId): void
    {
        if ($contractorId <= 0) {
            return;
        }

        $contractor = Contractor::query()->find($contractorId);

        if (! $contractor) {
            return;
        }

        $items = $component->getRawState();

        if (! is_array($items)) {
            $items = [];
        }

        $selectedNit = static::normalizeNit($contractor->nit);

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $existingContractorId = is_numeric($item['contractor_id'] ?? null) ? (int) $item['contractor_id'] : null;
            $existingNit = static::normalizeNit(static::normalizeStringValue($item['nit'] ?? null));

            if ($existingContractorId === $contractor->id || ($existingNit !== '' && $existingNit === $selectedNit)) {
                throw ValidationException::withMessages([
                    'participants' => 'Ese oferente ya fue agregado en este proceso.',
                ]);
            }
        }

        $participant = [
            'contractor_id' => $contractor->id,
            'name' => $contractor->name,
            'nit' => $contractor->nit,
            'social_object' => $contractor->social_object,
            'evaluation_score' => null,
            'is_awarded' => false,
            'sort_order' => static::nextParticipantSortOrder($items),
        ];

        $newUuid = $component->generateUuid();

        if ($newUuid) {
            $items[$newUuid] = $participant;
        } else {
            $items[] = $participant;
        }

        $component->rawState($items);
        $component->collapsed(false, shouldMakeComponentCollapsible: false);
        $component->callAfterStateUpdated();

        if ($component->shouldPartiallyRenderAfterActionsCalled()) {
            $component->partiallyRender();
        }
    }

    /**
     * @param  array<int|string, mixed>  $items
     */
    private static function nextParticipantSortOrder(array $items): int
    {
        $maxSortOrder = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $sortOrder = $item['sort_order'] ?? null;

            if (is_numeric($sortOrder)) {
                $maxSortOrder = max($maxSortOrder, (int) $sortOrder);
            }
        }

        return $maxSortOrder + 1;
    }

    /**
     * @return array{0: array<int>, 1: array<string>}
     */
    private static function extractSelectedParticipantSignatures(mixed $participantsState): array
    {
        if (! is_array($participantsState)) {
            return [[], []];
        }

        $selectedContractorIds = [];
        $selectedNits = [];

        foreach ($participantsState as $participant) {
            if (! is_array($participant)) {
                continue;
            }

            if (is_numeric($participant['contractor_id'] ?? null)) {
                $selectedContractorIds[] = (int) $participant['contractor_id'];
            }

            $nit = static::normalizeNit(static::normalizeStringValue($participant['nit'] ?? null));

            if ($nit !== '') {
                $selectedNits[] = $nit;
            }
        }

        return [
            array_values(array_unique($selectedContractorIds)),
            array_values(array_unique($selectedNits)),
        ];
    }

    private static function validateParticipantsCollection(mixed $participants, Closure $fail): void
    {
        if (! is_array($participants)) {
            return;
        }

        $seenContractors = [];
        $seenNits = [];

        foreach ($participants as $participant) {
            if (! is_array($participant)) {
                continue;
            }

            $contractorId = is_numeric($participant['contractor_id'] ?? null) ? (int) $participant['contractor_id'] : null;
            $nit = static::normalizeNit(static::normalizeStringValue($participant['nit'] ?? null));

            if ($contractorId !== null) {
                if (isset($seenContractors[$contractorId])) {
                    $fail('No puedes registrar dos veces el mismo oferente.');

                    return;
                }

                $seenContractors[$contractorId] = true;
            }

            if ($nit !== '') {
                if (isset($seenNits[$nit])) {
                    $fail('No puedes registrar dos veces el mismo oferente (NIT duplicado).');

                    return;
                }

                $seenNits[$nit] = true;
            }
        }
    }

    private static function validateDocumentsCollection(mixed $documents, Closure $fail): void
    {
        if (! is_array($documents)) {
            return;
        }

        $officialCounts = [];

        foreach ($documents as $document) {
            if (! is_array($document)) {
                continue;
            }

            $type = static::normalizeStringValue($document['document_type'] ?? null);
            $stage = static::normalizeStringValue($document['stage'] ?? null);
            $title = static::normalizeStringValue($document['title'] ?? null);
            $externalUrl = static::normalizeStringValue($document['external_url'] ?? null);

            if ($type === '') {
                continue;
            }

            if ($externalUrl === '') {
                $fail('Cada documento debe tener URL externa.');

                return;
            }

            if ($externalUrl !== '' && ! static::isValidReferenceUrl($externalUrl)) {
                $fail('Hay URLs de documento invalidas.');

                return;
            }

            if ($type === 'otro' && $title === '') {
                $fail('Los documentos de tipo "Otro" deben tener titulo.');

                return;
            }

            if (! ContractDocument::isDocumentTypeAllowedForStage($stage, $type)) {
                $fail('El tipo de documento no corresponde a la etapa seleccionada.');

                return;
            }

            if (ContractDocument::isOfficialType($type)) {
                $officialCounts[$type] = ($officialCounts[$type] ?? 0) + 1;
            }
        }

        foreach ($officialCounts as $count) {
            if ($count <= 1) {
                continue;
            }

            $fail('Solo se permite un documento por cada tipo oficial.');

            return;
        }
    }

    private static function normalizeStringValue(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $normalized = static::normalizeStringValue($item);

                if ($normalized !== '') {
                    return $normalized;
                }
            }
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value);
        }

        return '';
    }

    private static function publicationRequirementsHint(string $processStatus): ?string
    {
        $requiredTypes = Contract::requiredDocumentTypesForProcessStatus($processStatus);

        if ($requiredTypes === []) {
            return null;
        }

        $labels = collect($requiredTypes)
            ->map(fn (string $type): string => ContractDocument::labelForType($type))
            ->implode(', ');

        return "Para publicar este estado debes cargar: {$labels}.";
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function minDateFromValues(array $values): ?string
    {
        $dates = collect($values)
            ->map(fn (mixed $value): ?Carbon => static::parseDateValue($value))
            ->filter()
            ->sort()
            ->values();

        /** @var Carbon|null $earliest */
        $earliest = $dates->first();

        return $earliest?->toDateString();
    }

    private static function datePickerValue(mixed $value): ?string
    {
        return static::parseDateValue($value)?->toDateString();
    }

    private static function parseDateValue(mixed $value): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function normalizeNit(string $value): string
    {
        $normalized = Str::upper(trim($value));

        return (string) preg_replace('/[^A-Z0-9]/', '', $normalized);
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

    private static function resolveDocumentTitle(Get $get, mixed $state): string
    {
        $title = static::normalizeStringValue($state);

        if ($title !== '') {
            return $title;
        }

        $documentType = static::normalizeStringValue($get('document_type'));

        if ($documentType === '') {
            return '';
        }

        return ContractDocument::labelForType($documentType);
    }
}
