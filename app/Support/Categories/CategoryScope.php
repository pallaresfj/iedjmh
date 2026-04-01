<?php

namespace App\Support\Categories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class CategoryScope
{
    public const POSTS = 'posts';

    public const DOCUMENTS = 'documents';

    public const PROJECTS = 'projects';

    public const EVENTS = 'events';

    public const PROCEDURES = 'procedures';

    public const FAQS = 'faqs';

    /**
     * @var array<string, string>
     */
    private const CONTEXT_PARENT_SLUGS = [
        self::POSTS => 'noticias',
        self::DOCUMENTS => 'documentos',
        self::PROJECTS => 'proyectos',
        self::EVENTS => 'eventos',
        self::PROCEDURES => 'tramites-y-servicios',
        self::FAQS => 'preguntas-frecuentes',
    ];

    public static function applySubcategoryScope(Builder $query, string $context): void
    {
        $parentId = static::resolveParentCategoryId($context);

        if ($parentId === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public static function hasParentCategory(string $context): bool
    {
        return static::resolveParentCategoryId($context) !== null;
    }

    public static function helperText(string $context, string $parentName): string
    {
        if (! static::hasParentCategory($context)) {
            return sprintf('Crea primero la categoria padre "%s" y sus subcategorias.', $parentName);
        }

        return sprintf('Solo se muestran subcategorias de la categoria padre "%s".', $parentName);
    }

    public static function parentSlugFor(string $context): string
    {
        $parentSlug = static::CONTEXT_PARENT_SLUGS[$context] ?? null;

        if ($parentSlug === null) {
            throw new InvalidArgumentException(sprintf('No parent category slug is configured for context "%s".', $context));
        }

        return $parentSlug;
    }

    private static function resolveParentCategoryId(string $context): ?int
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('slug', static::parentSlugFor($context))
            ->value('id');
    }
}
