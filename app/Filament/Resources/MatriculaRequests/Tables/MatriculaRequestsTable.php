<?php

namespace App\Filament\Resources\MatriculaRequests\Tables;

use App\Support\Matricula\MatriculaOptions;
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

class MatriculaRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('student_name')
                    ->label('Estudiante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable(),
                TextColumn::make('grade')
                    ->label('Grado')
                    ->formatStateUsing(fn (string $state): string => MatriculaOptions::gradeOptions()[$state] ?? $state),
                TextColumn::make('campus.name')
                    ->label('Sede')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MatriculaOptions::statusOptions()[$state] ?? ucfirst($state)),
                TextColumn::make('attachments')
                    ->label('Adjuntos')
                    ->state(fn ($record): int => count($record->attachments ?? []))
                    ->formatStateUsing(fn (int $state): string => "{$state} archivo(s)")
                    ->alignCenter(),
                TextColumn::make('submitted_at')
                    ->label('Radicada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(MatriculaOptions::statusOptions()),
                SelectFilter::make('campus_id')
                    ->label('Sede')
                    ->relationship('campus', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('grade')
                    ->label('Grado')
                    ->options(MatriculaOptions::gradeOptions()),
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
