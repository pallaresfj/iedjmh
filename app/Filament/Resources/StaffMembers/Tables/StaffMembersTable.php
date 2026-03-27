<?php

namespace App\Filament\Resources\StaffMembers\Tables;

use App\Models\StaffMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StaffMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_title')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department_label')
                    ->label('Dependencia')
                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin etiqueta')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('staff_group')
                    ->label('Grupo')
                    ->formatStateUsing(fn (?string $state): string => $state !== null ? (StaffMember::STAFF_GROUP_OPTIONS[$state] ?? $state) : 'Sin grupo')
                    ->badge(),
                TextColumn::make('campus.name')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('institutional_email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(StaffMember::STATUS_OPTIONS),
                SelectFilter::make('staff_group')
                    ->label('Tipo de personal')
                    ->options(StaffMember::STAFF_GROUP_OPTIONS),
                SelectFilter::make('campus_id')
                    ->label('Sede')
                    ->relationship('campus', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
