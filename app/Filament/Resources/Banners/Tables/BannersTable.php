<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\Str;
use Throwable;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('page.title')
                    ->label('Pagina')
                    ->placeholder('Home / sin pagina')
                    ->visible(fn (): bool => static::hasPageIdColumn())
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('starts_at')
                    ->label('Desde')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Hasta')
                    ->dateTime()
                    ->placeholder('Permanente')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Editar')
                    ->iconSize(IconSize::Large),
                ReplicateAction::make()
                    ->label('Duplicar')
                    ->iconButton()
                    ->tooltip('Duplicar')
                    ->iconSize(IconSize::Large)
                    ->mutateRecordDataUsing(function (array $data): array {
                        $originalTitle = trim((string) ($data['title'] ?? ''));

                        if ($originalTitle !== '') {
                            $data['title'] = Str::limit("{$originalTitle} (copia)", 255, '');
                        }

                        $data['status'] = 'draft';
                        $data['slug'] = null;

                        return $data;
                    }),
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

    private static function hasPageIdColumn(): bool
    {
        try {
            return DbSchema::hasColumn('banners', 'page_id');
        } catch (Throwable) {
            return false;
        }
    }
}
