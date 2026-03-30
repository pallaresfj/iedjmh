<?php

use App\Models\Graduate;
use App\Models\GraduateDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected when accessing graduate panel', function () {
    $response = $this->get(route('egresados.panel.resumen'));

    $response->assertRedirect(route('egresados.index'));
});

test('authenticated graduate can access all panel sections', function () {
    $graduate = Graduate::factory()->create([
        'status' => 'active',
    ]);

    $this->actingAs($graduate, 'graduate');

    $this->get(route('egresados.panel.resumen'))->assertOk();
    $this->get(route('egresados.panel.certificados'))->assertOk();
    $this->get(route('egresados.panel.registro-academico'))->assertOk();
    $this->get(route('egresados.panel.configuracion'))->assertOk();
});

test('graduate only sees own visible documents on certificates page', function () {
    $graduate = Graduate::factory()->create(['status' => 'active']);
    $otherGraduate = Graduate::factory()->create(['status' => 'active']);

    GraduateDocument::factory()->create([
        'graduate_id' => $graduate->id,
        'title' => 'Acta propia visible',
        'is_visible' => true,
    ]);

    GraduateDocument::factory()->create([
        'graduate_id' => $graduate->id,
        'title' => 'Acta propia oculta',
        'is_visible' => false,
    ]);

    GraduateDocument::factory()->create([
        'graduate_id' => $otherGraduate->id,
        'title' => 'Acta ajena visible',
        'is_visible' => true,
    ]);

    $this->actingAs($graduate, 'graduate');

    $response = $this->get(route('egresados.panel.certificados'));

    $response->assertOk();
    $response->assertSee('Acta propia visible');
    $response->assertDontSee('Acta propia oculta');
    $response->assertDontSee('Acta ajena visible');
});

