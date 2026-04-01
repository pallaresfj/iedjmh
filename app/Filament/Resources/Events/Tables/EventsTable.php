<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use App\Support\Categories\CategoryScope;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Inicia')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Finaliza')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lugar')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime()
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
                    ->relationship(
                        'categories',
                        'name',
                        function (Builder $query): void {
                            CategoryScope::applySubcategoryScope($query, CategoryScope::EVENTS);
                        },
                    ),
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
                        $data['published_at'] = null;
                        $data['slug'] = static::generateReplicaSlug($originalSlug);

                        return $data;
                    })
                    ->after(function (ReplicateAction $action): void {
                        $record = $action->getRecord();
                        $replica = $action->getReplica();

                        if (! $record instanceof Event || ! $replica instanceof Event) {
                            return;
                        }

                        $categorySyncData = $record->categories
                            ->mapWithKeys(fn ($category): array => [
                                $category->getKey() => [
                                    'sort_order' => (int) ($category->pivot?->sort_order ?? 0),
                                ],
                            ])
                            ->all();

                        $replica->categories()->sync($categorySyncData);
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
            $sourceSlug = 'evento';
        }

        $baseSlug = Str::slug($sourceSlug);

        if ($baseSlug === '') {
            $baseSlug = 'evento';
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
        return Event::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->exists();
    }
}
