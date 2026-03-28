<?php

use App\Models\Page;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('academic landing page loads and shows navigation cards', function () {
    $this->get(route('academico.index'))
        ->assertOk()
        ->assertSee('Academico')
        ->assertSee('Niveles Educativos')
        ->assertSee('Modalidad Agropecuaria')
        ->assertSee('Planes de Area')
        ->assertSee('Sistema de Evaluacion')
        ->assertSee('Proyectos Pedagogicos')
        ->assertSee('Calendario Academico')
        ->assertSee('Zona Academica');
});

test('academic niveles educativos page loads', function () {
    $this->get(route('academico.niveles-educativos'))
        ->assertOk()
        ->assertSee('Niveles Educativos');
});

test('academic modalidad agropecuaria page loads', function () {
    $this->get(route('academico.modalidad-agropecuaria'))
        ->assertOk()
        ->assertSee('Modalidad Agropecuaria');
});

test('academic planes de area page loads', function () {
    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Planes de Area');
});

test('academic sistema evaluacion page loads', function () {
    $this->get(route('academico.sistema-evaluacion'))
        ->assertOk()
        ->assertSee('Sistema de Evaluacion');
});

test('academic proyectos pedagogicos page loads', function () {
    $this->get(route('academico.proyectos-pedagogicos'))
        ->assertOk()
        ->assertSee('Proyectos Pedagogicos');
});

test('academic proyectos pedagogicos shows published projects', function () {
    Project::query()->create([
        'title' => 'Semillero de ciencias',
        'slug' => 'semillero-ciencias',
        'summary' => 'Proyecto para fomentar investigacion.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('academico.proyectos-pedagogicos'))
        ->assertOk()
        ->assertSee('Semillero de ciencias');
});

test('academic calendario academico page loads', function () {
    $this->get(route('academico.calendario-academico'))
        ->assertOk()
        ->assertSee('Calendario Academico');
});

test('academic page uses cms content when available', function () {
    Page::query()->create([
        'title' => 'Niveles Educativos - Actualizado',
        'slug' => 'academico-niveles-educativos',
        'content' => '<p>Contenido actualizado desde el CMS.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('academico.niveles-educativos'))
        ->assertOk()
        ->assertSee('Niveles Educativos - Actualizado')
        ->assertSee('Contenido actualizado desde el CMS.');
});

test('academic page returns 404 for unknown page key', function () {
    $this->get('/academico/pagina-inexistente')
        ->assertNotFound();
});
