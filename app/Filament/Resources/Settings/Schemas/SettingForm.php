<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SettingForm
{
    private const HEX_COLOR_REGEX = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * @var array<string, string>
     */
    private const THEME_DEFAULTS = [
        'theme_primary' => '#2E7D32',
        'theme_primary_dark' => '#1B5E20',
        'theme_primary_light' => '#66BB6A',
        'theme_accent' => '#F57C00',
        'theme_gray_900' => '#263238',
        'theme_gray_700' => '#4C5A61',
        'theme_gray_600' => '#5F6F77',
        'theme_gray_200' => '#DFE5E8',
        'theme_gray_100' => '#F5F7FA',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Configuracion')
                    ->tabs([
                        Tab::make('Institucion')
                            ->schema([
                                TextInput::make('institution_name')
                                    ->label('Nombre institucion')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(6),
                                TextInput::make('dane')
                                    ->label('DANE')
                                    ->maxLength(100)
                                    ->columnSpan(3),
                                TextInput::make('nit')
                                    ->label('NIT')
                                    ->maxLength(100)
                                    ->columnSpan(3),
                                TextInput::make('academic_modality_label')
                                    ->label('Modalidad')
                                    ->maxLength(120)
                                    ->placeholder('Modalidad')
                                    ->columnSpan(6),
                                TextInput::make('academic_modality_icon')
                                    ->label('Icono Modalidad')
                                    ->helperText('Nombre de icono Material Symbols. Ejemplo: agriculture.')
                                    ->maxLength(60)
                                    ->placeholder('agriculture')
                                    ->columnSpan(6),
                                FileUpload::make('logo_path')
                                    ->label('Logo institucional')
                                    ->helperText('Admite formato PNG o SVG.')
                                    ->disk('public')
                                    ->directory('settings')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                                    ->maxSize(2048)
                                    ->fetchFileInformation(false)
                                    ->deletable()
                                    ->openable()
                                    ->columnSpan(6),
                            ])
                            ->columns(12),
                        Tab::make('Contacto y ubicacion')
                            ->schema([
                                TextInput::make('address')
                                    ->label('Direccion')
                                    ->placeholder('Carrera 5 # 12-34, Pivijay')
                                    ->maxLength(255)
                                    ->columnSpan(6),
                                TextInput::make('location')
                                    ->label('Ubicacion')
                                    ->placeholder('Pivijay, Magdalena')
                                    ->maxLength(255)
                                    ->columnSpan(6),
                                TextInput::make('location_latitude')
                                    ->label('Latitud')
                                    ->placeholder('10.595432')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('any')
                                    ->rule('between:-90,90')
                                    ->helperText('Formato decimal entre -90 y 90. Ejemplo: 10.595432.')
                                    ->columnSpan(6),
                                TextInput::make('location_longitude')
                                    ->label('Longitud')
                                    ->placeholder('-74.186521')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step('any')
                                    ->rule('between:-180,180')
                                    ->helperText('Formato decimal entre -180 y 180. Ejemplo: -74.186521.')
                                    ->columnSpan(6),
                                TextInput::make('phone')
                                    ->label('Telefono')
                                    ->tel()
                                    ->placeholder('+57 300 000 0000')
                                    ->maxLength(80)
                                    ->columnSpan(6),
                                TextInput::make('email')
                                    ->label('Correo')
                                    ->email()
                                    ->placeholder('contacto@iedjmh.edu.co')
                                    ->maxLength(255)
                                    ->columnSpan(6),
                                TextInput::make('contact_hours')
                                    ->label('Horario de atencion')
                                    ->placeholder('Lunes a viernes: 8:00 AM - 3:00 PM')
                                    ->maxLength(255)
                                    ->columnSpan(12),
                            ])
                            ->columns(12),
                        Tab::make('Plataformas y transparencia')
                            ->schema([
                                TextInput::make('siee_name')
                                    ->label('Nombre SIEE')
                                    ->maxLength(120)
                                    ->placeholder('SIEE')
                                    ->columnSpan(6),
                                TextInput::make('siee')
                                    ->label('SIEE')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->maxLength(2048)
                                    ->columnSpan(6),
                                TextInput::make('aula_virtual_name')
                                    ->label('Nombre Aula Virtual')
                                    ->maxLength(120)
                                    ->placeholder('Aula Virtual')
                                    ->columnSpan(6),
                                TextInput::make('aula_virtual')
                                    ->label('Aula Virtual')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->maxLength(2048)
                                    ->columnSpan(6),
                                Select::make('siee_document_id')
                                    ->label('Documento SIEE')
                                    ->relationship(
                                        name: 'sieeDocument',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->where('status', 'published')
                                            ->orderByDesc('published_at')
                                            ->orderBy('title'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona un documento SIEE')
                                    ->columnSpan(12),
                                Select::make('pei_document_id')
                                    ->label('PEI (documento)')
                                    ->relationship(
                                        name: 'peiDocument',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->where('status', 'published')
                                            ->orderByDesc('published_at')
                                            ->orderBy('title'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona el documento PEI')
                                    ->columnSpan(12),
                                Select::make('manual_convivencia_document_id')
                                    ->label('Manual de convivencia (documento)')
                                    ->relationship(
                                        name: 'manualConvivenciaDocument',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->where('status', 'published')
                                            ->orderByDesc('published_at')
                                            ->orderBy('title'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona el manual de convivencia')
                                    ->columnSpan(12),
                                Select::make('contracting_manual_document_id')
                                    ->label('Manual de contratacion (documento)')
                                    ->relationship(
                                        name: 'contractingManualDocument',
                                        titleAttribute: 'title',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->where('status', 'published')
                                            ->orderByDesc('published_at')
                                            ->orderBy('title'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona un documento de transparencia')
                                    ->columnSpan(12),
                                Repeater::make('allies')
                                    ->label('Aliados')
                                    ->helperText('Configura los aliados institucionales mostrados en el footer.')
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->addActionLabel('Agregar aliado')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(120),
                                        TextInput::make('url')
                                            ->label('Enlace')
                                            ->required()
                                            ->url()
                                            ->placeholder('https://...')
                                            ->maxLength(2048),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(12),
                        Tab::make('Inicio (Hero)')
                            ->schema([
                                TextInput::make('home_hero_eyebrow')
                                    ->label('Ante titulo')
                                    ->maxLength(120),
                                TextInput::make('home_hero_title')
                                    ->label('Titulo principal')
                                    ->maxLength(160)
                                    ->columnSpan(2),
                                Textarea::make('home_hero_description')
                                    ->label('Descripcion')
                                    ->rows(4)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                TextInput::make('home_hero_cta_label')
                                    ->label('Texto del boton')
                                    ->maxLength(100),
                                TextInput::make('home_hero_cta_url')
                                    ->label('URL del boton')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->maxLength(2048),
                                Select::make('home_hero_cta_target')
                                    ->label('Destino del boton')
                                    ->options([
                                        '_self' => 'Misma ventana',
                                        '_blank' => 'Nueva ventana',
                                    ])
                                    ->default('_self')
                                    ->native(false),
                                FileUpload::make('home_hero_image_path')
                                    ->label('Imagen de fondo del hero')
                                    ->helperText('Recomendado: 1920 x 800 px.')
                                    ->disk('public')
                                    ->directory('settings/home')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                                    ->maxSize(4096)
                                    ->fetchFileInformation(false)
                                    ->deletable()
                                    ->openable()
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                        Tab::make('Simbolos institucionales')
                            ->schema([
                                Fieldset::make('Bandera')
                                    ->schema([
                                        Textarea::make('symbols_flag_intro')
                                            ->label('Introduccion de bandera')
                                            ->rows(3)
                                            ->maxLength(1200)
                                            ->columnSpanFull(),
                                        Repeater::make('symbols_flag_stripes')
                                            ->label('Franjas de la bandera')
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->addActionLabel('Agregar franja')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nombre')
                                                    ->required()
                                                    ->maxLength(120),
                                                TextInput::make('color_hex')
                                                    ->label('Color HEX')
                                                    ->required()
                                                    ->placeholder('#2E7D32')
                                                    ->rule('regex:'.self::HEX_COLOR_REGEX)
                                                    ->maxLength(7),
                                                Textarea::make('description')
                                                    ->label('Descripcion')
                                                    ->required()
                                                    ->rows(2)
                                                    ->maxLength(500)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(12)
                                    ->columnSpanFull(),
                                Fieldset::make('Escudo')
                                    ->schema([
                                        Textarea::make('symbols_shield_intro')
                                            ->label('Introduccion del escudo')
                                            ->rows(3)
                                            ->maxLength(1200)
                                            ->columnSpan(6),
                                        FileUpload::make('symbols_shield_image_path')
                                            ->label('Imagen del escudo')
                                            ->helperText('Recomendado: PNG con fondo transparente.')
                                            ->disk('public')
                                            ->directory('settings/symbols')
                                            ->image()
                                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                                            ->maxSize(4096)
                                            ->fetchFileInformation(false)
                                            ->deletable()
                                            ->openable()
                                            ->columnSpan(6),
                                        Repeater::make('symbols_shield_items')
                                            ->label('Elementos del escudo')
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->addActionLabel('Agregar elemento')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Titulo')
                                                    ->required()
                                                    ->maxLength(120),
                                                TextInput::make('icon')
                                                    ->label('Icono Material')
                                                    ->required()
                                                    ->placeholder('agriculture')
                                                    ->maxLength(60),
                                                Textarea::make('description')
                                                    ->label('Descripcion')
                                                    ->required()
                                                    ->rows(2)
                                                    ->maxLength(500)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(12)
                                    ->columnSpanFull(),
                                Fieldset::make('Himno')
                                    ->schema([
                                        TextInput::make('symbols_hymn_title')
                                            ->label('Titulo de himno')
                                            ->maxLength(160)
                                            ->columnSpan(6),
                                        FileUpload::make('symbols_hymn_audio_path')
                                            ->label('Audio del himno')
                                            ->helperText('Acepta MP3, OGG, WAV o M4A.')
                                            ->disk('public')
                                            ->directory('settings/symbols')
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/mp4', 'audio/x-m4a'])
                                            ->maxSize(10240)
                                            ->fetchFileInformation(false)
                                            ->deletable()
                                            ->openable()
                                            ->columnSpan(6),
                                        Textarea::make('symbols_hymn_lyrics')
                                            ->label('Letra del himno')
                                            ->rows(12)
                                            ->maxLength(12000)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(12)
                                    ->columnSpanFull(),
                            ])
                            ->columns(12),
                        Tab::make('Identidad visual')
                            ->schema([
                                static::colorPicker('theme_primary', 'Primario', self::THEME_DEFAULTS['theme_primary']),
                                static::colorPicker('theme_primary_dark', 'Primario oscuro', self::THEME_DEFAULTS['theme_primary_dark']),
                                static::colorPicker('theme_primary_light', 'Primario claro', self::THEME_DEFAULTS['theme_primary_light']),
                                static::colorPicker('theme_accent', 'Acento', self::THEME_DEFAULTS['theme_accent']),
                                static::colorPicker('theme_gray_900', 'Gris 900', self::THEME_DEFAULTS['theme_gray_900']),
                                static::colorPicker('theme_gray_700', 'Gris 700', self::THEME_DEFAULTS['theme_gray_700']),
                                static::colorPicker('theme_gray_600', 'Gris 600', self::THEME_DEFAULTS['theme_gray_600']),
                                static::colorPicker('theme_gray_200', 'Gris 200', self::THEME_DEFAULTS['theme_gray_200']),
                                static::colorPicker('theme_gray_100', 'Gris 100', self::THEME_DEFAULTS['theme_gray_100']),
                            ])
                            ->columns(3),
                    ])
                    ->activeTab(1)
                    ->persistTab()
                    ->id('settings-tabs')
                    ->persistTabInQueryString('settings_tab')
                    ->columnSpanFull(),
            ]);
    }

    private static function colorPicker(string $name, string $label, string $default): ColorPicker
    {
        return ColorPicker::make($name)
            ->label($label)
            ->hex()
            ->default($default)
            ->formatStateUsing(fn (mixed $state): string => filled($state) ? strtoupper(trim((string) $state)) : $default)
            ->rule('regex:'.self::HEX_COLOR_REGEX)
            ->helperText("Formato HEX de 6 digitos. Color base: {$default}.");
    }
}
