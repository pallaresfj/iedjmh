<?php

use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('projects page lists published projects', function () {
    $featured = Project::query()->create([
        'title' => 'Proyecto Destacado',
        'slug' => 'proyecto-destacado',
        'summary' => 'Proyecto con prioridad visual.',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDay(),
        'sort_order' => 10,
    ]);

    $regular = Project::query()->create([
        'title' => 'Proyecto Regular',
        'slug' => 'proyecto-regular',
        'summary' => 'Proyecto regular en listado.',
        'status' => 'published',
        'is_featured' => false,
        'published_at' => now(),
        'sort_order' => 0,
    ]);

    Project::query()->create([
        'title' => 'Proyecto Borrador',
        'slug' => 'proyecto-borrador',
        'status' => 'draft',
        'is_featured' => true,
    ]);

    $this->get(route('academico.proyectos-pedagogicos'))
        ->assertOk()
        ->assertSee('Proyecto destacado')
        ->assertSee($featured->title)
        ->assertSee($regular->title)
        ->assertDontSee('Proyecto Borrador')
        ->assertSeeInOrder([$featured->title, $regular->title]);
});

test('project detail page renders published project and hides draft project', function () {
    $publishedProject = Project::query()->create([
        'title' => 'Proyecto Productivo Escolar',
        'slug' => 'proyecto-productivo-escolar',
        'summary' => 'Resumen del proyecto.',
        'description' => 'Descripcion completa del proyecto.',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $draftProject = Project::query()->create([
        'title' => 'Proyecto No Publicado',
        'slug' => 'proyecto-no-publicado',
        'status' => 'draft',
    ]);

    $this->get(route('academico.proyectos-pedagogicos.show', ['slug' => $publishedProject->slug]))
        ->assertOk()
        ->assertSee($publishedProject->title)
        ->assertSee($publishedProject->description);

    $this->get(route('academico.proyectos-pedagogicos.show', ['slug' => $draftProject->slug]))
        ->assertNotFound();
});

test('projects filters by search and category', function () {
    $sostenibilidad = Category::query()->create([
        'name' => 'Sostenibilidad',
        'slug' => 'sostenibilidad',
        'status' => 'published',
    ]);

    $innovacion = Category::query()->create([
        'name' => 'Innovacion',
        'slug' => 'innovacion',
        'status' => 'published',
    ]);

    $featuredProject = Project::query()->create([
        'title' => 'Huerta destacada',
        'slug' => 'huerta-destacada',
        'summary' => 'Resumen del proyecto destacado.',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now(),
    ]);
    $featuredProject->categories()->attach($sostenibilidad->id);

    $regularProject = Project::query()->create([
        'title' => 'Laboratorio de suelos',
        'slug' => 'laboratorio-suelos',
        'summary' => 'Analisis de suelo para el cultivo.',
        'status' => 'published',
        'is_featured' => false,
        'published_at' => now()->subDay(),
    ]);
    $regularProject->categories()->attach($innovacion->id);

    $this->get(route('academico.proyectos-pedagogicos', ['q' => 'suelos']))
        ->assertOk()
        ->assertSee($regularProject->title)
        ->assertDontSee($featuredProject->title);

    $this->get(route('academico.proyectos-pedagogicos', ['category' => 'sostenibilidad']))
        ->assertOk()
        ->assertSee($featuredProject->title)
        ->assertDontSee($regularProject->title);

});

test('projects page renders accessible image urls stored on local disk', function () {
    Storage::disk('local')->put('projects/proyecto-prueba.jpg', 'contenido');

    Project::query()->create([
        'title' => 'Proyecto con imagen',
        'slug' => 'proyecto-con-imagen',
        'status' => 'published',
        'cover_image_path' => 'projects/proyecto-prueba.jpg',
    ]);

    $response = $this->get(route('academico.proyectos-pedagogicos'))->assertOk();
    $content = $response->getContent();

    expect($content)->toBeString();

    preg_match('/src="([^"]*storage\/projects\/proyecto-prueba\.jpg[^"]*)"/', $content, $matches);

    expect($matches[1] ?? null)->not->toBeNull();

    $imageUrl = $matches[1];
    $parsed = parse_url($imageUrl);
    $query = $parsed['query'] ?? '';

    expect($query)->toContain('expires=')
        ->and($query)->toContain('signature=');
});

test('project detail shows external reference button and gallery thumbnails', function () {
    $project = Project::query()->create([
        'title' => 'Proyecto con recursos',
        'slug' => 'proyecto-con-recursos',
        'status' => 'published',
        'external_url' => 'https://example.com/proyectos/recurso',
        'cover_image_path' => '/imagenes/proyectos/portada.jpg',
        'gallery_image_paths' => [
            '/imagenes/proyectos/galeria-1.jpg',
            '/imagenes/proyectos/galeria-2.jpg',
        ],
    ]);

    $this->get(route('academico.proyectos-pedagogicos.show', ['slug' => $project->slug]))
        ->assertOk()
        ->assertSee('Mas información')
        ->assertSee('href="https://example.com/proyectos/recurso"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('data-project-gallery', false)
        ->assertSee('/imagenes/proyectos/portada.jpg', false)
        ->assertSee('/imagenes/proyectos/galeria-1.jpg', false)
        ->assertSee('/imagenes/proyectos/galeria-2.jpg', false);
});

test('project detail uses gallery image as primary when cover is missing', function () {
    $project = Project::query()->create([
        'title' => 'Proyecto sin portada',
        'slug' => 'proyecto-sin-portada',
        'status' => 'published',
        'gallery_image_paths' => [
            '/imagenes/proyectos/alterna.jpg',
        ],
    ]);

    $this->get(route('academico.proyectos-pedagogicos.show', ['slug' => $project->slug]))
        ->assertOk()
        ->assertSee('/imagenes/proyectos/alterna.jpg', false)
        ->assertSee('data-gallery-open', false)
        ->assertSee('data-project-gallery-modal', false);
});

test('project model persists gallery_image_paths as array', function () {
    $project = Project::query()->create([
        'title' => 'Proyecto con array',
        'slug' => 'proyecto-con-array',
        'status' => 'draft',
        'gallery_image_paths' => [
            'projects/gallery/imagen-1.jpg',
            'projects/gallery/imagen-2.jpg',
        ],
    ]);

    $project->refresh();

    expect($project->gallery_image_paths)
        ->toBeArray()
        ->toHaveCount(2)
        ->toBe([
            'projects/gallery/imagen-1.jpg',
            'projects/gallery/imagen-2.jpg',
        ]);
});

test('only one published featured project remains active', function () {
    $first = Project::query()->create([
        'title' => 'Proyecto primero',
        'slug' => 'proyecto-primero',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now()->subDay(),
    ]);

    $second = Project::query()->create([
        'title' => 'Proyecto segundo',
        'slug' => 'proyecto-segundo',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $first->refresh();
    $second->refresh();

    expect($first->is_featured)->toBeFalse()
        ->and($second->is_featured)->toBeTrue();
});

test('single project is automatically featured', function () {
    $project = Project::query()->create([
        'title' => 'Proyecto unico',
        'slug' => 'proyecto-unico',
        'status' => 'published',
        'is_featured' => false,
        'published_at' => now(),
    ]);

    $project->refresh();

    expect($project->is_featured)->toBeTrue();
});

test('project detail renders html description content', function () {
    $project = Project::query()->create([
        'title' => 'Proyecto con html',
        'slug' => 'proyecto-con-html',
        'status' => 'published',
        'description' => '<p>Descripcion con <strong>HTML</strong> renderizado.</p>',
        'published_at' => now(),
    ]);

    $this->get(route('academico.proyectos-pedagogicos.show', ['slug' => $project->slug]))
        ->assertOk()
        ->assertSee('<strong>HTML</strong>', false);
});

test('home featured project uses project gallery images and dynamic content', function () {
    Project::query()->create([
        'title' => 'Proyecto Ambiental 2027',
        'slug' => 'proyecto-ambiental-2027',
        'summary' => 'Subtitulo de proyecto ambiental.',
        'description' => '<p>Descripcion extensa del proyecto para fortalecer procesos ambientales institucionales con estudiantes y familias.</p>',
        'status' => 'published',
        'is_featured' => true,
        'published_at' => now(),
        'cover_image_path' => '/imagenes/proyectos/portada-no-usar.jpg',
        'gallery_image_paths' => [
            '/imagenes/proyectos/galeria-1.jpg',
            '/imagenes/proyectos/galeria-2.jpg',
            '/imagenes/proyectos/galeria-3.jpg',
            '/imagenes/proyectos/galeria-4.jpg',
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Proyecto Ambiental 2027')
        ->assertSee('Subtitulo de proyecto ambiental.')
        ->assertSee('Descripcion extensa del proyecto para fortalecer procesos ambientales institucionales')
        ->assertSee('/imagenes/proyectos/galeria-1.jpg', false)
        ->assertSee('/imagenes/proyectos/galeria-2.jpg', false)
        ->assertSee('/imagenes/proyectos/galeria-3.jpg', false)
        ->assertSee('/imagenes/proyectos/galeria-4.jpg', false)
        ->assertDontSee('/imagenes/proyectos/portada-no-usar.jpg', false)
        ->assertSee(route('academico.proyectos-pedagogicos'), false);
});
