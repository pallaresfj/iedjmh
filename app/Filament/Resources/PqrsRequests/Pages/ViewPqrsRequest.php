<?php

namespace App\Filament\Resources\PqrsRequests\Pages;

use App\Models\PqrsMessage;
use App\Notifications\PqrsResponseNotification;
use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Throwable;

class ViewPqrsRequest extends ViewRecord
{
    protected static string $resource = PqrsRequestResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Action::make('respond')
                ->label('Responder')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn (): bool => auth()->user()?->can('Update:PqrsRequest') ?? false)
                ->authorize(fn (): bool => auth()->user()?->can('Update:PqrsRequest') ?? false)
                ->modalHeading('Responder PQRSF')
                ->modalSubmitActionLabel('Enviar respuesta')
                ->form([
                    DateTimePicker::make('responded_at')
                        ->label('Fecha')
                        ->default(now())
                        ->seconds(false)
                        ->required(),
                    TextInput::make('subject')
                        ->label('Asunto')
                        ->default(fn (): string => "Respuesta al {$this->record->tracking_code}")
                        ->required()
                        ->maxLength(255),
                    RichEditor::make('message')
                        ->label('Mensaje')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'h2',
                            'h3',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'link',
                            'undo',
                            'redo',
                        ]),
                    FileUpload::make('attachment_pdf')
                        ->label('Adjuntar PDF')
                        ->disk('local')
                        ->directory('pqrs-responses')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(2048)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $attachmentPath = $data['attachment_pdf'] ?? null;
                    $attachments = null;

                    if (filled($attachmentPath)) {
                        $path = (string) $attachmentPath;

                        $attachments = [[
                            'disk' => 'local',
                            'path' => $path,
                            'name' => basename($path),
                            'mime' => 'application/pdf',
                        ]];
                    }

                    $responseMessage = $this->record->messages()->create([
                        'user_id' => auth()->id(),
                        'author_name' => auth()->user()?->name,
                        'author_email' => auth()->user()?->email,
                        'subject' => $data['subject'],
                        'message' => $data['message'],
                        'responded_at' => $data['responded_at'] ?? now(),
                        'is_internal' => false,
                        'attachments' => $attachments,
                    ]);

                    $mailWasSent = $this->sendResponseMailNotification($responseMessage);

                    Notification::make()
                        ->title('Respuesta guardada correctamente.')
                        ->success()
                        ->send();

                    if (! $mailWasSent) {
                        Notification::make()
                            ->title('La respuesta se guardo, pero no se pudo enviar el correo al ciudadano.')
                            ->warning()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }

    private function sendResponseMailNotification(PqrsMessage $responseMessage): bool
    {
        if (! filled($this->record->applicant_email)) {
            Log::warning('pqrs_response_mail_not_sent_missing_email', [
                'tracking_code' => $this->record->tracking_code,
                'pqrs_message_id' => $responseMessage->id,
            ]);

            return false;
        }

        try {
            $this->record->notify(new PqrsResponseNotification($this->record, $responseMessage));

            return true;
        } catch (Throwable $exception) {
            Log::warning('pqrs_response_mail_failed', [
                'tracking_code' => $this->record->tracking_code,
                'pqrs_message_id' => $responseMessage->id,
                'applicant_email' => $this->record->applicant_email,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
