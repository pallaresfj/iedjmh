<?php

namespace App\Filament\Resources\Graduates\RelationManagers;

use App\Models\GraduateDocument;
use App\Rules\GoogleDriveUrl;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    /**
     * @var array<string, string>
     */
    private const TITLE_DESCRIPTION_PRESETS = [
        'Acta de grado' => 'Acta institucional de graduacion y certificacion legal.',
        'Diploma' => 'Diploma oficial emitido por la institucion para el egresado.',
        'Certificado' => 'Certificado oficial expedido por la institucion.',
        'Boletín' => 'Boletin academico institucional del egresado.',
        'Resultado Prueba Saber' => 'Resultado oficial de pruebas Saber del egresado.',
    ];

    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos del egresado';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('title_preset')
                    ->label('Titulos comunes')
                    ->options(array_combine(array_keys(self::TITLE_DESCRIPTION_PRESETS), array_keys(self::TITLE_DESCRIPTION_PRESETS)))
                    ->native(false)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            return;
                        }

                        $set('title', $state);
                        $set('description', self::TITLE_DESCRIPTION_PRESETS[$state] ?? null);
                    })
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                Select::make('type_label')
                    ->label('Tipo')
                    ->options(GraduateDocument::TYPE_OPTIONS)
                    ->required()
                    ->native(false),
                TextInput::make('drive_url')
                    ->label('URL Google Drive')
                    ->maxLength(2048)
                    ->nullable()
                    ->validationAttribute('URL Google Drive')
                    ->helperText('Opcional si adjuntas archivo. Solo se permiten enlaces de Google Drive.')
                    ->rule(new GoogleDriveUrl)
                    ->url()
                    ->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('Archivo (PDF o imagen)')
                    ->disk('local')
                    ->directory('graduates/documents')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(1024)
                    ->helperText('Opcional si registras URL de Google Drive. Tamano maximo: 1 MB.')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_official')
                    ->label('Documento oficial')
                    ->default(false),
                Toggle::make('is_visible')
                    ->label('Visible para el egresado')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                TextColumn::make('type_label')
                    ->label('Tipo')
                    ->placeholder('Otro'),
                IconColumn::make('is_official')
                    ->label('Oficial')
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear documento')
                    ->modalHeading('Crear documento')
                    ->modalSubmitActionLabel('Crear documento'),
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
