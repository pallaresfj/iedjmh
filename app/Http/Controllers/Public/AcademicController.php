<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\AreaPlan;
use App\Models\Event;
use App\Models\Page;
use App\Models\Project;
use App\Support\PageMenuCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $definitions = $this->pageDefinitions();
        $bindings = collect($definitions)->pluck('menu_binding')->filter()->all();
        $publishedPagesByBinding = $this->publishedPagesByMenuBinding($bindings);
        $slugs = collect($definitions)->pluck('slug')->prepend('academico')->all();
        $publishedPagesBySlug = $this->publishedPagesBySlug($slugs);
        $landingPage = $publishedPagesBySlug->get('academico');

        $cards = collect($definitions)
            ->map(function (array $definition) use ($publishedPagesByBinding, $publishedPagesBySlug): array {
                /** @var Page|null $cmsPage */
                $cmsPage = null;
                $menuBinding = $definition['menu_binding'] ?? null;

                if (filled($menuBinding)) {
                    $cmsPage = $publishedPagesByBinding->get($menuBinding);
                }

                if (! $cmsPage) {
                    $cmsPage = $publishedPagesBySlug->get($definition['slug']);
                }

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
            'banner' => $this->resolvePageBanner($landingPage),
            'academicPages' => $this->navigationItems($definitions),
            'cards' => $cards,
        ]);
    }

    public function page(Request $request, string $pageKey): View
    {
        $definitions = $this->pageDefinitions();
        abort_unless(array_key_exists($pageKey, $definitions), 404);

        $definition = $definitions[$pageKey];
        $cmsPage = $this->publishedPageByBindingOrSlug($definition['menu_binding'] ?? null, $definition['slug']);
        $calendar = $pageKey === 'calendario-academico'
            ? $this->resolveAcademicCalendar($request)
            : [
                'events' => collect(),
                'months' => collect(),
                'filters' => [
                    'q' => '',
                    'month' => '',
                ],
            ];

        return view('public.academico.page', [
            'pageKey' => $pageKey,
            'title' => $cmsPage?->title ?: $definition['title'],
            'lead' => $cmsPage?->summary ?: $definition['summary'],
            'banner' => $this->resolvePageBanner($cmsPage),
            'blocks' => $this->resolveBlocks($cmsPage, $definition),
            'academicPages' => $this->navigationItems($definitions),
            'plans' => $pageKey === 'planes-area' ? $this->resolveAreaPlans() : collect(),
            'projects' => $pageKey === 'proyectos-pedagogicos' ? $this->resolvePedagogicalProjects() : collect(),
            'calendarEvents' => $calendar['events'],
            'calendarMonths' => $calendar['months'],
            'calendarFilters' => $calendar['filters'],
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
                'route' => PageMenuCatalog::routeFor('academico.niveles-educativos') ?: 'academico.niveles-educativos',
                'slug' => PageMenuCatalog::slugFor('academico.niveles-educativos') ?: 'academico-niveles-educativos',
                'menu_binding' => 'academico.niveles-educativos',
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
                'route' => PageMenuCatalog::routeFor('academico.modalidad-agropecuaria') ?: 'academico.modalidad-agropecuaria',
                'slug' => PageMenuCatalog::slugFor('academico.modalidad-agropecuaria') ?: 'academico-modalidad-agropecuaria',
                'menu_binding' => 'academico.modalidad-agropecuaria',
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
                'route' => PageMenuCatalog::routeFor('academico.planes-area') ?: 'academico.planes-area',
                'slug' => PageMenuCatalog::slugFor('academico.planes-area') ?: 'academico-planes-area',
                'menu_binding' => 'academico.planes-area',
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
                'route' => PageMenuCatalog::routeFor('academico.sistema-evaluacion') ?: 'academico.sistema-evaluacion',
                'slug' => PageMenuCatalog::slugFor('academico.sistema-evaluacion') ?: 'academico-sistema-evaluacion',
                'menu_binding' => 'academico.sistema-evaluacion',
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
                'menu_binding' => null,
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
                'menu_binding' => null,
                'summary' => 'Fechas institucionales relevantes, periodos academicos y actividades programadas.',
                'blocks' => [],
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
                    'is_html' => true,
                ],
            ]);
        }

        return collect($definition['blocks'])
            ->map(fn (array $block): array => [
                ...$block,
                'is_html' => false,
            ]);
    }

    /**
     * @return Collection<int, array{
     *     area_name: string,
     *     responsible_teachers: string,
     *     icon: string,
     *     plan_url: string
     * }>|LengthAwarePaginator<int, array{
     *     area_name: string,
     *     responsible_teachers: string,
     *     icon: string,
     *     plan_url: string
     * }>
     */
    private function resolveAreaPlans(): Collection|LengthAwarePaginator
    {
        if ($this->canQueryTable('area_plans')) {
            $plans = AreaPlan::query()
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->orderBy('area_name')
                ->paginate(5)
                ->withQueryString()
                ->through(function (AreaPlan $plan): array {
                    return [
                        'area_name' => $plan->area_name,
                        'responsible_teachers' => $plan->responsible_teachers,
                        'icon' => trim((string) $plan->icon) !== '' ? $plan->icon : 'menu_book',
                        'plan_url' => $plan->plan_url,
                    ];
                });

            if ($plans->isNotEmpty() || request()->query('page')) {
                return $plans;
            }
        }

        return collect();
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
    /**
     * @return array{
     *     events: Collection<int, array<string, string|null|bool>>|LengthAwarePaginator<int, array<string, string|null|bool>>,
     *     months: Collection<int, array{value: string, label: string}>,
     *     filters: array{q: string, month: string}
     * }
     */
    private function resolveAcademicCalendar(Request $request): array
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'month' => trim((string) $request->query('month', '')),
        ];

        if (preg_match('/^\d{4}\-(0[1-9]|1[0-2])$/', $filters['month']) !== 1) {
            $filters['month'] = '';
        }

        if ($this->canQueryTable('events')) {
            $baseQuery = Event::query()
                ->where('status', 'published')
                ->whereNotNull('starts_at')
                ->where('starts_at', '>=', now()->startOfDay());

            $months = (clone $baseQuery)
                ->orderBy('starts_at')
                ->get(['starts_at'])
                ->map(function (Event $event): array {
                    return [
                        'value' => $event->starts_at->format('Y-m'),
                        'label' => Str::title($event->starts_at->translatedFormat('F Y')),
                    ];
                })
                ->unique('value')
                ->values();

            $events = $baseQuery
                ->when($filters['q'] !== '', function ($query) use ($filters): void {
                    $searchTerm = '%'.$filters['q'].'%';

                    $query->where(function ($searchQuery) use ($searchTerm): void {
                        $searchQuery
                            ->where('title', 'like', $searchTerm)
                            ->orWhere('summary', 'like', $searchTerm)
                            ->orWhere('description', 'like', $searchTerm)
                            ->orWhere('location', 'like', $searchTerm);
                    });
                })
                ->when($filters['month'] !== '', function ($query) use ($filters): void {
                    [$year, $month] = explode('-', $filters['month']);
                    $query
                        ->whereYear('starts_at', (int) $year)
                        ->whereMonth('starts_at', (int) $month);
                })
                ->orderBy('starts_at')
                ->paginate(5)
                ->withQueryString()
                ->through(function (Event $event): array {
                    return [
                        'day' => $event->starts_at->format('d'),
                        'month' => Str::upper($event->starts_at->translatedFormat('M')),
                        'title' => $event->title,
                        'meta' => trim(collect([
                            $event->starts_at->format('h:i A'),
                            $event->location,
                        ])->filter()->join(' - ')),
                        'url' => route('eventos.show', ['slug' => $event->slug]),
                        'open_in_new_tab' => false,
                    ];
                });

            if ($events->isNotEmpty() || $filters['q'] !== '' || $filters['month'] !== '') {
                return [
                    'events' => $events,
                    'months' => $months,
                    'filters' => $filters,
                ];
            }
        }

        return [
            'events' => collect([
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
            ]),
            'months' => collect(),
            'filters' => $filters,
        ];
    }
}
