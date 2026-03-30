<?php

use App\Models\Campus;
use App\Models\MatriculaRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('public matricula page renders designed sections', function () {
    Campus::factory()->create();

    $this->get(route('matricula.index'))
        ->assertOk()
        ->assertSee('Requisitos de Matricula')
        ->assertSee('Formulario de Inscripcion')
        ->assertSee('Fotocopia de Registro Civil y Tarjeta de Identidad.')
        ->assertSee('Documentos Acudiente')
        ->assertSee('Fotocopia de documento de identidad de los padres y acudiente. Recibo de servicio público del lugar de residencia.')
        ->assertSee('Toda la documentación debe ser entregada en una carpeta de cartón oficio o cargada digitalmente en el formulario de esta página.');
});

test('public matricula submission stores multiple attachments metadata', function () {
    Storage::fake('local');
    $campus = Campus::factory()->create();

    $response = $this->post(route('matricula.store'), [
        'student_name' => 'Laura Pardo',
        'document_number' => '1030120300',
        'phone' => '3001234567',
        'grade' => 'primero',
        'campus_id' => $campus->id,
        'attachments' => [
            UploadedFile::fake()->create('registro.pdf', 600, 'application/pdf'),
            UploadedFile::fake()->image('foto.jpg'),
        ],
    ]);

    $response
        ->assertStatus(302);

    $request = MatriculaRequest::query()->firstOrFail();

    expect($request->student_name)->toBe('Laura Pardo')
        ->and($request->phone)->toBe('3001234567')
        ->and($request->status)->toBe('pending')
        ->and($request->submitted_at)->not->toBeNull()
        ->and($request->attachments)->toHaveCount(2);

    collect($request->attachments)->each(function (array $attachment): void {
        expect($attachment)->toHaveKeys(['path', 'original_name', 'mime', 'size']);
        Storage::disk('local')->assertExists($attachment['path']);
    });
});

test('public matricula submission accepts pdf with fallback mime when extension is valid', function () {
    Storage::fake('local');
    $campus = Campus::factory()->create();

    $response = $this->post(route('matricula.store'), [
        'student_name' => 'Daniel Rueda',
        'document_number' => '90012345',
        'phone' => '3004567890',
        'grade' => 'tercero',
        'campus_id' => $campus->id,
        'attachments' => [
            UploadedFile::fake()->create('soporte.pdf', 700, 'application/octet-stream'),
        ],
    ]);

    $response->assertStatus(302);

    $request = MatriculaRequest::query()->where('student_name', 'Daniel Rueda')->firstOrFail();

    expect($request->attachments)->toHaveCount(1);
});

test('public matricula submission validates required fields', function () {
    $response = $this->from(route('matricula.index'))->post(route('matricula.store'), []);

    $response
        ->assertRedirect(route('matricula.index'))
        ->assertSessionHasErrors(['student_name', 'document_number', 'phone', 'grade', 'campus_id']);
});

test('public matricula submission rejects invalid attachment extension', function () {
    $campus = Campus::factory()->create();

    $response = $this->from(route('matricula.index'))->post(route('matricula.store'), [
        'student_name' => 'Camilo Ruiz',
        'document_number' => '1234567890',
        'phone' => '3000000000',
        'grade' => 'sexto',
        'campus_id' => $campus->id,
        'attachments' => [
            UploadedFile::fake()->create('evidencia.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ],
    ]);

    $response
        ->assertRedirect(route('matricula.index'))
        ->assertSessionHasErrors('attachments.0');
});

test('public matricula submission rejects files larger than 1mb', function () {
    $campus = Campus::factory()->create();

    $response = $this->from(route('matricula.index'))->post(route('matricula.store'), [
        'student_name' => 'Paola Cardenas',
        'document_number' => '88888888',
        'phone' => '3010000000',
        'grade' => 'undecimo',
        'campus_id' => $campus->id,
        'attachments' => [
            UploadedFile::fake()->create('documento.pdf', 2000, 'application/pdf'),
        ],
    ]);

    $response
        ->assertRedirect(route('matricula.index'))
        ->assertSessionHasErrors('attachments.0');
});

test('public cta links for matricula route are present on home', function () {
    Campus::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('matricula.index'), false)
        ->assertSee('Conoce nuestra matricula 2026');
});
