<?php

use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('academic landing page loads and shows navigation cards', function () {
    $this->get(route('academico.index'))
        ->assertOk()
        ->assertSee('Académico')
        ->assertSee('Niveles Educativos')
        ->assertSee('Modalidad')
        ->assertSee('Planes de Área')
        ->assertSee('Sistema de Evaluación')
        ->assertSee('Proyectos Pedagógicos')
        ->assertSee('Calendario Académico');
});

test('academic niveles educativos page loads', function () {
    $this->get(route('academico.niveles-educativos'))
        ->assertOk()
        ->assertSee('Niveles Educativos');
});

test('academic modalidad page loads', function () {
    $this->get(route('academico.modalidad'))
        ->assertOk()
        ->assertSee('Modalidad');
});

test('academic modalidad legacy route returns 404', function () {
    $this->get('/academico/modalidad-agropecuaria')
        ->assertNotFound();
});

test('academic module uses modality label and icon from settings', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Modalidad',
        'academic_modality_label' => 'Modalidad Tecnica',
        'academic_modality_icon' => 'eco',
    ]);

    $this->get(route('academico.index'))
        ->assertOk()
        ->assertSee('Modalidad Tecnica')
        ->assertSee('>eco<', false);

    $this->get(route('academico.niveles-educativos'))
        ->assertOk()
        ->assertSee('Modalidad Tecnica');
});

test('academic planes de area page loads', function () {
    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Planes de Área');
});

test('academic sistema evaluacion page loads', function () {
    $this->get(route('academico.sistema-evaluacion'))
        ->assertOk()
        ->assertSee('Sistema de Evaluación');
});

test('academic proyectos pedagogicos page loads', function () {
    $this->get(route('academico.proyectos-pedagogicos'))
        ->assertOk()
        ->assertSee('Proyectos Pedagógicos');
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
        ->assertSee('Calendario Académico');
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
