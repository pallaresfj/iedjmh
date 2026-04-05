<?php

namespace App\Filament\Resources\PqrsRequests\Pages;

use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPqrsRequest extends ViewRecord
{
    protected static string $resource = PqrsRequestResource::class;

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
                        ->default(fn (): string => "Respuesta al ID del PQRSF {$this->record->id}")
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

                    $this->record->messages()->create([
                        'user_id' => auth()->id(),
                        'author_name' => auth()->user()?->name,
                        'author_email' => auth()->user()?->email,
                        'subject' => $data['subject'],
                        'message' => $data['message'],
                        'responded_at' => $data['responded_at'] ?? now(),
                        'is_internal' => false,
                        'attachments' => $attachments,
                    ]);

                    Notification::make()
                        ->title('Respuesta enviada correctamente.')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
