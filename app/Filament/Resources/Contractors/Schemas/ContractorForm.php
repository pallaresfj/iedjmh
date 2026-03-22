<?php

namespace App\Filament\Resources\Contractors\Schemas;

use App\Models\Contractor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContractorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nit')
                    ->label('NIT')
                    ->required()
                    ->unique(Contractor::class, 'nit', ignoreRecord: true)
                    ->maxLength(100),
                Textarea::make('social_object')
                    ->label('Objeto social')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}
