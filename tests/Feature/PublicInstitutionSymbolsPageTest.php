<?php

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('symbols page renders symbols content inside shared institutional layout with fallback data', function () {
    $this->get(route('institucion.simbolos'))
        ->assertOk()
        ->assertSee('La Bandera')
        ->assertSee('El Escudo')
        ->assertSee('Escudo no cargado')
        ->assertSee('Himno Institucional')
        ->assertSee('public-symbols-lyrics__content', false)
        ->assertDontSee('Seccion institucional')
        ->assertSee('Landing Institucion')
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('Letra del himno institucional')
        ->assertSee('No hay un archivo de audio del himno cargado')
        ->assertSee('public-banner-full-bleed', false);
});

test('symbols page uses cms settings content and media assets when configured', function () {
    Setting::query()->create([
        'singleton' => 1,
        'symbols_flag_intro' => 'Bandera administrable.',
        'symbols_flag_stripes' => [
            [
                'name' => 'Verde Institucional',
                'description' => 'Compromiso ambiental y agropecuario.',
                'color_hex' => '#2E7D32',
            ],
        ],
        'symbols_shield_intro' => 'Escudo administrable.',
        'symbols_shield_image_path' => 'settings/symbols/escudo.png',
        'symbols_shield_items' => [
            [
                'title' => 'Libro y Ciencia',
                'description' => 'Formacion academica permanente.',
                'icon' => 'menu_book',
            ],
        ],
        'symbols_hymn_title' => 'Himno JMH',
        'symbols_hymn_audio_path' => 'settings/symbols/himno.mp3',
        'symbols_hymn_lyrics' => "Coro desde settings\nlinea dos",
    ]);

    $this->get(route('institucion.simbolos'))
        ->assertOk()
        ->assertSee('Bandera administrable.')
        ->assertSee('Verde Institucional')
        ->assertSee('Escudo administrable.')
        ->assertSee('Libro y Ciencia')
        ->assertSee('menu_book')
        ->assertSee('Himno JMH')
        ->assertSee('public-symbols-lyrics__content', false)
        ->assertSee('Coro desde settings')
        ->assertSee('linea dos')
        ->assertSee('data-symbols-audio-player', false)
        ->assertSee('src="/storage/settings/symbols/himno.mp3"', false)
        ->assertSee('src="/storage/settings/symbols/escudo.png"', false)
        ->assertDontSee('No hay un archivo de audio del himno cargado')
        ->assertDontSee('Escudo no cargado')
        ->assertDontSee('<strong>', false)
        ->assertDontSee('<table>', false);
});

test('symbols page keeps page title and summary as semantic fallback and renders sanitized cms content', function () {
    Page::query()->create([
        'title' => 'Simbolos CMS Personalizados',
        'slug' => 'simbolos-cms-personalizados',
        'menu_binding' => 'institucion.simbolos',
        'summary' => 'Resumen personalizado para simbolos.',
        'status' => 'published',
        'content' => '<h2>Bloque adicional</h2><p>Contenido enriquecido.</p><script>alert(1)</script><p><a href="javascript:alert(1)" onclick="hack()">enlace no seguro</a></p>',
    ]);

    $this->get(route('institucion.simbolos'))
        ->assertOk()
        ->assertSee('Simbolos CMS Personalizados')
        ->assertSee('Resumen personalizado para simbolos.')
        ->assertSee('<h2>Bloque adicional</h2>', false)
        ->assertSee('<p>Contenido enriquecido.</p>', false)
        ->assertDontSee('javascript:alert(1)', false)
        ->assertDontSee('alert(1)', false)
        ->assertDontSee('onclick=', false);
});
