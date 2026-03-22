<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostSubmittedByCollaboratorNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Post $post,
        private readonly User $submittedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Nueva noticia enviada por colaborador')
            ->greeting('Hola,')
            ->line("{$this->submittedBy->name} envio una noticia para revision.")
            ->line("Titulo: {$this->post->title}")
            ->action('Revisar noticia', route('filament.admin.resources.posts.edit', ['record' => $this->post]))
            ->line('Ingresa al panel para revisarla y publicar cuando corresponda.');
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Nueva noticia enviada por colaborador')
            ->body("{$this->submittedBy->name} envio: {$this->post->title}")
            ->actions([
                Action::make('ver-noticia')
                    ->label('Revisar')
                    ->url(route('filament.admin.resources.posts.edit', ['record' => $this->post])),
            ])
            ->getDatabaseMessage();
    }
}
