<?php

use App\Models\Category;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transparency landing page loads', function () {
    $this->get(route('transparencia.index'))
        ->assertOk()
        ->assertSee('Transparencia');
});

test('transparency landing shows recent published documents', function () {
    Document::query()->create([
        'title' => 'Informe de gestion 2025',
        'slug' => 'informe-gestion-2025',
        'summary' => 'Resumen del informe anual.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Document::query()->create([
        'title' => 'Documento borrador',
        'slug' => 'documento-borrador',
        'status' => 'draft',
    ]);

    $this->get(route('transparencia.index'))
        ->assertOk()
        ->assertSee('Informe de gestion 2025')
        ->assertDontSee('Documento borrador');
});

test('transparency documents page lists published documents with pagination', function () {
    for ($i = 1; $i <= 12; $i++) {
        Document::query()->create([
            'title' => "Documento publico {$i}",
            'slug' => "documento-publico-{$i}",
            'status' => 'published',
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('transparencia.documentos'));

    $response->assertOk()
        ->assertSee('Documento publico 1');
});

test('transparency documents filters by search', function () {
    Document::query()->create([
        'title' => 'Presupuesto 2026',
        'slug' => 'presupuesto-2026',
        'summary' => 'Proyeccion financiera anual.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Document::query()->create([
        'title' => 'Manual de funciones',
        'slug' => 'manual-funciones',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.documentos', ['q' => 'Presupuesto']))
        ->assertOk()
        ->assertSee('Presupuesto 2026')
        ->assertDontSee('Manual de funciones');
});

test('transparency documents filters by category', function () {
    $category = Category::query()->create([
        'name' => 'Financiero',
        'slug' => 'financiero',
        'status' => 'published',
    ]);

    $categorized = Document::query()->create([
        'title' => 'Balance financiero',
        'slug' => 'balance-financiero',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $categorized->categories()->attach($category->id);

    $uncategorized = Document::query()->create([
        'title' => 'Otro documento',
        'slug' => 'otro-documento',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.documentos', ['category' => 'financiero']))
        ->assertOk()
        ->assertSee('Balance financiero')
        ->assertDontSee('Otro documento');
});

test('transparency document detail shows published document', function () {
    $document = Document::query()->create([
        'title' => 'Rendicion de cuentas',
        'slug' => 'rendicion-cuentas',
        'summary' => 'Informe de rendicion.',
        'description' => '<p>Contenido completo de la rendicion de cuentas.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('transparencia.documento', ['slug' => $document->slug]))
        ->assertOk()
        ->assertSee('Rendicion de cuentas')
        ->assertSee('Contenido completo de la rendicion de cuentas.');
});

test('transparency document detail returns 404 for draft', function () {
    Document::query()->create([
        'title' => 'Borrador secreto',
        'slug' => 'borrador-secreto',
        'status' => 'draft',
    ]);

    $this->get(route('transparencia.documento', ['slug' => 'borrador-secreto']))
        ->assertNotFound();
});
