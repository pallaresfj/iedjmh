<?php

test('public home renders key institutional sections', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Muro de Actualidad')
        ->assertSee('Granja Experimental')
        ->assertSee('Proximos Eventos');
});

test('public home renders global public layout elements', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('GOV.CO')
        ->assertSee('Matriculas')
        ->assertSee('Explorar');
});
