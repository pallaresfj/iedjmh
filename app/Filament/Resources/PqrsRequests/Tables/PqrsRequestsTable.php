<?php

namespace App\Filament\Resources\PqrsRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PqrsRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('tracking_code')
                    ->label('Radicado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'peticion' => 'Peticion',
                        'queja' => 'Queja',
                        'reclamo' => 'Reclamo',
                        'sugerencia' => 'Sugerencia',
                        'felicitacion' => 'Felicitacion',
                        'tramite' => 'Tramite',
                        default => ucfirst($state),
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'received' => 'Recibida',
                        'in_process' => 'En proceso',
                        'resolved', 'resuelto' => 'Resuelta',
                        'closed', 'cerrado', 'finalizado' => 'Cerrada',
                        default => ucfirst($state),
                    }),
                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                        default => ucfirst($state),
                    }),
                TextColumn::make('is_anonymous')
                    ->label('Modalidad')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Anonima' : 'Identificada'),
                TextColumn::make('message')
                    ->label('Resumen')
                    ->limit(60),
                TextColumn::make('applicant_name')
                    ->label('Solicitante')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? $state : 'Anonimo')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->label('Radicada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Asignado a')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'received' => 'Recibida',
                        'in_process' => 'En proceso',
                        'resolved' => 'Resuelta',
                        'closed' => 'Cerrada',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'peticion' => 'Peticion',
                        'queja' => 'Queja',
                        'reclamo' => 'Reclamo',
                        'sugerencia' => 'Sugerencia',
                        'felicitacion' => 'Felicitacion',
                        'tramite' => 'Tramite',
                    ]),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
                Filter::make('submitted_at')
                    ->label('Fecha de radicacion')
                    ->form([
                        DatePicker::make('submitted_from')->label('Desde'),
                        DatePicker::make('submitted_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['submitted_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '>=', $date))
                            ->when($data['submitted_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Ver')
                    ->iconSize(IconSize::Large),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Editar')
                    ->iconSize(IconSize::Large),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Eliminar')
                    ->iconSize(IconSize::Large),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
