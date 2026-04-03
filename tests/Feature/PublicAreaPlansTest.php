<?php

use App\Models\AreaPlan;
use App\Models\Banner;
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

test('public area plans page shows classic header when no linked banner exists', function () {
    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Seccion institucional')
        ->assertDontSee('public-internal-banner-section public-banner-full-bleed', false);
});

test('public area plans page replaces header with linked banner when available', function () {
    $page = Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes de Area CMS',
            'slug' => 'academico-planes-area',
            'status' => 'published',
        ],
    );

    Banner::query()->create([
        'title' => 'Banner destacado de planes',
        'slug' => 'banner-destacado-planes',
        'page_id' => $page->id,
        'description' => 'Consulta institucional de planes por area.',
        'status' => 'published',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Banner destacado de planes')
        ->assertSee('Consulta institucional de planes por area.')
        ->assertDontSee('Seccion institucional');
});

test('public area plans page falls back to canonical banner slug when banner is not linked by page id', function () {
    Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes de Area CMS',
            'slug' => 'academico-planes-area',
            'status' => 'published',
        ],
    );

    Banner::query()->create([
        'title' => 'Banner canonico de planes',
        'slug' => 'academico-planes-area',
        'page_id' => null,
        'description' => 'Banner por slug canonico sin page_id.',
        'status' => 'published',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Banner canonico de planes')
        ->assertSee('Banner por slug canonico sin page_id.')
        ->assertDontSee('Seccion institucional');
});

test('public area plans page does not use non canonical slug banner fallback', function () {
    Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes de Area CMS',
            'slug' => 'academico-planes-area',
            'status' => 'published',
        ],
    );

    Banner::query()->create([
        'title' => 'Banner slug no canonico',
        'slug' => 'academico-planes-de-area',
        'page_id' => null,
        'description' => 'Este banner no debe mostrarse.',
        'status' => 'published',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('public-internal-banner--fallback', false)
        ->assertDontSee('Banner slug no canonico')
        ->assertDontSee('Este banner no debe mostrarse.');
});

test('public area plans page prioritizes page linked banner over canonical slug fallback', function () {
    $page = Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes de Area CMS',
            'slug' => 'academico-planes-area',
            'status' => 'published',
        ],
    );

    Banner::query()->create([
        'title' => 'Banner fallback por slug',
        'slug' => 'academico-planes-area',
        'page_id' => null,
        'description' => 'Fallback por slug.',
        'status' => 'published',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    Banner::query()->create([
        'title' => 'Banner principal por pagina',
        'slug' => 'banner-principal-planes-area',
        'page_id' => $page->id,
        'description' => 'Este banner debe ganar por page_id.',
        'status' => 'published',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Banner principal por pagina')
        ->assertSee('Este banner debe ganar por page_id.')
        ->assertDontSee('Banner fallback por slug')
        ->assertDontSee('Fallback por slug.');
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
