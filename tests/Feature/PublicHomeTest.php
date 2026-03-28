<?php

test('public home renders key institutional sections', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Muro de Actualidad')
        ->assertSee('Granja Experimental')
        ->assertSee('Próximos Eventos')
        ->assertSee('08:00 AM - 04:00 PM')
        ->assertSee('Sede Principal - Granja');
});

test('public home renders global public layout elements', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('GOV.CO')
        ->assertSee('Matriculas')
        ->assertSee('Explorar');
});

test('public home hero renders full width structure with overlay layers', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('public-home-hero public-banner-full-bleed', false)
        ->assertSee('public-home-hero__media', false)
        ->assertSee('public-home-hero__overlay', false)
        ->assertSee('public-home-hero__content', false)
        ->assertSee('public-home-hero__cta', false);
});

test('public home hero renders fallback headline and cta copy', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Formando lideres para el agro y la vida')
        ->assertSee('Conoce nuestra matricula 2026');
});

test('public home renders theme marker and toggle controls', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-home-page="1"', false)
        ->assertSee('data-public-theme="', false)
        ->assertSee('data-public-theme-toggle', false);
});

test('non-home public pages render global theme marker and toggle', function () {
    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('data-home-page="0"', false)
        ->assertSee('data-public-theme="', false)
        ->assertSee('data-public-theme-toggle', false);
});
