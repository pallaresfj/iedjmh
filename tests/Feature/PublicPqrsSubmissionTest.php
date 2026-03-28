<?php

use App\Models\PqrsRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('public pqrs submission stores optional attachment in local disk', function () {
    Storage::fake('local');

    $response = $this->post(route('atencion.pqrs.store'), [
        'type' => 'peticion',
        'subject' => 'Solicitud con adjunto',
        'message' => 'Este mensaje tiene la longitud suficiente para cumplir validacion.',
        'applicant_name' => 'Laura Perez',
        'applicant_email' => 'laura@example.test',
        'applicant_phone' => '3001234567',
        'applicant_document' => '10203040',
        'applicant_address' => 'Carrera 10 # 20-30',
        'municipality' => 'Pivijay',
        'attachment' => UploadedFile::fake()->create('soporte.pdf', 200, 'application/pdf'),
        'consent_habeas_data' => '1',
    ]);

    $response
        ->assertRedirect(route('atencion.pqrs'))
        ->assertSessionHas('pqrs_success');

    $pqrs = PqrsRequest::query()->firstOrFail();

    expect($pqrs->attachment_path)->not->toBeNull();
    Storage::disk('local')->assertExists($pqrs->attachment_path);
});

test('public pqrs submission rejects invalid attachment extension', function () {
    Storage::fake('local');

    $response = $this->from(route('atencion.pqrs'))->post(route('atencion.pqrs.store'), [
        'type' => 'queja',
        'subject' => 'Solicitud con extension invalida',
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
        'subject' => 'Solicitud con archivo grande',
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
