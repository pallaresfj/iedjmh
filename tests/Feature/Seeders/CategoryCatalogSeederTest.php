<?php

use App\Models\Category;
use Database\Seeders\CategoryCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeder creates the expected category hierarchy from catalog', function (): void {
    $this->seed(CategoryCatalogSeeder::class);

    expect(Category::query()->count())->toBe(44);
    expect(Category::query()->whereNull('parent_id')->count())->toBe(9);
    expect(Category::query()->whereNotNull('parent_id')->count())->toBe(35);

    $expectedChildren = [
        'noticias-institucionales' => 'noticias',
        'noticias-academicas' => 'noticias',
        'noticias-convivencia-y-bienestar' => 'noticias',
        'noticias-cultura-y-deporte' => 'noticias',
        'noticias-gobierno-escolar-y-participacion' => 'noticias',
        'noticias-comunidad-y-alianzas' => 'noticias',
        'noticias-infraestructura-y-gestion' => 'noticias',

        'documentos-institucionales' => 'documentos',
        'documentos-academicos' => 'documentos',
        'documentos-normativos' => 'documentos',
        'documentos-gestion-y-planeacion' => 'documentos',
        'documentos-transparencia' => 'documentos',
        'documentos-formatos-y-formularios' => 'documentos',

        'proyectos-academicos-transversales' => 'proyectos',
        'proyectos-pedagogicos-obligatorios' => 'proyectos',
        'proyectos-institucionales-estrategicos' => 'proyectos',
        'proyectos-productivos-o-tecnicos' => 'proyectos',
        'proyectos-convivencia-y-comunidad' => 'proyectos',
        'proyectos-alianzas-externas' => 'proyectos',

        'eventos-academicos' => 'eventos',
        'eventos-institucionales' => 'eventos',
        'eventos-participacion' => 'eventos',
        'eventos-culturales-y-deportivos' => 'eventos',
        'eventos-bienestar-y-orientacion' => 'eventos',

        'tramites-y-servicios-academicos' => 'tramites-y-servicios',
        'tramites-y-servicios-admisiones-y-matricula' => 'tramites-y-servicios',
        'tramites-y-servicios-atencion-a-familias' => 'tramites-y-servicios',
        'tramites-y-servicios-egresados' => 'tramites-y-servicios',
        'tramites-y-servicios-docentes-y-administrativos' => 'tramites-y-servicios',

        'preguntas-frecuentes-matricula' => 'preguntas-frecuentes',
        'preguntas-frecuentes-vida-academica' => 'preguntas-frecuentes',
        'preguntas-frecuentes-convivencia-escolar' => 'preguntas-frecuentes',
        'preguntas-frecuentes-servicios-escolares' => 'preguntas-frecuentes',
        'preguntas-frecuentes-tramites-y-certificados' => 'preguntas-frecuentes',
        'preguntas-frecuentes-participacion-y-gobierno-escolar' => 'preguntas-frecuentes',
    ];

    foreach ($expectedChildren as $childSlug => $parentSlug) {
        $child = Category::query()->with('parent')->where('slug', $childSlug)->first();

        expect($child)->not->toBeNull();
        expect($child?->parent?->slug)->toBe($parentSlug);
    }
});

test('seeder is idempotent, restores catalog records and deletes categories outside the catalog', function (): void {
    $trashedCatalogCategory = Category::query()->create([
        'name' => 'Categoria noticias vieja',
        'slug' => 'noticias',
        'status' => 'draft',
        'sort_order' => 999,
    ]);
    $trashedCatalogCategory->delete();

    Category::query()->create([
        'name' => 'Categoria fuera de catalogo',
        'slug' => 'fuera-de-catalogo',
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $this->seed(CategoryCatalogSeeder::class);
    $this->seed(CategoryCatalogSeeder::class);

    expect(Category::query()->count())->toBe(44);
    expect(Category::query()->whereNull('parent_id')->count())->toBe(9);
    expect(Category::query()->whereNotNull('parent_id')->count())->toBe(35);
    expect(Category::query()->where('slug', 'noticias')->where('status', 'published')->exists())->toBeTrue();
    expect(Category::query()->where('slug', 'noticias')->where('sort_order', 1)->exists())->toBeTrue();
    expect(Category::query()->withTrashed()->where('slug', 'noticias')->whereNull('deleted_at')->exists())->toBeTrue();
    expect(Category::query()->withTrashed()->where('slug', 'fuera-de-catalogo')->exists())->toBeFalse();
});
