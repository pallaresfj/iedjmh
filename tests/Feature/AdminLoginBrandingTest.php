<?php

use App\Models\Setting;
use App\Support\PublicSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin login renders institutional branding from settings', function () {
    Storage::disk('public')->put('settings/logo-admin.png', 'fake-logo-content');
    Storage::disk('public')->put('settings/home/hero-admin.jpg', 'fake-hero-content');

    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Branding Admin',
        'logo_path' => 'settings/logo-admin.png',
        'home_hero_image_path' => 'settings/home/hero-admin.jpg',
    ]);

    $response = $this->get('/admin/login');

    $response
        ->assertOk()
        ->assertSee('IED Branding Admin')
        ->assertSee('Acceso al Portal')
        ->assertSee('Ingresa con tu correo institucional')
        ->assertDontSee('fi-simple-header', false)
        ->assertSee('src="/storage/settings/logo-admin.png"', false)
        ->assertSee('/storage/settings/home/hero-admin.jpg', false)
        ->assertSee('AS&amp;Servicios.com', false)
        ->assertSee('href="https://asyservicios.com"', false);

    expect($response->getContent())
        ->toMatch('/&copy;\s*'.now()->year.'\s+IED Branding Admin\s*-\s*Desarrollado por/s')
        ->toMatch('/id="form\.password"[^>]*type="password"/');
});

test('admin login uses shared home hero fallback when hero image setting is empty', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Fallback Login',
        'logo_path' => null,
        'home_hero_image_path' => null,
    ]);

    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('IED Fallback Login')
        ->assertSee(PublicSettings::homeHeroFallbackImageUrl(), false);
});

test('admin login renders google separator and branded google button when oauth is configured', function () {
    config()->set('services.google.client_id', 'google-client-id');
    config()->set('services.google.client_secret', 'google-client-secret');
    config()->set('services.google.redirect', 'https://iedjmh.test/auth/google/callback');

    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Login Google',
    ]);

    $this->get('/admin/login')
        ->assertOk()
        ->assertDontSee('fi-simple-header', false)
        ->assertSee('agro-login-separator', false)
        ->assertSee('O CONTINUAR CON')
        ->assertSee('agro-google-btn', false)
        ->assertSee('agro-google-logo', false)
        ->assertSee('Continuar con Google');
});
