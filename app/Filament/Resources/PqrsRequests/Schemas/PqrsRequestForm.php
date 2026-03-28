<?php

namespace App\Filament\Resources\PqrsRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Throwable;

class PqrsRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make()
                    ->columns(4)
                    ->schema([
                        TextInput::make('tracking_code')
                            ->label('Radicado')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Se genera automaticamente'),
                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'peticion' => 'Peticion',
                                'queja' => 'Queja',
                                'reclamo' => 'Reclamo',
                                'sugerencia' => 'Sugerencia',
                                'felicitacion' => 'Felicitacion',
                            ])
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'received' => 'Recibida',
                                'in_process' => 'En proceso',
                                'resolved' => 'Resuelta',
                                'closed' => 'Cerrada',
                            ])
                            ->required()
                            ->default('received')
                            ->native(false),
                        Select::make('priority')
                            ->label('Prioridad')
                            ->options([
                                'low' => 'Baja',
                                'medium' => 'Media',
                                'high' => 'Alta',
                                'urgent' => 'Urgente',
                            ])
                            ->required()
                            ->default('medium')
                            ->native(false),
                    ]),
                Grid::make()
                    ->columns(4)
                    ->schema([
                        DateTimePicker::make('submitted_at')
                            ->label('Fecha de radicacion')
                            ->disabledOn('edit')
                            ->seconds(false),
                        DateTimePicker::make('resolved_at')
                            ->label('Fecha de cierre')
                            ->seconds(false),
                        Select::make('assigned_to')
                            ->label('Asignado a')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload(),
                        Toggle::make('consent_habeas_data')
                            ->label('Acepta tratamiento')
                            ->disabledOn('edit')
                            ->default(false),
                    ]),
                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('applicant_name')
                            ->label('Solicitante')
                            ->required()
                            ->disabledOn('edit')
                            ->maxLength(255),
                        TextInput::make('applicant_document')
                            ->label('Documento')
                            ->disabledOn('edit')
                            ->maxLength(120),
                        TextInput::make('applicant_email')
                            ->label('Correo')
                            ->email()
                            ->disabledOn('edit')
                            ->maxLength(255),
                    ]),
                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('applicant_phone')
                            ->label('Telefono')
                            ->disabledOn('edit')
                            ->maxLength(80),
                        TextInput::make('applicant_address')
                            ->label('Direccion')
                            ->disabledOn('edit')
                            ->maxLength(255),
                        TextInput::make('municipality')
                            ->label('Municipio')
                            ->disabledOn('edit')
                            ->maxLength(120),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('subject')
                            ->label('Asunto')
                            ->required()
                            ->disabledOn('edit')
                            ->maxLength(255),
                        Placeholder::make('attachment_display')
                            ->label('Adjunto')
                            ->content(function (Get $get): HtmlString|string {
                                $path = (string) ($get('attachment_path') ?? '');

                                if ($path === '') {
                                    return 'Sin adjunto';
                                }

                                try {
                                    if (Storage::disk('local')->exists($path)) {
                                        $url = Storage::disk('local')->temporaryUrl($path, now()->addMinutes(30));

                                        return new HtmlString('<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">Ver adjunto</a>');
                                    }
                                } catch (Throwable) {
                                    // If the disk cannot generate a temporary URL, show the stored path as fallback.
                                }

                                return $path;
                            }),
                    ]),
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Textarea::make('message')
                            ->label('Mensaje')
                            ->required()
                            ->disabledOn('edit')
                            ->rows(8)
                            ->maxLength(5000),
                    ]),
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Notas internas')
                            ->rows(5),
                    ]),
            ]);
    }
}
