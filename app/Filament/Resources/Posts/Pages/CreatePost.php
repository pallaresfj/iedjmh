<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\User;
use App\Support\Posts\PostSubmissionNotifier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (
            $user === null
            || ! method_exists($user, 'hasRole')
            || ! $user->hasRole('colaborador')
        ) {
            return;
        }

        $result = app(PostSubmissionNotifier::class)->notify($this->record, $user);
        $recipientCount = (int) ($result['recipient_count'] ?? 0);
        $failedCount = count($result['failed_recipient_ids'] ?? []);

        if ($recipientCount === 0) {
            Notification::make()
                ->warning()
                ->title('Noticia creada sin destinatarios de moderacion')
                ->body('No hay usuarios con rol editor o administrador para notificar.')
                ->send();

            return;
        }

        if ($failedCount > 0) {
            Notification::make()
                ->warning()
                ->title('Notificacion parcial de moderacion')
                ->body("Se notifico a {$recipientCount} usuario(s) y {$failedCount} presentaron error de envio.")
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Noticia enviada a moderacion')
            ->body("Se notifico a {$recipientCount} usuario(s) con rol editor/administrador.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
