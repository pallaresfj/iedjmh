<?php

namespace App\Filament\Resources\PqrsRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PqrsRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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
                TextInput::make('subject')
                    ->label('Asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->rows(8)
                    ->maxLength(5000)
                    ->columnSpanFull(),
                TextInput::make('applicant_name')
                    ->label('Solicitante')
                    ->required()
                    ->maxLength(255),
                TextInput::make('applicant_email')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255),
                TextInput::make('applicant_phone')
                    ->label('Telefono')
                    ->maxLength(80),
                TextInput::make('applicant_document')
                    ->label('Documento')
                    ->maxLength(120),
                TextInput::make('municipality')
                    ->label('Municipio')
                    ->maxLength(120),
                TextInput::make('applicant_address')
                    ->label('Direccion')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('consent_habeas_data')
                    ->label('Acepta tratamiento de datos')
                    ->default(false),
                Select::make('assigned_to')
                    ->label('Asignado a')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('submitted_at')
                    ->label('Fecha de radicacion')
                    ->seconds(false),
                DateTimePicker::make('resolved_at')
                    ->label('Fecha de cierre/resolucion')
                    ->seconds(false),
                Textarea::make('internal_notes')
                    ->label('Notas internas')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }
}
