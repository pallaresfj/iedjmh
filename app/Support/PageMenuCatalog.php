<?php

namespace App\Support;

class PageMenuCatalog
{
    /**
     * @var array<string, array{label: string, route: string, path: string, slug: string, section: string}>
     */
    private const ITEMS = [
        'institucion.historia' => [
            'label' => 'Historia',
            'route' => 'institucion.historia',
            'path' => '/institucion/historia',
            'slug' => 'institucion-historia',
            'section' => 'institucion',
        ],
        'institucion.mision-vision' => [
            'label' => 'Mision y Vision',
            'route' => 'institucion.mision-vision',
            'path' => '/institucion/mision-vision',
            'slug' => 'institucion-mision-vision',
            'section' => 'institucion',
        ],
        'institucion.simbolos' => [
            'label' => 'Simbolos',
            'route' => 'institucion.simbolos',
            'path' => '/institucion/simbolos',
            'slug' => 'institucion-simbolos',
            'section' => 'institucion',
        ],
        'institucion.equipo-directivo' => [
            'label' => 'Equipo Directivo',
            'route' => 'institucion.equipo-directivo',
            'path' => '/institucion/equipo-directivo',
            'slug' => 'institucion-equipo-directivo',
            'section' => 'institucion',
        ],
        'academico.niveles-educativos' => [
            'label' => 'Niveles Educativos',
            'route' => 'academico.niveles-educativos',
            'path' => '/academico/niveles-educativos',
            'slug' => 'academico-niveles-educativos',
            'section' => 'academico',
        ],
        'academico.modalidad-agropecuaria' => [
            'label' => 'Modalidad Agropecuaria',
            'route' => 'academico.modalidad-agropecuaria',
            'path' => '/academico/modalidad-agropecuaria',
            'slug' => 'academico-modalidad-agropecuaria',
            'section' => 'academico',
        ],
        'academico.planes-area' => [
            'label' => 'Planes de Area',
            'route' => 'academico.planes-area',
            'path' => '/academico/planes-area',
            'slug' => 'academico-planes-area',
            'section' => 'academico',
        ],
        'academico.sistema-evaluacion' => [
            'label' => 'Sistema de Evaluacion',
            'route' => 'academico.sistema-evaluacion',
            'path' => '/academico/sistema-evaluacion',
            'slug' => 'academico-sistema-evaluacion',
            'section' => 'academico',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function formOptions(): array
    {
        $options = [];

        foreach (self::ITEMS as $binding => $item) {
            $options[$binding] = "{$item['label']} ({$item['path']})";
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function bindingsForSection(string $section): array
    {
        $bindings = [];

        foreach (self::ITEMS as $binding => $item) {
            if ($item['section'] === $section) {
                $bindings[] = $binding;
            }
        }

        return $bindings;
    }

    /**
     * @return array<string, string>
     */
    public static function slugToBindingMap(): array
    {
        $map = [];

        foreach (self::ITEMS as $binding => $item) {
            $map[$item['slug']] = $binding;
        }

        return $map;
    }

    public static function slugFor(string $binding): ?string
    {
        return self::ITEMS[$binding]['slug'] ?? null;
    }

    public static function routeFor(string $binding): ?string
    {
        return self::ITEMS[$binding]['route'] ?? null;
    }

    public static function pathFor(string $binding): ?string
    {
        return self::ITEMS[$binding]['path'] ?? null;
    }
}
