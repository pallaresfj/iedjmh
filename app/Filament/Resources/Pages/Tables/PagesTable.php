<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
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
use Illuminate\Support\Str;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('menu_binding')
                    ->label('Vinculo menu')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
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
                        $originalSlug = trim((string) ($data['slug'] ?? ''));

                        if ($originalTitle !== '') {
                            $data['title'] = Str::limit("{$originalTitle} (copia)", 255, '');
                        }

                        $data['status'] = 'draft';
                        $data['menu_binding'] = null;
                        $data['slug'] = static::generateReplicaSlug($originalSlug);

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

    private static function generateReplicaSlug(string $originalSlug): string
    {
        $sourceSlug = trim($originalSlug);

        if ($sourceSlug === '') {
            $sourceSlug = 'pagina';
        }

        $baseSlug = Str::slug($sourceSlug);

        if ($baseSlug === '') {
            $baseSlug = 'pagina';
        }

        $copySuffix = '-copia';
        $maxSlugLength = 255;
        $baseLimit = $maxSlugLength - strlen($copySuffix);
        $basePrefix = Str::limit($baseSlug, $baseLimit, '');
        $candidate = $basePrefix.$copySuffix;

        if (! static::slugExists($candidate)) {
            return $candidate;
        }

        $counter = 2;

        while (true) {
            $indexedSuffix = "{$copySuffix}-{$counter}";
            $indexedBaseLimit = $maxSlugLength - strlen($indexedSuffix);
            $indexedBase = Str::limit($baseSlug, $indexedBaseLimit, '');
            $indexedCandidate = $indexedBase.$indexedSuffix;

            if (! static::slugExists($indexedCandidate)) {
                return $indexedCandidate;
            }

            $counter++;
        }
    }

    private static function slugExists(string $slug): bool
    {
        return Page::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->exists();
    }
}
