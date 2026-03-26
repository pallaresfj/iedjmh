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

test('public home hero uses directional overlay structure', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('public-home-hero relative', false)
        ->assertSee('public-home-hero__overlay', false)
        ->assertDontSee('bg-gradient-to-r from-slate-950 via-slate-900/45 to-transparent', false);
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
