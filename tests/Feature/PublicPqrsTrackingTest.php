<?php

use App\Models\PqrsMessage;
use App\Models\PqrsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pqrs tracking renders rich text only for panel responses and escapes citizen messages', function () {
    $pqrs = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-TRACK-001',
        'type' => 'peticion',
        'is_anonymous' => false,
        'status' => 'received',
        'priority' => 'medium',
        'message' => 'Solicitud inicial para seguimiento publico.',
        'applicant_name' => 'Laura Seguimiento',
        'applicant_email' => 'laura.seguimiento@example.test',
        'submitted_at' => now()->subDays(1),
    ]);

    PqrsMessage::query()->create([
        'pqrs_request_id' => $pqrs->id,
        'author_name' => 'Laura Seguimiento',
        'author_email' => 'laura.seguimiento@example.test',
        'subject' => null,
        'message' => '<strong>Mensaje ciudadano</strong> con HTML no confiable.',
        'responded_at' => now()->subHours(4),
        'is_internal' => false,
    ]);

    $staffUser = User::factory()->create([
        'is_admin' => false,
    ]);

    PqrsMessage::query()->create([
        'pqrs_request_id' => $pqrs->id,
        'user_id' => $staffUser->id,
        'author_name' => 'Gestor PQRS',
        'author_email' => 'gestor@example.test',
        'subject' => 'Respuesta al Código completo del radicado '.$pqrs->tracking_code,
        'message' => '<p><strong>Respuesta institucional</strong> con formato enriquecido.</p>',
        'responded_at' => now()->subHours(2),
        'is_internal' => false,
    ]);

    $response = $this->post(route('atencion.pqrs.status'), [
        'tracking_code' => $pqrs->tracking_code,
        'applicant_email' => $pqrs->applicant_email,
    ]);

    $response
        ->assertOk()
        ->assertSee('Respuesta al Código completo del radicado '.$pqrs->tracking_code)
        ->assertSee('<strong>Respuesta institucional</strong>', false)
        ->assertDontSee('<strong>Mensaje ciudadano</strong>', false)
        ->assertSee('&lt;strong&gt;Mensaje ciudadano&lt;/strong&gt;', false);
});

test('pqrs tracking exposes attachment links for panel responses when available', function () {
    $pqrs = PqrsRequest::query()->create([
        'tracking_code' => 'PQRS-2026-TRACK-002',
        'type' => 'queja',
        'is_anonymous' => false,
        'status' => 'in_process',
        'priority' => 'high',
        'message' => 'Solicitud con respuesta adjunta.',
        'applicant_name' => 'Carlos Adjuntos',
        'applicant_email' => 'carlos.adjuntos@example.test',
        'submitted_at' => now()->subDay(),
    ]);

    $staffUser = User::factory()->create([
        'is_admin' => false,
    ]);

    PqrsMessage::query()->create([
        'pqrs_request_id' => $pqrs->id,
        'user_id' => $staffUser->id,
        'author_name' => 'Gestor PQRS',
        'author_email' => 'gestor@example.test',
        'subject' => 'Seguimiento adjunto',
        'message' => '<p>Adjunto en revision.</p>',
        'responded_at' => now()->subHour(),
        'is_internal' => false,
        'attachments' => [
            [
                'disk' => 'local',
                'path' => 'pqrs-responses/falso.pdf',
                'name' => 'falso.pdf',
                'mime' => 'application/pdf',
            ],
        ],
    ]);

    $this->post(route('atencion.pqrs.status'), [
        'tracking_code' => $pqrs->tracking_code,
        'applicant_email' => $pqrs->applicant_email,
    ])
        ->assertOk()
        ->assertSee('Seguimiento adjunto')
        ->assertSee('falso.pdf');
});
