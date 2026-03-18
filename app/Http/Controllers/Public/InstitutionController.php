<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Campus;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $definitions = $this->pageDefinitions();
        $slugs = collect($definitions)->pluck('slug')->prepend('institucion')->all();
        $publishedPages = $this->publishedPagesBySlug($slugs);
        $landingPage = $publishedPages->get('institucion');

        $cards = collect($definitions)
            ->map(function (array $definition) use ($publishedPages): array {
                /** @var Page|null $cmsPage */
                $cmsPage = $publishedPages->get($definition['slug']);

                return [
                    'title' => $cmsPage?->title ?: $definition['title'],
                    'summary' => $cmsPage?->summary ?: $definition['summary'],
                    'route' => $definition['route'],
                ];
            })
            ->values();

        return view('public.institucion.index', [
            'title' => $landingPage?->title ?: 'Institucion',
            'lead' => $landingPage?->summary ?: 'Conoce nuestra historia, lineamientos institucionales, equipo de trabajo y servicios para la comunidad.',
            'institutionPages' => $this->navigationItems($definitions),
            'cards' => $cards,
        ]);
    }

    public function page(string $pageKey): View
    {
        $definitions = $this->pageDefinitions();
        abort_unless(array_key_exists($pageKey, $definitions), 404);

        $definition = $definitions[$pageKey];
        $cmsPage = $this->publishedPageBySlug($definition['slug']);

        return view('public.institucion.page', [
            'pageKey' => $pageKey,
            'title' => $cmsPage?->title ?: $definition['title'],
            'lead' => $cmsPage?->summary ?: $definition['summary'],
            'blocks' => $this->resolveBlocks($cmsPage, $definition),
            'campuses' => $pageKey === 'sedes' ? $this->resolveCampuses() : collect(),
            'institutionPages' => $this->navigationItems($definitions),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pageDefinitions(): array
    {
        return [
            'historia' => [
                'title' => 'Historia',
                'route' => 'institucion.historia',
                'slug' => 'institucion-historia',
                'summary' => 'Trayectoria institucional al servicio de la educacion en Pivijay, Magdalena.',
                'blocks' => [
                    [
                        'title' => 'Resena historica',
                        'body' => 'Nuestra institucion ha consolidado un proyecto educativo orientado a la formacion integral, con enfasis en valores, liderazgo y compromiso comunitario.',
                    ],
                ],
            ],
            'mision-vision' => [
                'title' => 'Mision y Vision',
                'route' => 'institucion.mision-vision',
                'slug' => 'institucion-mision-vision',
                'summary' => 'Direccion estrategica que orienta el desarrollo academico e institucional.',
                'blocks' => [
                    [
                        'title' => 'Mision',
                        'body' => 'Formar estudiantes con pensamiento critico, sensibilidad social y competencias para aportar al desarrollo sostenible del territorio.',
                    ],
                    [
                        'title' => 'Vision',
                        'body' => 'Ser una institucion referente en educacion agropecuaria y calidad academica, reconocida por su impacto positivo en la comunidad.',
                    ],
                ],
            ],
            'simbolos' => [
                'title' => 'Simbolos Institucionales',
                'route' => 'institucion.simbolos',
                'slug' => 'institucion-simbolos',
                'summary' => 'Elementos de identidad que representan nuestros principios institucionales.',
                'blocks' => [
                    [
                        'title' => 'Identidad',
                        'body' => 'Escudo, bandera e himno expresan la identidad de la institucion y fortalecen el sentido de pertenencia de toda la comunidad educativa.',
                    ],
                ],
            ],
            'equipo-directivo' => [
                'title' => 'Equipo Directivo',
                'route' => 'institucion.equipo-directivo',
                'slug' => 'institucion-equipo-directivo',
                'summary' => 'Equipo responsable de la gestion academica, administrativa y de convivencia.',
                'blocks' => [
                    [
                        'title' => 'Liderazgo institucional',
                        'body' => 'El equipo directivo orienta la toma de decisiones, el acompanamiento pedagogico y la articulacion con familias y actores del territorio.',
                    ],
                ],
            ],
            'sedes' => [
                'title' => 'Sedes',
                'route' => 'institucion.sedes',
                'slug' => 'institucion-sedes',
                'summary' => 'Informacion de nuestras sedes y puntos de atencion.',
                'blocks' => [
                    [
                        'title' => 'Cobertura institucional',
                        'body' => 'Consulta ubicacion, datos de contacto y caracteristicas de las sedes que hacen parte de la institucion.',
                    ],
                ],
            ],
            'pei' => [
                'title' => 'PEI',
                'route' => 'institucion.pei',
                'slug' => 'institucion-pei',
                'summary' => 'Proyecto Educativo Institucional y lineamientos pedagogicos.',
                'blocks' => [
                    [
                        'title' => 'Proyecto Educativo Institucional',
                        'body' => 'El PEI define nuestra propuesta pedagogica, principios formativos y estrategias de mejoramiento continuo.',
                    ],
                ],
            ],
            'manual-convivencia' => [
                'title' => 'Manual de Convivencia',
                'route' => 'institucion.manual-convivencia',
                'slug' => 'institucion-manual-convivencia',
                'summary' => 'Normas y acuerdos para la convivencia escolar y la formacion ciudadana.',
                'blocks' => [
                    [
                        'title' => 'Convivencia escolar',
                        'body' => 'Documento que orienta derechos, deberes y protocolos para fortalecer el respeto, la participacion y el bienestar escolar.',
                    ],
                ],
            ],
            'directorio' => [
                'title' => 'Directorio Institucional',
                'route' => 'institucion.directorio',
                'slug' => 'institucion-directorio',
                'summary' => 'Canales de contacto institucional para comunidad y ciudadanos.',
                'blocks' => [
                    [
                        'title' => 'Canales de atencion',
                        'body' => 'Encuentra datos de contacto de areas institucionales y orientacion para solicitudes de informacion.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return Collection<int, array<string, string>>
     */
    private function navigationItems(array $definitions): Collection
    {
        return collect($definitions)
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                'title' => $definition['title'],
                'route' => $definition['route'],
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return Collection<int, array<string, string|null>>
     */
    private function resolveBlocks(?Page $cmsPage, array $definition): Collection
    {
        if ($cmsPage && filled($cmsPage->content)) {
            return collect([
                [
                    'title' => null,
                    'body' => $cmsPage->content,
                ],
            ]);
        }

        return collect($definition['blocks']);
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function resolveCampuses(): Collection
    {
        if ($this->canQueryTable('campuses')) {
            $campuses = Campus::query()
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (Campus $campus): array {
                    return [
                        'name' => $campus->name,
                        'description' => $campus->description,
                        'address' => $campus->address,
                        'phone' => $campus->phone,
                        'email' => $campus->email,
                        'map_url' => ($campus->latitude && $campus->longitude)
                            ? "https://www.google.com/maps?q={$campus->latitude},{$campus->longitude}"
                            : null,
                    ];
                });

            if ($campuses->isNotEmpty()) {
                return $campuses;
            }
        }

        return collect([
            [
                'name' => 'Sede Principal',
                'description' => 'Sede administrativa y academica principal de la institucion.',
                'address' => config('institution.address'),
                'phone' => config('institution.phone'),
                'email' => config('institution.email'),
                'map_url' => null,
            ],
        ]);
    }
}
