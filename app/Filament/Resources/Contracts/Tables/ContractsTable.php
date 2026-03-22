<?php

namespace App\Filament\Resources\Contracts\Tables;

use App\Models\Contract;
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

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('publication_date', 'desc')
            ->columns([
                TextColumn::make('process_code')
                    ->label('ID proceso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contractType.name')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fiscal_year')
                    ->label('Vigencia')
                    ->sortable(),
                TextColumn::make('object')
                    ->label('Objeto')
                    ->searchable()
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('official_budget')
                    ->label('Cuantia')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),
                TextColumn::make('process_status')
                    ->label('Estado proceso')
                    ->formatStateUsing(fn (string $state): string => Contract::PROCESS_STATUS_OPTIONS[$state] ?? $state)
                    ->badge(),
                TextColumn::make('status')
                    ->label('Publicacion')
                    ->formatStateUsing(fn (string $state): string => Contract::STATUS_OPTIONS[$state] ?? $state)
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('documents_count')
                    ->label('Docs')
                    ->counts('documents'),
            ])
            ->filters([
                SelectFilter::make('fiscal_year')
                    ->label('Vigencia')
                    ->options(fn (): array => Contract::query()
                        ->select('fiscal_year')
                        ->distinct()
                        ->orderByDesc('fiscal_year')
                        ->pluck('fiscal_year', 'fiscal_year')
                        ->map(fn (mixed $year): string => (string) $year)
                        ->all()),
                SelectFilter::make('process_status')
                    ->label('Estado proceso')
                    ->options(Contract::PROCESS_STATUS_OPTIONS),
                SelectFilter::make('status')
                    ->label('Estado publicacion')
                    ->options(Contract::STATUS_OPTIONS),
                SelectFilter::make('contract_type_id')
                    ->label('Tipo de contrato')
                    ->relationship('contractType', 'name'),
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
