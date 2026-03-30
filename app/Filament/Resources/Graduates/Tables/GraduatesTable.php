<?php

namespace App\Filament\Resources\Graduates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GraduatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('full_name')
            ->columns([
                TextColumn::make('national_id')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('graduation_year')
                    ->label('Promocion')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('record_verification_status')
                    ->label('Verificacion')
                    ->badge(),
                TextColumn::make('last_login_at')
                    ->label('Ultimo acceso')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(\App\Models\Graduate::STATUS_OPTIONS),
                SelectFilter::make('record_verification_status')
                    ->label('Verificacion')
                    ->options(\App\Models\Graduate::VERIFICATION_STATUS_OPTIONS),
                SelectFilter::make('graduation_year')
                    ->label('Promocion')
                    ->options(fn (): array => \App\Models\Graduate::query()
                        ->select('graduation_year')
                        ->distinct()
                        ->orderByDesc('graduation_year')
                        ->pluck('graduation_year', 'graduation_year')
                        ->mapWithKeys(fn ($value, $key): array => [(string) $key => (string) $value])
                        ->all()),
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
