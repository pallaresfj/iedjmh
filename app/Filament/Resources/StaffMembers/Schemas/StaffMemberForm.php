<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use App\Models\StaffMember;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('full_name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position_title')
                    ->label('Cargo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('department_label')
                    ->label('Dependencia / etiqueta')
                    ->placeholder('Rectoria, Academico, Administrativo, etc.')
                    ->maxLength(255),
                Select::make('staff_group')
                    ->label('Tipo de personal')
                    ->options(StaffMember::STAFF_GROUP_OPTIONS)
                    ->required()
                    ->default('directive')
                    ->native(false),
                Select::make('campus_id')
                    ->label('Sede')
                    ->relationship(
                        name: 'campus',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('institutional_email')
                    ->label('Correo institucional')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefono')
                    ->maxLength(50),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Select::make('status')
                    ->label('Estado')
                    ->options(StaffMember::STATUS_OPTIONS)
                    ->required()
                    ->default('draft')
                    ->native(false),
                DateTimePicker::make('published_at')
                    ->label('Publicado en')
                    ->seconds(false),
                FileUpload::make('profile_photo_path')
                    ->label('Foto de perfil')
                    ->image()
                    ->disk('public')
                    ->directory('staff-members')
                    ->columnSpanFull(),
            ]);
    }
}
