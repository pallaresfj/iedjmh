<?php

use App\Models\Campus;
use App\Models\StaffMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCampus(array $attributes = []): Campus
{
    return Campus::query()->create(array_merge([
        'name' => 'Sede Principal',
        'slug' => 'sede-principal',
        'status' => 'published',
        'sort_order' => 0,
    ], $attributes));
}

function createStaffMember(array $attributes = []): StaffMember
{
    return StaffMember::query()->create(array_merge([
        'full_name' => 'Funcionario Institucional',
        'position_title' => 'Cargo institucional',
        'department_label' => 'Dependencia',
        'staff_group' => 'directive',
        'institutional_email' => 'funcionario@iedjmh.edu.co',
        'status' => 'published',
        'published_at' => now(),
        'sort_order' => 0,
    ], $attributes));
}

test('equipo directivo page renders published directive staff', function () {
    $campus = createCampus();

    createStaffMember([
        'full_name' => 'Dr. Carlos Eduardo Mendez',
        'position_title' => 'Rector Institucional',
        'department_label' => 'Rectoria',
        'institutional_email' => 'rectoria@iedjmh.edu.co',
        'campus_id' => $campus->id,
    ]);

    createStaffMember([
        'full_name' => 'Docente de apoyo',
        'position_title' => 'Docente de primaria',
        'staff_group' => 'teacher',
        'institutional_email' => 'docente@iedjmh.edu.co',
        'campus_id' => $campus->id,
    ]);

    createStaffMember([
        'full_name' => 'Directivo borrador',
        'position_title' => 'Coordinador',
        'status' => 'draft',
        'published_at' => null,
        'institutional_email' => 'borrador@iedjmh.edu.co',
        'campus_id' => $campus->id,
    ]);

    $this->get(route('institucion.equipo-directivo'))
        ->assertOk()
        ->assertSee('Buscar por nombre o cargo...')
        ->assertSee('Dr. Carlos Eduardo Mendez')
        ->assertSee('Rector Institucional')
        ->assertSee('Rectoria')
        ->assertSee('mailto:rectoria@iedjmh.edu.co', false)
        ->assertDontSee('Docente de apoyo')
        ->assertDontSee('Directivo borrador');
});

test('equipo directivo supports text search by name or position', function () {
    $campus = createCampus();

    createStaffMember([
        'full_name' => 'MSc. Martha Lucia Rivera',
        'position_title' => 'Coordinadora Academica',
        'campus_id' => $campus->id,
    ]);

    createStaffMember([
        'full_name' => 'Lic. Ricardo Jose Torres',
        'position_title' => 'Coordinador de Convivencia',
        'campus_id' => $campus->id,
    ]);

    $this->get(route('institucion.equipo-directivo', ['q' => 'Martha']))
        ->assertOk()
        ->assertSee('MSc. Martha Lucia Rivera')
        ->assertDontSee('Lic. Ricardo Jose Torres');

    $this->get(route('institucion.equipo-directivo', ['q' => 'Convivencia']))
        ->assertOk()
        ->assertSee('Lic. Ricardo Jose Torres')
        ->assertDontSee('MSc. Martha Lucia Rivera');
});

test('equipo directivo filters by selected campus', function () {
    $principal = createCampus([
        'name' => 'Sede Principal',
        'slug' => 'sede-principal',
    ]);

    $campestre = createCampus([
        'name' => 'Sede Campestre',
        'slug' => 'sede-campestre',
        'sort_order' => 10,
    ]);

    createStaffMember([
        'full_name' => 'Beatriz Elena Castro',
        'position_title' => 'Secretaria General',
        'campus_id' => $principal->id,
    ]);

    createStaffMember([
        'full_name' => 'Carlos Mendez',
        'position_title' => 'Rector Institucional',
        'campus_id' => $campestre->id,
    ]);

    $this->get(route('institucion.equipo-directivo', ['campus' => 'sede-campestre']))
        ->assertOk()
        ->assertSee('Carlos Mendez')
        ->assertDontSee('Beatriz Elena Castro');
});

test('equipo directivo contact action uses institutional email mailto', function () {
    $campus = createCampus();

    $member = createStaffMember([
        'full_name' => 'Carlos Eduardo Mendez',
        'position_title' => 'Rector Institucional',
        'institutional_email' => 'rectoria@iedjmh.edu.co',
        'campus_id' => $campus->id,
    ]);

    $expected = 'mailto:rectoria@iedjmh.edu.co?subject='.rawurlencode('Contacto institucional - '.$member->full_name);

    $this->get(route('institucion.equipo-directivo'))
        ->assertOk()
        ->assertSee($expected, false);
});

test('equipo directivo shows empty state when there are no matching directives', function () {
    $campus = createCampus();

    createStaffMember([
        'full_name' => 'Docente sin directorio',
        'position_title' => 'Docente de ciencias',
        'staff_group' => 'teacher',
        'campus_id' => $campus->id,
    ]);

    $this->get(route('institucion.equipo-directivo', ['q' => 'No existe']))
        ->assertOk()
        ->assertSee('No se encontraron integrantes del equipo directivo con los filtros aplicados.');
});
