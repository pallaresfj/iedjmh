<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Campus;
use App\Models\Page;
use App\Models\StaffMember;
use App\Support\PageMenuCatalog;
use App\Support\PublicSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $definitions = $this->pageDefinitions();
        $bindings = collect($definitions)->pluck('menu_binding')->filter()->all();
        $publishedPagesByBinding = $this->publishedPagesByMenuBinding($bindings);
        $slugs = collect($definitions)->pluck('slug')->prepend('institucion')->all();
        $publishedPagesBySlug = $this->publishedPagesBySlug($slugs);
        $landingPage = $publishedPagesBySlug->get('institucion');

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
                    'icon' => $definition['icon'] ?? 'article',
                ];
            })
            ->values();

        return view('public.institucion.index', [
            'title' => $landingPage?->title ?: 'Institución',
            'lead' => $landingPage?->summary ?: 'Conoce nuestra historia, lineamientos institucionales, equipo de trabajo y servicios para la comunidad.',
            'banner' => $this->resolvePageBanner($landingPage),
            'institutionPages' => $this->navigationItems($definitions),
            'cards' => $cards,
        ]);
    }

    public function page(Request $request, string $pageKey): View
    {
        $definitions = $this->pageDefinitions();
        abort_unless(array_key_exists($pageKey, $definitions), 404);

        $definition = $definitions[$pageKey];
        $cmsPage = $this->publishedPageByBindingOrSlug($definition['menu_binding'] ?? null, $definition['slug']);
        $title = $cmsPage?->title ?: $definition['title'];
        $lead = $cmsPage?->summary ?: $definition['summary'];
        $symbols = $pageKey === 'simbolos' ? PublicSettings::symbols() : [];

        $directiveDirectory = $pageKey === 'equipo-institucional'
            ? $this->resolveDirectiveStaffDirectory($request)
            : [
                'filters' => ['q' => '', 'campus' => ''],
                'campuses' => collect(),
                'members' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5),
                'has_active_filters' => false,
            ];

        return view('public.institucion.page', [
            'pageKey' => $pageKey,
            'title' => $title,
            'lead' => $lead,
            'banner' => $this->resolvePageBanner($cmsPage),
            'blocks' => $this->resolveBlocks($cmsPage, $definition),
            'campuses' => $pageKey === 'sedes' ? $this->resolveCampuses() : collect(),
            'staffFilters' => $directiveDirectory['filters'],
            'staffCampuses' => $directiveDirectory['campuses'],
            'directiveStaff' => $directiveDirectory['members'],
            'hasStaffActiveFilters' => $directiveDirectory['has_active_filters'],
            'institutionPages' => $this->navigationItems($definitions),
            'symbols' => $symbols,
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
                'route' => PageMenuCatalog::routeFor('institucion.historia') ?: 'institucion.historia',
                'slug' => PageMenuCatalog::slugFor('institucion.historia') ?: 'institucion-historia',
                'menu_binding' => 'institucion.historia',
                'icon' => 'history_edu',
                'summary' => 'Trayectoria institucional al servicio de la educación en Pivijay, Magdalena.',
                'blocks' => [
                    [
                        'title' => 'Reseña histórica',
                        'body' => 'Nuestra institución ha consolidado un proyecto educativo orientado a la formación integral, con énfasis en valores, liderazgo y compromiso comunitario.',
                    ],
                ],
            ],
            'mision-vision' => [
                'title' => 'Misión y Visión',
                'route' => PageMenuCatalog::routeFor('institucion.mision-vision') ?: 'institucion.mision-vision',
                'slug' => PageMenuCatalog::slugFor('institucion.mision-vision') ?: 'institucion-mision-vision',
                'menu_binding' => 'institucion.mision-vision',
                'icon' => 'visibility',
                'summary' => 'Dirección estratégica que orienta el desarrollo académico e institucional.',
                'blocks' => [
                    [
                        'title' => 'Misión',
                        'body' => 'Formar estudiantes con pensamiento crítico, sensibilidad social y competencias para aportar al desarrollo sostenible del territorio.',
                    ],
                    [
                        'title' => 'Visión',
                        'body' => 'Ser una institución referente en educación agropecuaria y calidad académica, reconocida por su impacto positivo en la comunidad.',
                    ],
                ],
            ],
            'simbolos' => [
                'title' => 'Símbolos Institucionales',
                'route' => PageMenuCatalog::routeFor('institucion.simbolos') ?: 'institucion.simbolos',
                'slug' => PageMenuCatalog::slugFor('institucion.simbolos') ?: 'institucion-simbolos',
                'menu_binding' => 'institucion.simbolos',
                'icon' => 'award_star',
                'summary' => 'Elementos de identidad que representan nuestros principios institucionales.',
                'blocks' => [
                    [
                        'title' => 'Identidad',
                        'body' => 'Escudo, bandera e himno expresan la identidad de la institución y fortalecen el sentido de pertenencia de toda la comunidad educativa.',
                    ],
                ],
            ],
            'equipo-institucional' => [
                'title' => 'Equipo Institucional',
                'route' => PageMenuCatalog::routeFor('institucion.equipo-institucional') ?: 'institucion.equipo-institucional',
                'slug' => PageMenuCatalog::slugFor('institucion.equipo-institucional') ?: 'institucion-equipo-institucional',
                'menu_binding' => 'institucion.equipo-institucional',
                'icon' => 'groups',
                'summary' => 'Equipo responsable de la gestión académica, administrativa y de convivencia.',
                'blocks' => [
                    [
                        'title' => 'Liderazgo institucional',
                        'body' => 'El equipo institucional orienta la toma de decisiones, el acompañamiento pedagógico y la articulación con familias y actores del territorio.',
                    ],
                ],
            ],
            'sedes' => [
                'title' => 'Sedes',
                'route' => 'institucion.sedes',
                'slug' => 'institucion-sedes',
                'menu_binding' => null,
                'icon' => 'location_on',
                'summary' => 'Información de nuestras sedes y puntos de atención.',
                'blocks' => [
                    [
                        'title' => 'Cobertura institucional',
                        'body' => 'Consulta ubicación, datos de contacto y características de las sedes que hacen parte de la institución.',
                    ],
                ],
            ],
            'pei' => [
                'title' => 'PEI',
                'route' => 'institucion.pei',
                'slug' => 'institucion-pei',
                'menu_binding' => null,
                'icon' => 'description',
                'summary' => 'Proyecto Educativo Institucional y lineamientos pedagógicos.',
                'blocks' => [
                    [
                        'title' => 'Proyecto Educativo Institucional',
                        'body' => 'El PEI define nuestra propuesta pedagógica, principios formativos y estrategias de mejoramiento continuo.',
                    ],
                ],
            ],
            'manual-convivencia' => [
                'title' => 'Manual de Convivencia',
                'route' => 'institucion.manual-convivencia',
                'slug' => 'institucion-manual-convivencia',
                'menu_binding' => null,
                'icon' => 'handshake',
                'summary' => 'Normas y acuerdos para la convivencia escolar y la formación ciudadana.',
                'blocks' => [
                    [
                        'title' => 'Convivencia escolar',
                        'body' => 'Documento que orienta derechos, deberes y protocolos para fortalecer el respeto, la participación y el bienestar escolar.',
                    ],
                ],
            ],
            'directorio' => [
                'title' => 'Directorio Institucional',
                'route' => 'institucion.directorio',
                'slug' => 'institucion-directorio',
                'menu_binding' => null,
                'icon' => 'contact_phone',
                'summary' => 'Canales de contacto institucional para comunidad y ciudadanos.',
                'blocks' => [
                    [
                        'title' => 'Canales de atención',
                        'body' => 'Encuentra datos de contacto de áreas institucionales y orientación para solicitudes de información.',
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

        $contact = PublicSettings::contact();

        return collect([
            [
                'name' => 'Sede Principal',
                'description' => 'Sede administrativa y académica principal de la institución.',
                'address' => $contact['address'],
                'phone' => $contact['phone'],
                'email' => $contact['email'],
                'map_url' => null,
            ],
        ]);
    }

    /**
     * @return array{
     *     filters: array{q: string, campus: string},
     *     campuses: Collection<int, array{slug: string, name: string}>,
     *     members: Collection<int, array{
     *         full_name: string,
     *         position_title: string,
     *         department_label: string|null,
     *         campus_name: string|null,
     *         institutional_email: string|null,
     *         phone: string|null,
     *         photo_url: string|null,
     *         initials: string,
     *         contact_url: string|null
     *     }>,
     *     has_active_filters: bool
     * }
     */
    private function resolveDirectiveStaffDirectory(Request $request): array
    {
        $hasCampusesTable = $this->canQueryTable('campuses');
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'campus' => trim((string) $request->query('campus', '')),
        ];

        $campuses = collect();

        if ($hasCampusesTable) {
            $campuses = Campus::query()
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'name'])
                ->map(fn (Campus $campus): array => [
                    'slug' => (string) $campus->slug,
                    'name' => $campus->name,
                ]);
        }

        $validCampusSlugs = $campuses->pluck('slug')->filter()->all();

        if ($filters['campus'] !== '' && ! in_array($filters['campus'], $validCampusSlugs, true)) {
            $filters['campus'] = '';
        }

        $members = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);

        if ($this->canQueryTable('staff_members')) {
            $query = StaffMember::query()
                ->published();

            if ($hasCampusesTable) {
                $query->with('campus');
            }

            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $staffQuery) use ($search): void {
                    $staffQuery
                        ->where('full_name', 'like', $search)
                        ->orWhere('position_title', 'like', $search)
                        ->orWhere('department_label', 'like', $search);
                });
            }

            if ($filters['campus'] !== '') {
                $query->whereHas('campus', function (Builder $campusQuery) use ($filters): void {
                    $campusQuery->where('slug', $filters['campus']);
                });
            }

            $paginator = $query
                ->orderBy('sort_order')
                ->orderBy('full_name')
                ->paginate(5)
                ->withQueryString();

            $members = $paginator->through(function (StaffMember $staffMember) use ($hasCampusesTable): array {
                return [
                    'full_name' => $staffMember->full_name,
                    'position_title' => $staffMember->position_title,
                    'department_label' => $staffMember->department_label,
                    'campus_name' => $hasCampusesTable ? $staffMember->campus?->name : null,
                    'institutional_email' => $staffMember->institutional_email,
                    'phone' => $staffMember->phone,
                    'photo_url' => $this->resolveMediaUrl($staffMember->profile_photo_path),
                    'initials' => $this->nameInitials($staffMember->full_name),
                    'contact_url' => $this->buildStaffContactUrl($staffMember),
                ];
            });
        }

        return [
            'filters' => $filters,
            'campuses' => $campuses,
            'members' => $members,
            'has_active_filters' => $filters['q'] !== '' || $filters['campus'] !== '',
        ];
    }

    private function nameInitials(string $fullName): string
    {
        $initials = collect(explode(' ', trim($fullName)))
            ->filter()
            ->take(2)
            ->map(fn (string $chunk): string => Str::upper(Str::substr($chunk, 0, 1)))
            ->join('');

        return $initials !== '' ? $initials : 'IP';
    }

    private function buildStaffContactUrl(StaffMember $staffMember): ?string
    {
        $email = trim((string) $staffMember->institutional_email);

        if ($email === '') {
            return null;
        }

        $subject = rawurlencode('Contacto institucional - '.$staffMember->full_name);

        return "mailto:{$email}?subject={$subject}";
    }
}
