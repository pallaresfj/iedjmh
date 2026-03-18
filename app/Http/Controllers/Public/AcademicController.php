<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $definitions = $this->pageDefinitions();
        $slugs = collect($definitions)->pluck('slug')->prepend('academico')->all();
        $publishedPages = $this->publishedPagesBySlug($slugs);
        $landingPage = $publishedPages->get('academico');

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

        return view('public.academico.index', [
            'title' => $landingPage?->title ?: 'Academico',
            'lead' => $landingPage?->summary ?: 'Informacion curricular, recursos pedagogicos y servicios academicos para estudiantes y familias.',
            'academicPages' => $this->navigationItems($definitions),
            'cards' => $cards,
        ]);
    }

    public function page(string $pageKey): View
    {
        $definitions = $this->pageDefinitions();
        abort_unless(array_key_exists($pageKey, $definitions), 404);

        $definition = $definitions[$pageKey];
        $cmsPage = $this->publishedPageBySlug($definition['slug']);

        return view('public.academico.page', [
            'pageKey' => $pageKey,
            'title' => $cmsPage?->title ?: $definition['title'],
            'lead' => $cmsPage?->summary ?: $definition['summary'],
            'blocks' => $this->resolveBlocks($cmsPage, $definition),
            'academicPages' => $this->navigationItems($definitions),
            'plans' => $pageKey === 'planes-area' ? $this->resolveAreaPlans() : collect(),
            'projects' => $pageKey === 'proyectos-pedagogicos' ? $this->resolvePedagogicalProjects() : collect(),
            'calendarEvents' => $pageKey === 'calendario-academico' ? $this->resolveAcademicCalendar() : collect(),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pageDefinitions(): array
    {
        return [
            'niveles-educativos' => [
                'title' => 'Niveles Educativos',
                'route' => 'academico.niveles-educativos',
                'slug' => 'academico-niveles-educativos',
                'summary' => 'Oferta educativa desde preescolar hasta media con enfoque integral.',
                'blocks' => [
                    [
                        'title' => 'Preescolar, primaria y bachillerato',
                        'body' => 'La institucion ofrece atencion educativa por ciclos, fortaleciendo competencias basicas, ciudadanas y tecnico-productivas en cada nivel.',
                    ],
                ],
            ],
            'modalidad-agropecuaria' => [
                'title' => 'Modalidad Agropecuaria',
                'route' => 'academico.modalidad-agropecuaria',
                'slug' => 'academico-modalidad-agropecuaria',
                'summary' => 'Formacion tecnica articulada con el contexto rural y productivo del territorio.',
                'blocks' => [
                    [
                        'title' => 'Enfoque tecnico',
                        'body' => 'La modalidad agropecuaria integra teoria y practica para desarrollar capacidades en produccion sostenible, emprendimiento y cuidado ambiental.',
                    ],
                ],
            ],
            'planes-area' => [
                'title' => 'Planes de Area',
                'route' => 'academico.planes-area',
                'slug' => 'academico-planes-area',
                'summary' => 'Consulta de planes curriculares, mallas y orientaciones por area.',
                'blocks' => [
                    [
                        'title' => 'Documentacion curricular',
                        'body' => 'Publicaciones oficiales de referencia para docentes, estudiantes y familias. Esta informacion se administra desde el CMS.',
                    ],
                ],
            ],
            'sistema-evaluacion' => [
                'title' => 'Sistema de Evaluacion',
                'route' => 'academico.sistema-evaluacion',
                'slug' => 'academico-sistema-evaluacion',
                'summary' => 'Criterios y procesos para valorar el aprendizaje y acompanamiento estudiantil.',
                'blocks' => [
                    [
                        'title' => 'Evaluacion formativa',
                        'body' => 'El sistema de evaluacion institucional define criterios de desempeno, estrategias de seguimiento y rutas de mejoramiento academico.',
                    ],
                ],
            ],
            'proyectos-pedagogicos' => [
                'title' => 'Proyectos Pedagogicos',
                'route' => 'academico.proyectos-pedagogicos',
                'slug' => 'academico-proyectos-pedagogicos',
                'summary' => 'Iniciativas de aula y de institucion para fortalecer aprendizaje significativo.',
                'blocks' => [
                    [
                        'title' => 'Aprendizaje basado en proyectos',
                        'body' => 'Conoce proyectos pedagogicos que articulan competencias academicas, investigacion y solucion de problemas del entorno.',
                    ],
                ],
            ],
            'calendario-academico' => [
                'title' => 'Calendario Academico',
                'route' => 'academico.calendario-academico',
                'slug' => 'academico-calendario-academico',
                'summary' => 'Fechas institucionales relevantes, periodos academicos y actividades programadas.',
                'blocks' => [
                    [
                        'title' => 'Agenda institucional',
                        'body' => 'Consulta eventos y actividades academicas en orden cronologico para la planeacion escolar.',
                    ],
                ],
            ],
            'zona-academica' => [
                'title' => 'Acceso a Zona Academica',
                'route' => 'academico.zona-academica',
                'slug' => 'academico-zona-academica',
                'summary' => 'Punto de acceso a plataformas institucionales de seguimiento academico.',
                'blocks' => [
                    [
                        'title' => 'Plataformas y servicios',
                        'body' => 'Desde este espacio puedes ingresar a los servicios de consulta academica y acompanamiento estudiantil.',
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
    private function resolveAreaPlans(): Collection
    {
        if ($this->canQueryTable('documents')) {
            $documents = Document::query()
                ->where('status', 'published')
                ->where(function ($query): void {
                    $query->whereHas('categories', function ($categoryQuery): void {
                        $categoryQuery->whereIn('slug', ['planes-de-area', 'academico-planes-area']);
                    })->orWhereDoesntHave('categories');
                })
                ->orderByDesc('document_date')
                ->orderByDesc('published_at')
                ->limit(8)
                ->get()
                ->map(function (Document $document): array {
                    return [
                        'title' => $document->title,
                        'summary' => $document->summary ?: Str::limit(strip_tags((string) $document->description), 140),
                        'date' => $document->document_date?->translatedFormat('d M Y'),
                        'number' => $document->document_number,
                        'url' => $this->resolveDocumentUrl($document),
                    ];
                });

            if ($documents->isNotEmpty()) {
                return $documents;
            }
        }

        return collect([
            [
                'title' => 'Plan de area - Ciencias Naturales',
                'summary' => 'Orientaciones curriculares, competencias y actividades por grado.',
                'date' => null,
                'number' => null,
                'url' => null,
            ],
            [
                'title' => 'Plan de area - Matematicas',
                'summary' => 'Referentes de aprendizaje y rutas metodologicas institucionales.',
                'date' => null,
                'number' => null,
                'url' => null,
            ],
        ]);
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function resolvePedagogicalProjects(): Collection
    {
        if ($this->canQueryTable('projects')) {
            $projects = Project::query()
                ->where('status', 'published')
                ->where(function ($query): void {
                    $query->whereHas('categories', function ($categoryQuery): void {
                        $categoryQuery->whereIn('slug', ['proyectos-pedagogicos', 'academico-proyectos-pedagogicos']);
                    })->orWhereDoesntHave('categories');
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest('published_at')
                ->limit(6)
                ->get()
                ->map(function (Project $project): array {
                    $period = trim(collect([
                        $project->starts_on?->translatedFormat('M Y'),
                        $project->ends_on?->translatedFormat('M Y'),
                    ])->filter()->join(' - '));

                    return [
                        'title' => $project->title,
                        'summary' => $project->summary ?: Str::limit(strip_tags((string) $project->description), 140),
                        'period' => $period ?: null,
                        'image_url' => $this->resolveMediaUrl($project->cover_image_path),
                    ];
                });

            if ($projects->isNotEmpty()) {
                return $projects;
            }
        }

        return collect([
            [
                'title' => 'Semillero de investigacion escolar',
                'summary' => 'Proyecto interdisciplinar para fortalecer pensamiento cientifico en estudiantes.',
                'period' => null,
                'image_url' => null,
            ],
            [
                'title' => 'Huerta pedagogica y sostenibilidad',
                'summary' => 'Estrategia de aprendizaje practico sobre produccion limpia y seguridad alimentaria.',
                'period' => null,
                'image_url' => null,
            ],
        ]);
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function resolveAcademicCalendar(): Collection
    {
        if ($this->canQueryTable('events')) {
            $events = Event::query()
                ->where('status', 'published')
                ->whereNotNull('starts_at')
                ->where(function ($query): void {
                    $query->whereHas('categories', function ($categoryQuery): void {
                        $categoryQuery->whereIn('slug', ['calendario-academico', 'academico-calendario-academico']);
                    })->orWhereDoesntHave('categories');
                })
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(12)
                ->get()
                ->map(function (Event $event): array {
                    return [
                        'day' => $event->starts_at->format('d'),
                        'month' => Str::upper($event->starts_at->translatedFormat('M')),
                        'title' => $event->title,
                        'meta' => trim(collect([
                            $event->starts_at->format('h:i A'),
                            $event->location,
                        ])->filter()->join(' - ')),
                        'url' => $event->registration_url,
                    ];
                });

            if ($events->isNotEmpty()) {
                return $events;
            }
        }

        return collect([
            [
                'day' => '02',
                'month' => 'ABR',
                'title' => 'Inicio de periodo academico',
                'meta' => '07:00 AM - Sede principal',
                'url' => null,
            ],
            [
                'day' => '18',
                'month' => 'MAY',
                'title' => 'Corte evaluativo intermedio',
                'meta' => 'Jornada academica',
                'url' => null,
            ],
            [
                'day' => '10',
                'month' => 'JUN',
                'title' => 'Entrega de informes de desempeno',
                'meta' => 'Atencion a familias',
                'url' => null,
            ],
        ]);
    }

    private function resolveDocumentUrl(Document $document): ?string
    {
        if ($document->external_url) {
            return $document->external_url;
        }

        if (! $document->file_path) {
            return null;
        }

        return $this->resolveMediaUrl($document->file_path);
    }
}
