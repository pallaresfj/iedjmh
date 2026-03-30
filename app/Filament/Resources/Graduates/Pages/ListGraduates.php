<?php

namespace App\Filament\Resources\Graduates\Pages;

use App\Filament\Resources\Graduates\GraduateResource;
use App\Services\Graduates\GraduateBulkImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListGraduates extends ListRecords
{
    protected static string $resource = GraduateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadGraduatesTemplate')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function (): void {
                        echo GraduateBulkImportService::templateXlsx();
                    }, 'plantilla-egresados.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
            Action::make('importGraduates')
                ->label('Importar egresados')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('Create:Graduate') ?? false)
                ->authorize(fn (): bool => auth()->user()?->can('Create:Graduate') ?? false)
                ->modalDescription('La primera fila debe incluir el encabezado "Identificacion nacional *".')
                ->schema([
                    FileUpload::make('file')
                        ->label('Archivo')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'text/plain',
                        ])
                        ->helperText('Formatos permitidos: .xlsx, .xls, .csv')
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data, GraduateBulkImportService $importService): void {
                    $file = Arr::first(Arr::wrap($data['file'] ?? null));

                    if (! ($file instanceof TemporaryUploadedFile) && ! ($file instanceof UploadedFile)) {
                        Notification::make()
                            ->title('No se pudo leer el archivo cargado.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $result = $importService->import($file);
                    } catch (ValidationException $exception) {
                        $message = collect($exception->errors())
                            ->flatten()
                            ->implode('; ');

                        Notification::make()
                            ->title('No se pudo completar la importacion.')
                            ->body($message)
                            ->danger()
                            ->send();

                        return;
                    }

                    $notification = Notification::make()
                        ->title('Importacion de egresados completada')
                        ->body($result->getNotificationBody());

                    if ($result->hasFailures() && ($result->created + $result->updated) === 0) {
                        $notification->danger()->send();

                        return;
                    }

                    if ($result->hasFailures()) {
                        $notification->warning()->send();

                        return;
                    }

                    $notification->success()->send();
                }),
            CreateAction::make(),
        ];
    }
}
