<?php

use App\Models\AreaPlan;
use App\Models\Page;
use App\Models\StaffMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public area plans page renders formatted cards and pagination', function () {
    foreach (range(1, 6) as $index) {
        $teacherA = createPublishedTeacherForPublicAreaPlanTest("Docente {$index}A");
        $teacherB = createPublishedTeacherForPublicAreaPlanTest("Docente {$index}B");

        $plan = AreaPlan::query()->create([
            'area_name' => "Area {$index}",
            'icon' => 'menu_book',
            'plan_url' => "https://example.com/plan-{$index}",
            'status' => 'published',
            'sort_order' => $index,
            'published_at' => now(),
        ]);

        $plan->responsibleTeachers()->sync([
            $teacherA->id => ['sort_order' => 0],
            $teacherB->id => ['sort_order' => 1],
        ]);
    }

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Area 1')
        ->assertSee('Area 5')
        ->assertDontSee('Area 6')
        ->assertDontSee('Plan de Area -')
        ->assertSee('Docentes Responsables:')
        ->assertSee('Docente 1A, Docente 1B')
        ->assertSee('Consultar Plan')
        ->assertSee('page=2', false)
        ->assertSee('data-public-pagination-link', false);

    $this->get(route('academico.planes-area', ['page' => 2]))
        ->assertOk()
        ->assertSee('Area 6')
        ->assertDontSee('Area 1');
});

test('public area plans page shows fallback when plan has no related teachers', function () {
    AreaPlan::query()->create([
        'area_name' => 'Area sin docentes',
        'icon' => 'menu_book',
        'plan_url' => 'https://example.com/plan-sin-docentes',
        'status' => 'published',
        'sort_order' => 1,
        'published_at' => now(),
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Por asignar');
});

test('public area plans page hides cms content blocks', function () {
    Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes desde CMS',
            'slug' => 'academico-planes-area',
            'status' => 'published',
            'content' => '<p>Contenido largo de CMS que no debe verse en planes-area.</p>',
        ],
    );

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Planes desde CMS')
        ->assertDontSee('Contenido largo de CMS que no debe verse en planes-area.');
});

test('public area plans page uses fallback internal banner style from title and summary', function () {
    Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes de Area CMS',
            'slug' => 'academico-planes-area',
            'summary' => 'Resumen institucional de planes de area.',
            'status' => 'published',
        ],
    );

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('public-internal-banner--fallback', false)
        ->assertSee('Planes de Area CMS')
        ->assertSee('Resumen institucional de planes de area.')
        ->assertDontSee('Seccion institucional');
});

function createPublishedTeacherForPublicAreaPlanTest(string $fullName): StaffMember
{
    return StaffMember::query()->create([
        'full_name' => $fullName,
        'position_title' => 'Docente',
        'staff_group' => 'teacher',
        'status' => 'published',
        'published_at' => now(),
        'sort_order' => 0,
    ]);
}
