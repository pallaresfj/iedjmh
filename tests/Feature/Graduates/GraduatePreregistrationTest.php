<?php

use App\Models\Graduate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

function graduatePreregistrationPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Mateo Rivera',
        'email' => 'mateo@example.com',
        'phone' => '3001234567',
        'graduation_year' => 2023,
        'national_id' => '1234567890',
        'current_occupation' => 'Especialista en Sistemas de Riego',
        'city' => 'Pivijay',
        'country' => 'Colombia',
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
        'identity_document' => UploadedFile::fake()->create('identificacion.pdf', 256, 'application/pdf'),
        'data_processing_consent' => '1',
    ], $overrides);
}

test('preregistration activates preloaded graduate and signs in', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'preloaded',
        'password' => null,
        'email' => null,
    ]);

    $response = $this->post(route('egresados.preregister'), graduatePreregistrationPayload());

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('egresados.panel.resumen'));

    $this->assertAuthenticated('graduate');

    $graduate = Graduate::query()->where('national_id', '1234567890')->firstOrFail();
    $identityDocument = $graduate->documents()
        ->where('title', 'Identificación')
        ->where('type_label', 'Personal')
        ->first();

    expect($graduate->status)->toBe('active')
        ->and($graduate->data_processing_consent_at)->not->toBeNull()
        ->and($graduate->password)->not->toBeNull()
        ->and($graduate->activated_at)->not->toBeNull()
        ->and($identityDocument)->not->toBeNull();

    expect($identityDocument->description)->toBe('Documento de identidad del egresado')
        ->and($identityDocument->is_official)->toBeTrue()
        ->and($identityDocument->is_visible)->toBeTrue()
        ->and($identityDocument->drive_url)->toBeNull()
        ->and($identityDocument->file_disk)->toBe('local')
        ->and($identityDocument->file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($identityDocument->file_path);
});

test('preregistration fails when national id is unknown', function () {
    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), graduatePreregistrationPayload([
            'national_id' => '999999999',
        ]));

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('preregistro');
    $this->assertGuest('graduate');
});

test('preregistration fails when graduate is blocked', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'blocked',
    ]);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), graduatePreregistrationPayload());

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('preregistro');
    $this->assertGuest('graduate');
});

test('preregistration requires consent acceptance', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'preloaded',
    ]);

    $payload = graduatePreregistrationPayload();
    unset($payload['data_processing_consent']);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), $payload);

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('data_processing_consent');
    $this->assertGuest('graduate');
});

test('preregistration requires identity document file', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'preloaded',
    ]);

    $payload = graduatePreregistrationPayload();
    unset($payload['identity_document']);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), $payload);

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('identity_document');
    $this->assertGuest('graduate');
});

test('preregistration rejects unsupported identity document file type', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'preloaded',
    ]);

    $payload = graduatePreregistrationPayload([
        'identity_document' => UploadedFile::fake()->create('identificacion.txt', 256, 'text/plain'),
    ]);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), $payload);

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('identity_document');
    $this->assertGuest('graduate');
});

test('preregistration rejects identity document larger than one megabyte', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'preloaded',
    ]);

    $payload = graduatePreregistrationPayload([
        'identity_document' => UploadedFile::fake()->create('identificacion.pdf', 1536, 'application/pdf'),
    ]);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.preregister'), $payload);

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('identity_document');
    $this->assertGuest('graduate');
});
