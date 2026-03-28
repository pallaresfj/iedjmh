<?php

use App\Models\AreaPlan;
use App\Models\Banner;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public area plans page renders formatted cards and pagination', function () {
    foreach (range(1, 6) as $index) {
        AreaPlan::query()->create([
            'area_name' => "Area {$index}",
            'responsible_teachers' => "Docente {$index}A, Docente {$index}B",
            'icon' => 'menu_book',
            'plan_url' => "https://example.com/plan-{$index}",
            'status' => 'published',
            'sort_order' => $index,
            'published_at' => now(),
        ]);
    }

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Area 1')
        ->assertSee('Area 5')
        ->assertDontSee('Area 6')
        ->assertDontSee('Plan de Area -')
        ->assertSee('Docentes Responsables:')
        ->assertSee('Consultar Plan')
        ->assertSee('page=2', false)
        ->assertSee('data-public-pagination-link', false);

    $this->get(route('academico.planes-area', ['page' => 2]))
        ->assertOk()
        ->assertSee('Area 6')
        ->assertDontSee('Area 1');
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
