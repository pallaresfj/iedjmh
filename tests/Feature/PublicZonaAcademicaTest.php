<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('zona academica route redirects permanently to academico index', function () {
    $this->get(route('academico.zona-academica'))
        ->assertRedirect(route('academico.index'))
        ->assertStatus(301);
});

test('legacy standalone zona academica route remains unavailable', function () {
    $this->get('/zona-academica')->assertNotFound();
});

test('zona academica does not appear in academic landing page cards', function () {
    $this->get(route('academico.index'))
        ->assertOk()
        ->assertDontSee('Zona Academica')
        ->assertDontSee('Zona Académica');
});
