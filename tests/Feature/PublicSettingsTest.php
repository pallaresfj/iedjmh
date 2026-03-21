<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('legacy academic zone routes return 404', function () {
    $this->get('/academico/zona-academica')->assertNotFound();
    $this->get('/zona-academica')->assertNotFound();
});

test('home header uses institution settings and renders external platform links', function () {
    Setting::query()->create([
        'institution_name' => 'IED Prueba Institucional',
        'dane' => '123456789',
        'nit' => '900123456-7',
        'location' => 'Pivijay Centro, Magdalena',
        'siee' => 'https://siee.example.edu',
        'aula_virtual' => 'https://aula.example.edu',
        'logo_path' => 'settings/logo-institucional.svg',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('IED Prueba Institucional')
        ->assertSee('Pivijay Centro, Magdalena')
        ->assertSee('src="/storage/settings/logo-institucional.svg"', false)
        ->assertSee('rel="icon" href="/storage/settings/logo-institucional.svg"', false)
        ->assertSee('href="https://siee.example.edu"', false)
        ->assertSee('href="https://aula.example.edu"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false);
});

test('home header hides external platform links when urls are empty', function () {
    Setting::query()->create([
        'institution_name' => 'IED Sin Plataformas',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('>SIEE<', false)
        ->assertDontSee('>Aula Virtual<', false);
});

test('setting singleton helper always returns a single record', function () {
    $first = Setting::singleton();
    $second = Setting::singleton();

    expect($first->is($second))->toBeTrue()
        ->and(Setting::query()->count())->toBe(1);
});
