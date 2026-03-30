<?php

use App\Models\Graduate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

    expect($graduate->status)->toBe('active')
        ->and($graduate->data_processing_consent_at)->not->toBeNull()
        ->and($graduate->password)->not->toBeNull()
        ->and($graduate->activated_at)->not->toBeNull();
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

