<?php

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config()->set('services.google.client_id', 'google-client-id');
    config()->set('services.google.client_secret', 'google-client-secret');
    config()->set('services.google.redirect', 'https://iedjmh.test/auth/google/callback');
});

test('google redirect route sends users to google oauth screen', function (): void {
    $provider = Mockery::mock();
    $provider
        ->shouldReceive('redirect')
        ->once()
        ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth'));

    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
});

test('google callback rejects accounts with unverified email', function (): void {
    fakeGoogleCallback(makeGoogleUser('admin@iedjmh.test', false));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('google');
    $this->assertGuest();
});

test('google callback rejects accounts that are not registered locally', function (): void {
    fakeGoogleCallback(makeGoogleUser('not-found@iedjmh.test'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('google');
    $this->assertGuest();
});

test('google callback authenticates existing users with an allowed panel role', function (): void {
    $role = Role::findOrCreate('administrador', 'web');

    $user = User::factory()->create([
        'email' => 'admin@iedjmh.test',
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    fakeGoogleCallback(makeGoogleUser('admin@iedjmh.test'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('filament.admin.pages.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('google callback rejects users without admin panel access', function (): void {
    User::factory()->create([
        'email' => 'user@iedjmh.test',
        'is_admin' => true,
    ]);

    fakeGoogleCallback(makeGoogleUser('user@iedjmh.test'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('google');
    $this->assertGuest();
});

test('google callback routes admin users with 2fa enabled through the challenge', function (): void {
    $role = Role::findOrCreate('super_admin', 'web');

    $user = User::factory()->withTwoFactor()->create([
        'email' => 'admin2fa@iedjmh.test',
        'is_admin' => false,
    ]);
    $user->assignRole($role);

    fakeGoogleCallback(makeGoogleUser('admin2fa@iedjmh.test'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->getKey());
    $this->assertGuest();
});

function makeGoogleUser(string $email, bool $emailVerified = true): SocialiteUser
{
    return (new SocialiteUser)
        ->setRaw([
            'sub' => 'google-123',
            'email' => $email,
            'email_verified' => $emailVerified,
        ])
        ->map([
            'id' => 'google-123',
            'name' => 'Google Admin',
            'email' => $email,
        ]);
}

function fakeGoogleCallback(SocialiteUser $socialiteUser): void
{
    $provider = Mockery::mock();
    $provider
        ->shouldReceive('user')
        ->once()
        ->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);
}
