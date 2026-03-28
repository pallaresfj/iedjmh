<?php

namespace App\Filament\Resources\StaffMembers\Pages;

use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Services\StaffMembers\StaffMemberBulkImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListStaffMembers extends ListRecords
{
    protected static string $resource = StaffMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadStaffMembersTemplate')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function (): void {
                        echo StaffMemberBulkImportService::templateXlsx();
                    }, 'plantilla-personal.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
            Action::make('importStaffMembers')
                ->label('Importar personal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('Create:StaffMember') ?? false)
                ->authorize(fn (): bool => auth()->user()?->can('Create:StaffMember') ?? false)
                ->modalDescription('La primera fila debe contener encabezados en espanol. Las columnas con * son obligatorias.')
                ->schema([
                    FileUpload::make('file')
                        ->label('Archivo')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'text/plain',
                        ])
                        ->helperText('Formatos permitidos: .xlsx, .xls, .csv. Obligatorias: Nombre completo * y Cargo *.')
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data, StaffMemberBulkImportService $importService): void {
                    $file = Arr::first(Arr::wrap($data['file'] ?? null));

                    if (! ($file instanceof TemporaryUploadedFile) && ! ($file instanceof UploadedFile)) {
                        Notification::make()
                            ->title('No se pudo leer el archivo cargado.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $result = $importService->import($file, (int) auth()->id());
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
                        ->title('Importacion de personal completada')
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
