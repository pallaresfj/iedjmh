<?php

namespace App\Filament\Resources\Graduates\Schemas;

use App\Models\Graduate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class GraduateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('national_id')
                    ->label('Identificacion nacional')
                    ->required()
                    ->maxLength(80)
                    ->unique(Graduate::class, 'national_id', ignoreRecord: true),
                TextInput::make('full_name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('graduation_year')
                    ->label('Ano de graduacion')
                    ->required()
                    ->numeric()
                    ->minValue(1980)
                    ->maxValue((int) now()->format('Y') + 1),
                TextInput::make('email')
                    ->label('Correo electronico')
                    ->email()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim((string) $state) : null)
                    ->unique(Graduate::class, 'email', ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Telefono')
                    ->maxLength(80),
                TextInput::make('current_occupation')
                    ->label('Ocupacion actual')
                    ->maxLength(255),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(255),
                TextInput::make('country')
                    ->label('Pais')
                    ->maxLength(255),
                Select::make('status')
                    ->label('Estado')
                    ->options(Graduate::STATUS_OPTIONS)
                    ->required()
                    ->default('preloaded')
                    ->native(false),
                Select::make('record_verification_status')
                    ->label('Verificacion')
                    ->options(Graduate::VERIFICATION_STATUS_OPTIONS)
                    ->required()
                    ->default('pending')
                    ->native(false),
                TextInput::make('academic_title')
                    ->label('Titulo academico')
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('graduation_date')
                    ->label('Fecha de grado'),
                TextInput::make('graduation_act_number')
                    ->label('Acta')
                    ->maxLength(120),
                TextInput::make('graduation_folio')
                    ->label('Folio')
                    ->maxLength(120),
                TextInput::make('password')
                    ->label('Nueva contrasena')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8),
                DateTimePicker::make('data_processing_consent_at')
                    ->label('Consentimiento de datos')
                    ->seconds(false),
                DateTimePicker::make('activated_at')
                    ->label('Activado en')
                    ->seconds(false),
                DateTimePicker::make('last_login_at')
                    ->label('Ultimo acceso')
                    ->seconds(false)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
