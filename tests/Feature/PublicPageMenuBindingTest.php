<?php

use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('institution page uses published menu binding content with sanitized rich text', function () {
    Page::query()->create([
        'title' => 'Historia CMS',
        'slug' => 'historia-personalizada',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
        'content' => '<h2>Nuestra historia</h2><p><strong>Contenido institucional</strong> para la comunidad.</p><script>alert(1)</script><p><a href="javascript:alert(1)" onclick="hack()">enlace no seguro</a></p><p><a href="https://example.com" target="_blank">enlace seguro</a></p>',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('<h2>Nuestra historia</h2>', false)
        ->assertSee('<strong>Contenido institucional</strong>', false)
        ->assertSee('href="https://example.com"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertDontSee('javascript:alert(1)', false)
        ->assertDontSee('alert(1)', false)
        ->assertDontSee('onclick=', false);
});

test('institution page falls back to base content when bound page is not published', function () {
    Page::query()->create([
        'title' => 'Historia en borrador',
        'slug' => 'historia-borrador',
        'menu_binding' => 'institucion.historia',
        'status' => 'draft',
        'content' => '<p>Contenido no publicado</p>',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Trayectoria institucional al servicio de la educación en Pivijay, Magdalena.')
        ->assertDontSee('Contenido no publicado');
});

test('institution page keeps legacy slug compatibility when menu binding is null', function () {
    Page::query()->create([
        'title' => 'Historia legado',
        'slug' => 'institucion-historia',
        'menu_binding' => null,
        'status' => 'published',
        'content' => '<p>Contenido legado publicado</p>',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Contenido legado publicado');
});

test('academic planes area route resolves published page by menu binding', function () {
    Page::query()->updateOrCreate(
        ['menu_binding' => 'academico.planes-area'],
        [
            'title' => 'Planes personalizados',
            'slug' => 'academico-planes-area',
            'status' => 'published',
            'content' => '<p>Planes de area administrados desde CMS</p>',
        ],
    );

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Planes personalizados')
        ->assertDontSee('Planes de area administrados desde CMS');
});

test('menu binding is unique in pages table', function () {
    Page::query()->create([
        'title' => 'Historia unica',
        'slug' => 'historia-unica',
        'menu_binding' => 'institucion.historia',
        'status' => 'draft',
    ]);

    expect(function (): void {
        Page::query()->create([
            'title' => 'Historia duplicada',
            'slug' => 'historia-duplicada',
            'menu_binding' => 'institucion.historia',
            'status' => 'draft',
        ]);
    })->toThrow(QueryException::class);
});

test('institution cms page renders fallback internal banner from title and summary', function () {
    Page::query()->create([
        'title' => 'Historia con encabezado visual',
        'slug' => 'historia-encabezado-visual',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
        'summary' => 'Resumen institucional visible en encabezado.',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('public-internal-banner--fallback', false)
        ->assertSee('Historia con encabezado visual')
        ->assertSee('Resumen institucional visible en encabezado.')
        ->assertDontSee('Seccion institucional');
});

test('news detail page renders fallback internal banner when no cms page is mapped', function () {
    $post = Post::query()->create([
        'title' => 'Noticia sin banner asociado',
        'slug' => 'noticia-sin-banner-asociado',
        'status' => 'published',
        'content' => '<p>Contenido de prueba</p>',
        'published_at' => now(),
    ]);

    $this->get(route('noticias.show', ['slug' => $post->slug]))
        ->assertOk()
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('public-internal-banner--fallback', false)
        ->assertDontSee('Seccion institucional')
        ->assertSee('Noticia sin banner asociado');
});

test('selected academic routes force fallback internal banner style', function () {
    $routes = [
        'academico.niveles-educativos',
        'academico.modalidad',
        'academico.planes-area',
        'academico.sistema-evaluacion',
        'academico.proyectos-pedagogicos',
        'academico.calendario-academico',
    ];

    foreach ($routes as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
            ->assertSee('public-internal-banner--fallback', false)
            ->assertDontSee('Seccion institucional');
    }
});

test('selected institution routes force fallback internal banner style', function () {
    $routes = [
        'institucion.historia',
        'institucion.mision-vision',
        'institucion.simbolos',
        'institucion.equipo-institucional',
        'institucion.sedes',
        'institucion.pei',
        'institucion.manual-convivencia',
        'institucion.directorio',
    ];

    foreach ($routes as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
            ->assertSee('public-internal-banner--fallback', false)
            ->assertDontSee('Seccion institucional');
    }
});
