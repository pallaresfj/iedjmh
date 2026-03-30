<?php

use App\Models\Graduate;
use App\Notifications\GraduateResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('graduate can request password reset link by email', function () {
    $graduate = Graduate::factory()->create([
        'status' => 'active',
        'email' => 'grad@example.com',
    ]);

    Notification::fake();

    $response = $this->post(route('egresados.password.email'), [
        'email' => 'grad@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status');

    Notification::assertSentTo($graduate, GraduateResetPasswordNotification::class);
});

test('graduate can reset password using valid token', function () {
    $graduate = Graduate::factory()->create([
        'status' => 'active',
        'email' => 'grad@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::broker('graduates')->createToken($graduate);

    $response = $this->post(route('egresados.password.reset.update'), [
        'token' => $token,
        'email' => 'grad@example.com',
        'password' => 'new-secret-123',
        'password_confirmation' => 'new-secret-123',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('egresados.index'));

    $graduate->refresh();
    expect(Hash::check('new-secret-123', (string) $graduate->password))->toBeTrue();
});

