<?php

use App\Models\Category;
use App\Models\Faq;
use App\Models\Procedure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('citizen attention landing page loads', function () {
    $this->get(route('atencion.index'))
        ->assertOk()
        ->assertSee('Atencion al Ciudadano');
});

test('citizen attention landing shows featured faqs', function () {
    Faq::query()->create([
        'question' => 'Como me matriculo?',
        'slug' => 'como-me-matriculo',
        'answer' => 'Acercandose a la sede principal.',
        'status' => 'published',
        'sort_order' => 1,
        'published_at' => now(),
    ]);

    Faq::query()->create([
        'question' => 'FAQ borrador',
        'slug' => 'faq-borrador',
        'answer' => 'No visible.',
        'status' => 'draft',
    ]);

    $this->get(route('atencion.index'))
        ->assertOk()
        ->assertSee('Como me matriculo?')
        ->assertDontSee('FAQ borrador');
});

test('faq page lists published faqs', function () {
    Faq::query()->create([
        'question' => 'Cual es el horario?',
        'slug' => 'cual-es-el-horario',
        'answer' => 'Lunes a viernes de 7am a 2pm.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('atencion.faq'))
        ->assertOk()
        ->assertSee('Cual es el horario?')
        ->assertSee('Lunes a viernes de 7am a 2pm.');
});

test('faq page filters by search', function () {
    Faq::query()->create([
        'question' => 'Donde queda la sede?',
        'slug' => 'donde-queda-la-sede',
        'answer' => 'En Pivijay, Magdalena.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Faq::query()->create([
        'question' => 'Que uniformes necesito?',
        'slug' => 'que-uniformes-necesito',
        'answer' => 'Uniforme de diario y deportivo.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('atencion.faq', ['q' => 'sede']))
        ->assertOk()
        ->assertSee('Donde queda la sede?')
        ->assertDontSee('Que uniformes necesito?');
});

test('faq page filters by category', function () {
    $category = Category::query()->create([
        'name' => 'Matriculas',
        'slug' => 'matriculas',
        'status' => 'published',
    ]);

    Faq::query()->create([
        'question' => 'Requisitos de matricula?',
        'slug' => 'requisitos-de-matricula',
        'answer' => 'Documento de identidad y certificados.',
        'status' => 'published',
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    Faq::query()->create([
        'question' => 'Horario de biblioteca?',
        'slug' => 'horario-de-biblioteca',
        'answer' => '8am a 12pm.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('atencion.faq', ['category' => 'matriculas']))
        ->assertOk()
        ->assertSee('Requisitos de matricula?')
        ->assertDontSee('Horario de biblioteca?');
});

test('procedures page lists published procedures', function () {
    Procedure::query()->create([
        'name' => 'Matricula ordinaria',
        'slug' => 'matricula-ordinaria',
        'summary' => 'Proceso de matricula para estudiantes nuevos.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Procedure::query()->create([
        'name' => 'Tramite borrador',
        'slug' => 'tramite-borrador',
        'status' => 'draft',
    ]);

    $this->get(route('atencion.tramites'))
        ->assertOk()
        ->assertSee('Matricula ordinaria')
        ->assertDontSee('Tramite borrador');
});

test('procedures page filters by search', function () {
    Procedure::query()->create([
        'name' => 'Certificado de estudio',
        'slug' => 'certificado-estudio',
        'summary' => 'Solicitud de certificado.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Procedure::query()->create([
        'name' => 'Constancia de notas',
        'slug' => 'constancia-notas',
        'summary' => 'Expedicion de constancias.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('atencion.tramites', ['q' => 'certificado']))
        ->assertOk()
        ->assertSee('Certificado de estudio')
        ->assertDontSee('Constancia de notas');
});

test('sitemap page loads', function () {
    $this->get(route('atencion.mapa-sitio'))
        ->assertOk()
        ->assertSee('Mapa del sitio');
});

test('participation page loads', function () {
    $this->get(route('atencion.participacion'))
        ->assertOk();
});

test('pqrs form page loads', function () {
    $this->get(route('atencion.pqrs'))
        ->assertOk()
        ->assertSee('PQRS');
});

test('pqrs tracking page loads', function () {
    $this->get(route('atencion.pqrs.track'))
        ->assertOk();
});
