<?php

namespace App\Filament\Resources\ContractTypes\Tables;

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

class ContractTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
                TextColumn::make('contracts_count')
                    ->label('Contratos')
                    ->counts('contracts'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ]),
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
