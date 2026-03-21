<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('institution_name')
                    ->label('Nombre institucion')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('dane')
                    ->label('DANE')
                    ->maxLength(100),
                TextInput::make('nit')
                    ->label('NIT')
                    ->maxLength(100),
                TextInput::make('location')
                    ->label('Ubicacion')
                    ->placeholder('Pivijay, Magdalena')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('siee')
                    ->label('SIEE')
                    ->url()
                    ->placeholder('https://...')
                    ->maxLength(2048),
                TextInput::make('aula_virtual')
                    ->label('Aula Virtual')
                    ->url()
                    ->placeholder('https://...')
                    ->maxLength(2048),
                FileUpload::make('logo_path')
                    ->label('Logo institucional')
                    ->helperText('Admite formato PNG o SVG.')
                    ->disk('public')
                    ->directory('settings')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->columnSpanFull(),
            ]);
    }
}
