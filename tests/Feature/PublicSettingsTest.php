<?php

use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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
        'allies' => [
            ['name' => 'Aliado desde settings', 'url' => 'https://aliado-settings.example.edu'],
        ],
        'singleton' => 1,
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('IED Prueba Institucional')
        ->assertSee('Pivijay Centro, Magdalena')
        ->assertSee('src="/storage/settings/logo-institucional.svg"', false)
        ->assertSee('alt="Logo institucional footer"', false)
        ->assertSee('public-footer__logo', false)
        ->assertSee('rel="icon" href="/storage/settings/logo-institucional.svg"', false)
        ->assertSee('href="https://siee.example.edu"', false)
        ->assertSee('href="https://aula.example.edu"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('Aliado desde settings')
        ->assertSee('href="https://aliado-settings.example.edu"', false)
        ->assertSee('href="https://asyservicios.com"', false)
        ->assertSee('AS&amp;Servicios.com', false)
        ->assertDontSee('Formando lideres para el agro y la vida.', false)
        ->assertDontSee('Sitio institucional oficial.', false);

    expect($response->getContent())
        ->toMatch('/&copy;\s*'.now()->year.'\s+IED Prueba Institucional\s*-\s*Desarrollado por/s');

    expect($response->getContent())
        ->toMatch('/href="https:\/\/aliado-settings\.example\.edu"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/');
});

test('contact page uses contact data from settings', function () {
    config()->set('institution.address', 'Direccion desde config');
    config()->set('institution.phone', '3000000000');
    config()->set('institution.email', 'config@iedjmh.edu.co');
    config()->set('institution.city', 'Ciudad config');
    config()->set('institution.department', 'Departamento config');

    Setting::query()->create([
        'institution_name' => 'IED Contacto desde settings',
        'address' => 'Carrera 5 # 12-34, Barrio Centro',
        'phone' => '+57 300 111 2233',
        'email' => 'contacto@iedjmh.edu.co',
        'location' => 'Pivijay Centro, Magdalena',
        'singleton' => 1,
    ]);

    $this->get(route('atencion.contactenos'))
        ->assertOk()
        ->assertSee('Carrera 5 # 12-34, Barrio Centro')
        ->assertSee('+57 300 111 2233')
        ->assertSee('contacto@iedjmh.edu.co')
        ->assertSee('Pivijay Centro, Magdalena')
        ->assertDontSee('Direccion desde config')
        ->assertDontSee('3000000000')
        ->assertDontSee('config@iedjmh.edu.co');
});

test('contact page falls back to config values when contact settings are empty', function () {
    config()->set('institution.address', 'Direccion fallback config');
    config()->set('institution.phone', '3119998877');
    config()->set('institution.email', 'fallback@iedjmh.edu.co');
    config()->set('institution.city', 'Ciudad fallback');
    config()->set('institution.department', 'Departamento fallback');

    Setting::query()->create([
        'institution_name' => 'IED Contacto fallback',
        'address' => '',
        'phone' => null,
        'email' => ' ',
        'location' => null,
        'singleton' => 1,
    ]);

    $this->get(route('atencion.contactenos'))
        ->assertOk()
        ->assertSee('Direccion fallback config')
        ->assertSee('3119998877')
        ->assertSee('fallback@iedjmh.edu.co')
        ->assertSee('Ciudad fallback, Departamento fallback');
});

test('topbar reads email phone and location from settings contact data', function () {
    config()->set('institution.email', 'legacy-topbar@iedjmh.edu.co');
    config()->set('institution.phone', '3000000011');
    config()->set('institution.city', 'Ciudad legacy');
    config()->set('institution.department', 'Departamento legacy');

    Setting::query()->create([
        'institution_name' => 'IED Topbar desde settings',
        'email' => 'topbar@iedjmh.edu.co',
        'phone' => '+57 300 777 8899',
        'location' => 'Ubicacion topbar settings',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('topbar@iedjmh.edu.co')
        ->assertSee('+57 300 777 8899')
        ->assertSee('Ubicacion topbar settings')
        ->assertDontSee('legacy-topbar@iedjmh.edu.co')
        ->assertDontSee('3000000011')
        ->assertDontSee('Ciudad legacy, Departamento legacy');
});

test('institution campuses fallback uses settings contact data', function () {
    config()->set('institution.address', 'Direccion legacy sedes');
    config()->set('institution.phone', '3004445566');
    config()->set('institution.email', 'legacy-sedes@iedjmh.edu.co');

    Setting::query()->create([
        'institution_name' => 'IED Sedes desde settings',
        'address' => 'Direccion principal desde settings',
        'phone' => '+57 300 444 5566',
        'email' => 'sedes@iedjmh.edu.co',
        'singleton' => 1,
    ]);

    $this->get(route('institucion.sedes'))
        ->assertOk()
        ->assertSee('Sede Principal')
        ->assertSee('Direccion principal desde settings')
        ->assertSee('+57 300 444 5566')
        ->assertSee('sedes@iedjmh.edu.co')
        ->assertDontSee('Direccion legacy sedes')
        ->assertDontSee('3004445566')
        ->assertDontSee('legacy-sedes@iedjmh.edu.co');
});

test('footer allies fallback to config when settings allies are empty', function () {
    config()->set('institution.allies', [
        ['label' => 'Aliado fallback de config', 'url' => 'https://ally-fallback.example.edu'],
    ]);

    Setting::query()->create([
        'institution_name' => 'IED Sin Aliados En Settings',
        'allies' => [],
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Aliado fallback de config')
        ->assertSee('href="https://ally-fallback.example.edu"', false);
});

test('home hero uses relative storage url when settings image exists on public disk', function () {
    Storage::disk('public')->put('settings/home/hero-test.jpg', 'fake-image-content');

    Setting::query()->create([
        'institution_name' => 'IED Hero Imagen Local',
        'home_hero_image_path' => 'settings/home/hero-test.jpg',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('src="/storage/settings/home/hero-test.jpg"', false)
        ->assertDontSee('src="http://iedjmh.test/storage/settings/home/hero-test.jpg"', false);
});

test('home header hides external platform links when urls are empty', function () {
    Setting::query()->create([
        'institution_name' => 'IED Sin Plataformas',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('>SIEE<', false)
        ->assertDontSee('>Aula Virtual<', false)
        ->assertSee('public-footer__icon-fallback', false);
});

test('setting singleton helper always returns a single record', function () {
    $first = Setting::singleton();
    $second = Setting::singleton();

    expect($first->is($second))->toBeTrue()
        ->and(Setting::query()->count())->toBe(1);
});

test('public layout injects theme colors from settings', function () {
    Setting::query()->create([
        'institution_name' => 'IED Tema Dinamico',
        'theme_primary' => '#123ABC',
        'theme_primary_dark' => '#102030',
        'theme_primary_light' => '#DDEEFF',
        'theme_accent' => '#FF9900',
        'theme_gray_900' => '#1F2937',
        'theme_gray_700' => '#374151',
        'theme_gray_600' => '#4B5563',
        'theme_gray_200' => '#E5E7EB',
        'theme_gray_100' => '#F3F4F6',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('--color-ied-primary: #123ABC;', false)
        ->assertSee('--color-ied-primary-rgb: 18, 58, 188;', false)
        ->assertSee('--color-ied-primary-dark-rgb: 16, 32, 48;', false)
        ->assertSee('--color-ied-primary-light-rgb: 221, 238, 255;', false)
        ->assertSee('--color-ied-gray-100: #F3F4F6;', false);
});

test('invalid theme color value falls back to safe default', function () {
    Setting::query()->create([
        'institution_name' => 'IED Color Invalido',
        'theme_primary' => 'verde-invalido',
        'singleton' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('--color-ied-primary: #2E7D32;', false)
        ->assertSee('--color-ied-primary-rgb: 46, 125, 50;', false);
});

test('home hero prioritizes settings content over home banner', function () {
    Setting::query()->create([
        'institution_name' => 'IED Hero Settings',
        'home_hero_eyebrow' => 'Hero desde settings',
        'home_hero_title' => 'Titulo hero configurado',
        'home_hero_description' => 'Descripcion del hero configurable.',
        'home_hero_cta_label' => 'Ir al proceso',
        'home_hero_cta_url' => 'https://settings-hero.example.edu',
        'home_hero_cta_target' => '_blank',
        'home_hero_image_path' => 'settings/home/hero-settings.jpg',
        'singleton' => 1,
    ]);

    Banner::query()->create([
        'title' => 'Titulo hero desde banner',
        'slug' => 'hero-banner-home',
        'subtitle' => 'Subtitulo banner',
        'description' => 'Descripcion banner',
        'cta_label' => 'CTA Banner',
        'cta_url' => 'https://banner-hero.example.edu',
        'target' => '_self',
        'status' => 'published',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Hero desde settings')
        ->assertSee('Titulo hero configurado')
        ->assertSee('Descripcion del hero configurable.')
        ->assertSee('href="https://settings-hero.example.edu"', false)
        ->assertSee('src="/storage/settings/home/hero-settings.jpg"', false)
        ->assertDontSee('Titulo hero desde banner');

    expect($response->getContent())
        ->toMatch('/href="https:\/\/settings-hero\.example\.edu"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/');
});

test('home hero falls back to home banner when settings are empty', function () {
    Setting::query()->create([
        'institution_name' => 'IED Hero Banner',
        'singleton' => 1,
    ]);

    Banner::query()->create([
        'title' => 'Hero principal desde banner',
        'slug' => 'hero-principal-banner',
        'subtitle' => 'Subtitulo banner home',
        'description' => 'Contenido principal desde banner vigente.',
        'cta_label' => 'Conocer mas',
        'cta_url' => 'https://banner-home.example.edu',
        'target' => '_blank',
        'status' => 'published',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Subtitulo banner home')
        ->assertSee('Hero principal desde banner')
        ->assertSee('Contenido principal desde banner vigente.')
        ->assertSee('href="https://banner-home.example.edu"', false);

    expect($response->getContent())
        ->toMatch('/href="https:\/\/banner-home\.example\.edu"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/');
});

test('home hero falls back to built in defaults when no settings and no banners exist', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Formando lideres para el agro y la vida')
        ->assertSee('Conoce nuestra matricula 2026');
});
