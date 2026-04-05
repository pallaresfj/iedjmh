<?php

use App\Models\Graduate;
use App\Models\GraduateDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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
    $this->get(route('egresados.panel.documentos'))->assertOk();
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

    $response = $this->get(route('egresados.panel.documentos'));

    $response->assertOk();
    $response->assertSee('Acta propia visible');
    $response->assertDontSee('Acta propia oculta');
    $response->assertDontSee('Acta ajena visible');
});

test('legacy certificates route redirects to documents route', function () {
    $graduate = Graduate::factory()->create(['status' => 'active']);

    $this->actingAs($graduate, 'graduate');

    $this->get(route('egresados.panel.certificados'))
        ->assertRedirect(route('egresados.panel.documentos'));
});

test('graduate panel exposes authenticated route for local file documents', function () {
    Storage::fake('local');

    $graduate = Graduate::factory()->create(['status' => 'active']);
    Storage::disk('local')->put('graduates/'.$graduate->id.'/identity-documents/identificacion.pdf', 'contenido');

    $document = GraduateDocument::factory()->create([
        'graduate_id' => $graduate->id,
        'title' => 'Identificacion',
        'drive_url' => null,
        'file_path' => 'graduates/'.$graduate->id.'/identity-documents/identificacion.pdf',
        'file_disk' => 'local',
        'is_visible' => true,
    ]);

    $this->actingAs($graduate, 'graduate');

    $this->get(route('egresados.panel.documentos'))
        ->assertOk()
        ->assertSee(route('egresados.panel.documentos.archivo', ['document' => $document]), false);

    $this->get(route('egresados.panel.documentos.archivo', ['document' => $document]))
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('graduate cannot access file document from another graduate', function () {
    Storage::fake('local');

    $graduate = Graduate::factory()->create(['status' => 'active']);
    $otherGraduate = Graduate::factory()->create(['status' => 'active']);
    Storage::disk('local')->put('graduates/'.$otherGraduate->id.'/identity-documents/identificacion.pdf', 'contenido');

    $document = GraduateDocument::factory()->create([
        'graduate_id' => $otherGraduate->id,
        'drive_url' => null,
        'file_path' => 'graduates/'.$otherGraduate->id.'/identity-documents/identificacion.pdf',
        'file_disk' => 'local',
        'is_visible' => true,
    ]);

    $this->actingAs($graduate, 'graduate');

    $this->get(route('egresados.panel.documentos.archivo', ['document' => $document]))
        ->assertNotFound();
});
