<?php

namespace App\Filament\Resources\Contractors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContractorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('social_object')
                    ->label('Objeto social')
                    ->limit(100)
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('participants_count')
                    ->label('Participaciones')
                    ->counts('participants'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activo'),
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
                ]),
            ]);
    }
}
