<?php

use App\Models\Campus;
use App\Models\Document;
use App\Models\Page;
use App\Models\Setting;
use App\Models\StaffMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('institution landing page loads', function () {
    $this->get(route('institucion.index'))
        ->assertOk()
        ->assertSee('Institucion');
});

test('institution landing shows navigation cards for all sub-pages', function () {
    $this->get(route('institucion.index'))
        ->assertOk()
        ->assertSee('Historia')
        ->assertSee('Misión y Visión')
        ->assertSee('Símbolos')
        ->assertSee('Equipo Institucional')
        ->assertSee('Sedes')
        ->assertSee('PEI')
        ->assertSee('Manual de Convivencia')
        ->assertDontSee('Directorio');
});

test('institution history page loads with fallback content', function () {
    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Historia');
});

test('institution history page uses cms content when available', function () {
    Page::query()->create([
        'title' => 'Nuestra Historia Institucional',
        'slug' => 'institucion-historia',
        'summary' => 'Resumen de la historia.',
        'content' => '<p>La institucion fue fundada en 1965.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Nuestra Historia Institucional')
        ->assertSee('La institucion fue fundada en 1965.');
});

test('institution mision vision page loads', function () {
    $this->get(route('institucion.mision-vision'))
        ->assertOk()
        ->assertSee('Misión y Visión');
});

test('institution simbolos page loads', function () {
    $this->get(route('institucion.simbolos'))
        ->assertOk()
        ->assertSee('Símbolos');
});

test('institution equipo institucional page loads', function () {
    $this->get(route('institucion.equipo-institucional'))
        ->assertOk()
        ->assertSee('Equipo Institucional');
});

test('institution equipo institucional shows published staff members', function () {
    StaffMember::query()->create([
        'full_name' => 'Maria Garcia Lopez',
        'position_title' => 'Rectora',
        'staff_group' => 'directive',
        'status' => 'published',
        'sort_order' => 1,
    ]);

    StaffMember::query()->create([
        'full_name' => 'Staff borrador',
        'position_title' => 'Coordinador',
        'staff_group' => 'directive',
        'status' => 'draft',
    ]);

    $this->get(route('institucion.equipo-institucional'))
        ->assertOk()
        ->assertSee('Maria Garcia Lopez')
        ->assertSee('Rectora')
        ->assertDontSee('Staff borrador');
});

test('institution sedes page loads', function () {
    $this->get(route('institucion.sedes'))
        ->assertOk()
        ->assertSee('Sedes');
});

test('institution sedes shows published campuses', function () {
    Campus::query()->create([
        'name' => 'Sede Rural El Bongo',
        'slug' => 'sede-rural-el-bongo',
        'description' => 'Sede ubicada en la vereda El Bongo.',
        'address' => 'Vereda El Bongo',
        'status' => 'published',
        'sort_order' => 1,
    ]);

    Campus::query()->create([
        'name' => 'Sede borrador',
        'slug' => 'sede-borrador',
        'status' => 'draft',
    ]);

    $this->get(route('institucion.sedes'))
        ->assertOk()
        ->assertSee('Sede Rural El Bongo')
        ->assertSee('Vereda El Bongo')
        ->assertDontSee('Sede borrador');
});

test('institution pei page loads', function () {
    $this->get(route('institucion.pei'))
        ->assertOk()
        ->assertSee('PEI');
});

test('institution manual convivencia page loads', function () {
    $this->get(route('institucion.manual-convivencia'))
        ->assertOk()
        ->assertSee('Manual de Convivencia');
});

test('institution directorio route returns 404', function () {
    $this->get('/institucion/directorio')
        ->assertNotFound();
});

test('institution pei page shows configured document from settings', function () {
    $document = Document::query()->create([
        'title' => 'PEI 2026',
        'slug' => 'pei-2026',
        'summary' => 'Documento institucional del PEI.',
        'external_url' => 'https://drive.google.com/file/d/1pei2026documento/view?usp=sharing',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED PEI',
        'pei_document_id' => $document->id,
    ]);

    $this->get(route('institucion.pei'))
        ->assertOk()
        ->assertSee('PEI 2026')
        ->assertSee('Documento institucional del PEI.')
        ->assertSee('href="https://drive.google.com/file/d/1pei2026documento/view?usp=sharing"', false)
        ->assertSee('target="_blank"', false);
});

test('institution manual convivencia page shows configured google docs document from settings', function () {
    $document = Document::query()->create([
        'title' => 'Manual de convivencia 2026',
        'slug' => 'manual-convivencia-2026',
        'summary' => 'Reglas institucionales vigentes.',
        'external_url' => 'https://docs.google.com/document/d/1manualconvivencia2026/edit?usp=sharing',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Manual',
        'manual_convivencia_document_id' => $document->id,
    ]);

    $this->get(route('institucion.manual-convivencia'))
        ->assertOk()
        ->assertSee('Manual de convivencia 2026')
        ->assertSee('Reglas institucionales vigentes.')
        ->assertSee('href="https://docs.google.com/document/d/1manualconvivencia2026/edit?usp=sharing"', false)
        ->assertSee('target="_blank"', false);
});

test('institution pei and manual pages show empty state when settings document is missing', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Sin Documento',
    ]);

    foreach (['institucion.pei', 'institucion.manual-convivencia'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee('Documento no disponible en este momento.')
            ->assertSee(route('transparencia.documentos'))
            ->assertSee('Ver documentos de transparencia');
    }
});
