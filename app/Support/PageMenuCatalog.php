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
            'label' => 'Misión y Visión',
            'route' => 'institucion.mision-vision',
            'path' => '/institucion/mision-vision',
            'slug' => 'institucion-mision-vision',
            'section' => 'institucion',
        ],
        'institucion.simbolos' => [
            'label' => 'Símbolos',
            'route' => 'institucion.simbolos',
            'path' => '/institucion/simbolos',
            'slug' => 'institucion-simbolos',
            'section' => 'institucion',
        ],
        'institucion.equipo-institucional' => [
            'label' => 'Equipo Institucional',
            'route' => 'institucion.equipo-institucional',
            'path' => '/institucion/equipo-institucional',
            'slug' => 'institucion-equipo-institucional',
            'section' => 'institucion',
        ],
        'academico.niveles-educativos' => [
            'label' => 'Niveles Educativos',
            'route' => 'academico.niveles-educativos',
            'path' => '/academico/niveles-educativos',
            'slug' => 'academico-niveles-educativos',
            'section' => 'academico',
        ],
        'academico.modalidad' => [
            'label' => 'Modalidad',
            'route' => 'academico.modalidad',
            'path' => '/academico/modalidad',
            'slug' => 'academico-modalidad',
            'section' => 'academico',
        ],
        'academico.planes-area' => [
            'label' => 'Planes de Área',
            'route' => 'academico.planes-area',
            'path' => '/academico/planes-area',
            'slug' => 'academico-planes-area',
            'section' => 'academico',
        ],
        'academico.sistema-evaluacion' => [
            'label' => 'Sistema de Evaluación',
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
