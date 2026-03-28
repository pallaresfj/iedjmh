<?php

use App\Models\Banner;
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
        ->assertSee('Resena historica')
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
    Page::query()->create([
        'title' => 'Planes personalizados',
        'slug' => 'planes-personalizados',
        'menu_binding' => 'academico.planes-area',
        'status' => 'published',
        'content' => '<p>Planes de area administrados desde CMS</p>',
    ]);

    $this->get(route('academico.planes-area'))
        ->assertOk()
        ->assertSee('Planes de area administrados desde CMS');
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

test('linked page banner is rendered above institutional page content', function () {
    $page = Page::query()->create([
        'title' => 'Historia con banner',
        'slug' => 'historia-banner',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
        'summary' => 'Resumen institucional visible',
        'content' => '<p>Contenido principal de historia</p>',
    ]);

    Banner::query()->create([
        'title' => 'Banner institucional de historia',
        'slug' => 'banner-historia',
        'page_id' => $page->id,
        'subtitle' => 'Comunidad educativa',
        'description' => 'Mensaje destacado para la pagina de historia.',
        'cta_label' => 'Leer mas',
        'cta_url' => 'https://example.com/historia',
        'target' => '_blank',
        'status' => 'published',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Resumen institucional visible')
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('Banner institucional de historia')
        ->assertSee('Mensaje destacado para la pagina de historia.')
        ->assertSee('href="https://example.com/historia"', false)
        ->assertSee('target="_blank"', false);
});

test('home hero ignores banners linked to specific pages', function () {
    $page = Page::query()->create([
        'title' => 'Historia home check',
        'slug' => 'historia-home-check',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner exclusivo de pagina interna',
        'slug' => 'banner-pagina-interna',
        'page_id' => $page->id,
        'status' => 'published',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Banner exclusivo de pagina interna');
});

test('citizen attention pages render linked banner for the mapped page slug', function () {
    $page = Page::query()->create([
        'title' => 'Contacto institucional',
        'slug' => 'atencion-contactenos',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner de atencion ciudadana',
        'slug' => 'banner-atencion-contacto',
        'page_id' => $page->id,
        'description' => 'Informacion destacada para quienes necesitan canales de contacto.',
        'status' => 'published',
    ]);

    $this->get(route('atencion.contactenos'))
        ->assertOk()
        ->assertSee('Banner de atencion ciudadana')
        ->assertSee('Informacion destacada para quienes necesitan canales de contacto.');
});

test('symbols page renders linked banner content when available', function () {
    $page = Page::query()->create([
        'title' => 'Simbolos institucionales CMS',
        'slug' => 'simbolos-institucionales-cms',
        'menu_binding' => 'institucion.simbolos',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner especial de simbolos',
        'slug' => 'banner-simbolos',
        'page_id' => $page->id,
        'subtitle' => 'Identidad institucional',
        'description' => 'Mensaje destacado de simbolos desde el banner.',
        'cta_label' => 'Conocer simbolos',
        'cta_url' => 'https://example.com/simbolos',
        'target' => '_blank',
        'status' => 'published',
    ]);

    $this->get(route('institucion.simbolos'))
        ->assertOk()
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertSee('Identidad institucional')
        ->assertSee('Banner especial de simbolos')
        ->assertSee('Mensaje destacado de simbolos desde el banner.')
        ->assertSee('Conocer simbolos')
        ->assertSee('href="https://example.com/simbolos"', false)
        ->assertSee('target="_blank"', false);
});

test('news listing page renders linked banner', function () {
    $page = Page::query()->create([
        'title' => 'Noticias CMS',
        'slug' => 'noticias',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner de noticias institucionales',
        'slug' => 'banner-noticias-listado',
        'page_id' => $page->id,
        'status' => 'published',
    ]);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('Banner de noticias institucionales');
});

test('news detail page reuses linked listing banner', function () {
    $page = Page::query()->create([
        'title' => 'Noticias CMS detalle',
        'slug' => 'noticias',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner noticias detalle',
        'slug' => 'banner-noticias-detalle',
        'page_id' => $page->id,
        'status' => 'published',
    ]);

    $post = Post::query()->create([
        'title' => 'Noticia para detalle',
        'slug' => 'noticia-para-detalle',
        'status' => 'published',
        'content' => '<p>Contenido de detalle</p>',
        'published_at' => now(),
    ]);

    $this->get(route('noticias.show', ['slug' => $post->slug]))
        ->assertOk()
        ->assertSee('Banner noticias detalle')
        ->assertSee('Noticia para detalle');
});

test('cms base page hides standard header when linked banner is present', function () {
    $page = Page::query()->create([
        'title' => 'Historia con reemplazo de encabezado',
        'slug' => 'historia-con-banner-encabezado',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
        'summary' => 'Resumen de historia',
    ]);

    Banner::query()->create([
        'title' => 'Banner principal historia',
        'slug' => 'banner-principal-historia',
        'page_id' => $page->id,
        'status' => 'published',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Banner principal historia')
        ->assertDontSee('Seccion institucional');
});

test('cms base page keeps standard header when no linked banner exists', function () {
    Page::query()->create([
        'title' => 'Historia sin banner',
        'slug' => 'historia-sin-banner-encabezado',
        'menu_binding' => 'institucion.historia',
        'status' => 'published',
    ]);

    $this->get(route('institucion.historia'))
        ->assertOk()
        ->assertSee('Seccion institucional');
});

test('detail page hides standard header and keeps listing banner for non cms views', function () {
    $listingPage = Page::query()->create([
        'title' => 'Noticias CMS para detalle',
        'slug' => 'noticias',
        'status' => 'published',
    ]);

    Banner::query()->create([
        'title' => 'Banner listado noticias',
        'slug' => 'banner-listado-noticias',
        'page_id' => $listingPage->id,
        'status' => 'published',
    ]);

    $post = Post::query()->create([
        'title' => 'Detalle noticia con encabezado',
        'slug' => 'detalle-noticia-con-encabezado',
        'status' => 'published',
        'content' => '<p>Contenido detalle</p>',
        'published_at' => now(),
    ]);

    $this->get(route('noticias.show', ['slug' => $post->slug]))
        ->assertOk()
        ->assertSee('public-internal-banner-section public-banner-full-bleed', false)
        ->assertDontSee('Seccion institucional')
        ->assertSee('Detalle noticia con encabezado')
        ->assertSee('Banner listado noticias');
});

test('non cms detail page renders dark fallback banner when cms banner is missing', function () {
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
