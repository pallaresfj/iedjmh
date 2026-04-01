<?php

use App\Models\Category;
use App\Support\Categories\CategoryScope;
use Database\Seeders\CategoryCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category scope filters subcategories by the configured parent category for each context', function (): void {
    $this->seed(CategoryCatalogSeeder::class);

    $contexts = [
        CategoryScope::POSTS => ['parent' => 'noticias', 'included' => 'noticias-institucionales', 'excluded' => 'documentos-institucionales'],
        CategoryScope::DOCUMENTS => ['parent' => 'documentos', 'included' => 'documentos-institucionales', 'excluded' => 'proyectos-academicos-transversales'],
        CategoryScope::PROJECTS => ['parent' => 'proyectos', 'included' => 'proyectos-academicos-transversales', 'excluded' => 'eventos-academicos'],
        CategoryScope::EVENTS => ['parent' => 'eventos', 'included' => 'eventos-academicos', 'excluded' => 'tramites-y-servicios-academicos'],
        CategoryScope::PROCEDURES => ['parent' => 'tramites-y-servicios', 'included' => 'tramites-y-servicios-academicos', 'excluded' => 'preguntas-frecuentes-matricula'],
        CategoryScope::FAQS => ['parent' => 'preguntas-frecuentes', 'included' => 'preguntas-frecuentes-matricula', 'excluded' => 'noticias-institucionales'],
    ];

    foreach ($contexts as $context => $expectation) {
        $query = Category::query();
        CategoryScope::applySubcategoryScope($query, $context);

        $slugs = $query->pluck('slug');

        expect(CategoryScope::parentSlugFor($context))->toBe($expectation['parent']);
        expect(CategoryScope::hasParentCategory($context))->toBeTrue();
        expect($slugs->contains($expectation['included']))->toBeTrue();
        expect($slugs->contains($expectation['excluded']))->toBeFalse();
        expect($slugs->isNotEmpty())->toBeTrue();
    }
});

test('category scope returns no options when the parent category does not exist', function (): void {
    $this->seed(CategoryCatalogSeeder::class);

    $newsParent = Category::query()->where('slug', 'noticias')->firstOrFail();
    $newsParent->forceDelete();

    $query = Category::query();
    CategoryScope::applySubcategoryScope($query, CategoryScope::POSTS);

    expect(CategoryScope::hasParentCategory(CategoryScope::POSTS))->toBeFalse();
    expect($query->count())->toBe(0);
});
