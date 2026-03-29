<?php

namespace App\Notifications;

use App\Models\PqrsRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PqrsReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PqrsRequest $pqrsRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabels = [
            'peticion' => 'Peticion',
            'queja' => 'Queja',
            'reclamo' => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'felicitacion' => 'Felicitacion',
            'tramite' => 'Tramite',
        ];

        $typeLabel = $typeLabels[$this->pqrsRequest->type] ?? ucfirst($this->pqrsRequest->type);
        $applicantLabel = filled($this->pqrsRequest->applicant_name)
            ? (string) $this->pqrsRequest->applicant_name
            : 'Ciudadano/a';
        $modalityLabel = $this->pqrsRequest->is_anonymous ? 'Anonima' : 'Identificada';
        $messageSummary = Str::limit(trim((string) $this->pqrsRequest->message), 180);

        return (new MailMessage)
            ->subject("Confirmacion de radicacion PQRS - {$this->pqrsRequest->tracking_code}")
            ->greeting("Hola {$applicantLabel},")
            ->line('Tu solicitud ha sido radicada exitosamente en nuestro sistema.')
            ->line("**Codigo de seguimiento:** {$this->pqrsRequest->tracking_code}")
            ->line("**Tipo:** {$typeLabel}")
            ->line("**Modalidad:** {$modalityLabel}")
            ->line("**Resumen del mensaje:** {$messageSummary}")
            ->line("**Fecha de radicacion:** {$this->pqrsRequest->submitted_at?->translatedFormat('d M Y H:i')}")
            ->action('Consultar estado', route('atencion.pqrs.track'))
            ->line('Conserva tu codigo de seguimiento para consultar el estado de tu solicitud en cualquier momento.')
            ->salutation('Cordialmente, IED Agropecuaria Jose Maria Herrera');
    }
}
