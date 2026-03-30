<?php

namespace App\Filament\Resources\MatriculaRequests\Schemas;

use App\Support\Matricula\MatriculaOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Throwable;

class MatriculaRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make()
                    ->columns(5)
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Estudiante')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('document_number')
                            ->label('Documento')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('phone')
                            ->label('Telefono')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('grade')
                            ->label('Grado')
                            ->options(MatriculaOptions::gradeOptions())
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false),
                        Select::make('campus_id')
                            ->label('Sede')
                            ->relationship('campus', 'name')
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false),
                    ]),
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(MatriculaOptions::statusOptions())
                            ->required()
                            ->default('pending')
                            ->native(false),
                        DateTimePicker::make('submitted_at')
                            ->label('Fecha de radicacion')
                            ->disabled()
                            ->dehydrated(false)
                            ->seconds(false),
                        DateTimePicker::make('reviewed_at')
                            ->label('Fecha de revision')
                            ->seconds(false),
                    ]),
                Placeholder::make('attachments_display')
                    ->label('Documentos adjuntos')
                    ->content(function (Get $get): HtmlString|string {
                        $attachments = $get('attachments');

                        if (! is_array($attachments) || $attachments === []) {
                            return 'Sin archivos adjuntos';
                        }

                        $items = collect($attachments)
                            ->filter(fn (mixed $attachment): bool => is_array($attachment) && filled($attachment['path'] ?? null))
                            ->map(function (array $attachment, int $index): string {
                                $path = (string) $attachment['path'];
                                $name = (string) ($attachment['original_name'] ?? "Adjunto {$index}");
                                $url = null;

                                try {
                                    if (Storage::disk('local')->exists($path)) {
                                        $url = Storage::disk('local')->temporaryUrl($path, now()->addMinutes(30));
                                    }
                                } catch (Throwable) {
                                    $url = null;
                                }

                                if (filled($url)) {
                                    return '<li><a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">'.e($name).'</a></li>';
                                }

                                return '<li>'.e($name).'</li>';
                            })
                            ->implode('');

                        if ($items === '') {
                            return 'Sin archivos adjuntos';
                        }

                        return new HtmlString('<ul class="list-disc space-y-1 pl-4">'.$items.'</ul>');
                    }),
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Notas internas')
                            ->rows(6),
                    ]),
            ]);
    }
}
