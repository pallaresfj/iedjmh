<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('categories_count')
                    ->label('Categorias')
                    ->counts('categories'),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'archived' => 'Archivado',
                    ]),
                SelectFilter::make('categories')
                    ->label('Categoria')
                    ->relationship('categories', 'name'),
                Filter::make('pending_moderation')
                    ->label('Pendientes de moderacion')
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->where('status', 'draft')
                            ->whereHas('creator.roles', function (Builder $rolesQuery): void {
                                $rolesQuery->where('name', 'colaborador');
                            });
                    }),
                TernaryFilter::make('is_featured')
                    ->label('Destacado'),
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
