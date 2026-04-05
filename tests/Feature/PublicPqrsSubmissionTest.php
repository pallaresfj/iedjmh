<?php

use App\Models\PqrsRequest;
use App\Notifications\PqrsReceivedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('public pqrs submission stores optional attachment in local disk', function () {
    Storage::fake('local');
    Notification::fake();

    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'message' => 'Este mensaje tiene la longitud suficiente para cumplir validacion.',
        'applicant_name' => 'Laura Perez',
        'applicant_email' => 'laura@example.test',
        'applicant_phone' => '3001234567',
        'applicant_document' => '10203040',
        'applicant_address' => 'Carrera 10 # 20-30',
        'attachment' => UploadedFile::fake()->create('soporte.pdf', 200, 'application/pdf'),
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHas('pqrs_success');

    $pqrs = PqrsRequest::query()->firstOrFail();

    expect($pqrs->attachment_path)->not->toBeNull();
    Storage::disk('local')->assertExists($pqrs->attachment_path);
    Notification::assertSentTo($pqrs, PqrsReceivedNotification::class);
});

test('contact form submission stores pqrs and redirects back to contact page', function () {
    Notification::fake();

    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'origin' => 'contact',
        'message' => 'Mensaje lo suficientemente largo para registrar la solicitud desde contacto.',
        'applicant_name' => 'Paula Gomez',
        'applicant_email' => 'paula@example.test',
        'applicant_phone' => '3009876543',
        'is_anonymous' => '0',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.contactenos'))
        ->assertSessionHas('pqrs_success');

    $pqrs = PqrsRequest::query()->firstOrFail();

    expect($pqrs->type)->toBe('peticion')
        ->and($pqrs->is_anonymous)->toBeFalse()
        ->and($pqrs->applicant_name)->toBe('Paula Gomez')
        ->and($pqrs->applicant_phone)->toBe('3009876543');

    Notification::assertSentTo($pqrs, PqrsReceivedNotification::class);
});

test('contact form submission does not fail when pqrs email notification mailer is unavailable', function () {
    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp.host', '127.0.0.1');
    config()->set('mail.mailers.smtp.port', 1);
    config()->set('mail.mailers.smtp.timeout', 1);

    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'origin' => 'contact',
        'message' => 'Mensaje lo suficientemente largo para registrar la solicitud desde contacto.',
        'applicant_name' => 'Paula Gomez',
        'applicant_email' => 'paula@example.test',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.contactenos'))
        ->assertSessionHas('pqrs_success');

    expect(PqrsRequest::query()->count())->toBe(1);
});

test('public pqrs submission accepts tramite as type', function () {
    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'tramite',
        'message' => 'Mensaje con contenido suficiente para validar registro de tipo tramite.',
        'applicant_name' => 'Andrea Rios',
        'applicant_email' => 'andrea@example.test',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHas('pqrs_success');

    expect(PqrsRequest::query()->firstOrFail()->type)->toBe('tramite');
});

test('public pqrs submission rejects invalid attachment extension', function () {
    Storage::fake('local');

    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'queja',
        'message' => 'Mensaje con longitud valida para disparar solo la validacion de adjunto.',
        'applicant_name' => 'Carlos Ramirez',
        'applicant_email' => 'carlos@example.test',
        'consent_habeas_data' => '1',
        'attachment' => UploadedFile::fake()->create('soporte.txt', 100, 'text/plain'),
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors('attachment');

    expect(PqrsRequest::query()->count())->toBe(0);
});

test('public pqrs submission rejects attachment larger than 2mb', function () {
    Storage::fake('local');

    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'reclamo',
        'message' => 'Mensaje con longitud valida para validar el limite maximo de tamano.',
        'applicant_name' => 'Diana Torres',
        'applicant_email' => 'diana@example.test',
        'consent_habeas_data' => '1',
        'attachment' => UploadedFile::fake()->create('grande.pdf', 3000, 'application/pdf'),
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors('attachment');

    expect(PqrsRequest::query()->count())->toBe(0);
});

test('public pqrs submission stores anonymous mode and clears identity fields', function () {
    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'is_anonymous' => '1',
        'applicant_name' => 'Debe limpiarse',
        'applicant_document' => '99999999',
        'applicant_phone' => '3001112233',
        'applicant_address' => 'Direccion temporal',
        'applicant_email' => 'anonimo@example.test',
        'message' => 'Mensaje anonimo con contenido suficiente para registrar la solicitud correctamente.',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHas('pqrs_success');

    $pqrs = PqrsRequest::query()->firstOrFail();

    expect($pqrs->is_anonymous)->toBeTrue()
        ->and($pqrs->applicant_name)->toBeNull()
        ->and($pqrs->applicant_document)->toBeNull()
        ->and($pqrs->applicant_phone)->toBeNull()
        ->and($pqrs->applicant_address)->toBeNull();
});

test('public pqrs submission requires email', function () {
    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'message' => 'Mensaje con longitud valida para validar obligatoriedad del correo.',
        'applicant_name' => 'Usuario sin correo',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors('applicant_email');
});

test('public pqrs submission validates email format', function () {
    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'message' => 'Mensaje con longitud valida para validar formato de correo.',
        'applicant_name' => 'Usuario con correo invalido',
        'applicant_email' => 'correo-invalido',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors('applicant_email');
});

test('public pqrs submission requires applicant name when request is not anonymous', function () {
    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'is_anonymous' => '0',
        'message' => 'Mensaje con longitud valida para validar obligatoriedad del nombre.',
        'applicant_email' => 'sin.nombre@example.test',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors('applicant_name');
});

test('public pqrs submission requires type and message', function () {
    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'applicant_name' => 'Usuario',
        'applicant_email' => 'usuario@example.test',
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHasErrors(['type', 'message']);
});
