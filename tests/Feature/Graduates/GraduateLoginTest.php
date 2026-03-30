<?php

use App\Models\Graduate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('graduate can login with national id and password', function () {
    $graduate = Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'active',
        'password' => Hash::make('secret12345'),
        'last_login_at' => null,
    ]);

    $response = $this->post(route('egresados.login'), [
        'national_id' => '1234567890',
        'password' => 'secret12345',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('egresados.panel.resumen'));

    $this->assertAuthenticated('graduate');

    $graduate->refresh();
    expect($graduate->last_login_at)->not->toBeNull();
});

test('graduate login fails with invalid credentials', function () {
    Graduate::factory()->create([
        'national_id' => '1234567890',
        'status' => 'active',
        'password' => Hash::make('secret12345'),
    ]);

    $response = $this->from(route('egresados.index'))
        ->post(route('egresados.login'), [
            'national_id' => '1234567890',
            'password' => 'invalid-password',
        ]);

    $response->assertRedirect(route('egresados.index'));
    $response->assertSessionHasErrors('login');
    $this->assertGuest('graduate');
});

