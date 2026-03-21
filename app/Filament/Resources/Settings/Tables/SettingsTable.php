<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('institution_name')
                    ->label('Nombre institucion')
                    ->searchable(),
                TextColumn::make('dane')
                    ->label('DANE'),
                TextColumn::make('nit')
                    ->label('NIT'),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ]);
    }
}
