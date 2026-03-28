<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('zona academica page loads and shows fallback content', function () {
    $this->get(route('academico.zona-academica'))
        ->assertOk()
        ->assertSee('Zona Academica')
        ->assertSee('Recursos academicos en linea');
});

test('zona academica shows platform links from settings', function () {
    Setting::query()->create([
        'institution_name' => 'IED Zona Academica',
        'siee' => 'https://siee.example.edu',
        'aula_virtual' => 'https://aula.example.edu',
        'singleton' => 1,
    ]);

    $this->get(route('academico.zona-academica'))
        ->assertOk()
        ->assertSee('Plataformas institucionales')
        ->assertSee('SIEE')
        ->assertSee('href="https://siee.example.edu"', false)
        ->assertSee('Aula Virtual')
        ->assertSee('href="https://aula.example.edu"', false)
        ->assertSee('data-academic-zone-platform', false);
});

test('zona academica hides platform section when settings urls are empty', function () {
    Setting::query()->create([
        'institution_name' => 'IED Sin Plataformas',
        'singleton' => 1,
    ]);

    $this->get(route('academico.zona-academica'))
        ->assertOk()
        ->assertDontSee('Plataformas institucionales')
        ->assertDontSee('data-academic-zone-platform', false);
});

test('zona academica shows categorized documents', function () {
    $category = Category::query()->create([
        'name' => 'Zona Academica',
        'slug' => 'zona-academica',
        'status' => 'published',
    ]);

    $published = Document::query()->create([
        'title' => 'Guia del estudiante 2026',
        'slug' => 'guia-estudiante-2026',
        'summary' => 'Documento orientador para estudiantes.',
        'external_url' => 'https://docs.example.edu/guia',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $published->categories()->attach($category->id);

    $draft = Document::query()->create([
        'title' => 'Documento borrador',
        'slug' => 'documento-borrador',
        'status' => 'draft',
    ]);
    $draft->categories()->attach($category->id);

    $this->get(route('academico.zona-academica'))
        ->assertOk()
        ->assertSee('Recursos descargables')
        ->assertSee('Guia del estudiante 2026')
        ->assertSee('Documento orientador para estudiantes.')
        ->assertSee('href="https://docs.example.edu/guia"', false)
        ->assertSee('data-academic-zone-document', false)
        ->assertDontSee('Documento borrador');
});

test('zona academica shows empty state when no platforms or documents', function () {
    $this->get(route('academico.zona-academica'))
        ->assertOk()
        ->assertSee('Proximamente se publicaran recursos y plataformas academicas en esta seccion.');
});

test('zona academica appears in academic landing page cards', function () {
    $this->get(route('academico.index'))
        ->assertOk()
        ->assertSee('Zona Academica');
});
