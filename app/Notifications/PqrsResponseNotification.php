<?php

namespace App\Notifications;

use App\Models\PqrsMessage;
use App\Models\PqrsRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PqrsResponseNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PqrsRequest $pqrsRequest,
        private readonly PqrsMessage $pqrsMessage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $applicantLabel = filled($this->pqrsRequest->applicant_name)
            ? (string) $this->pqrsRequest->applicant_name
            : 'Ciudadano/a';
        $responseSubject = trim((string) ($this->pqrsMessage->subject ?? ''));
        $mailSubject = $responseSubject !== ''
            ? "{$responseSubject} - {$this->pqrsRequest->tracking_code}"
            : "Actualizacion de tu solicitud PQRS - {$this->pqrsRequest->tracking_code}";
        $responseContent = trim(strip_tags((string) $this->pqrsMessage->message));
        $responseContent = $responseContent !== ''
            ? Str::limit($responseContent, 1500)
            : 'Se registro una nueva actualizacion en tu solicitud.';

        $mailMessage = (new MailMessage)
            ->subject($mailSubject)
            ->greeting("Hola {$applicantLabel},")
            ->line("Tu solicitud con codigo **{$this->pqrsRequest->tracking_code}** tiene una nueva respuesta.");

        if ($responseSubject !== '') {
            $mailMessage->line("Asunto: {$responseSubject}");
        }

        return $mailMessage
            ->line('---')
            ->line($responseContent)
            ->line('---')
            ->action('Ver historial completo', route('atencion.pqrs.track'))
            ->line('Usa tu codigo de seguimiento y correo electronico para consultar el estado completo de tu solicitud.')
            ->salutation('Cordialmente, IED Agropecuaria Jose Maria Herrera');
    }
}
