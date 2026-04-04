<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Faq;
use App\Models\PqrsMessage;
use App\Models\PqrsRequest;
use App\Models\Procedure;
use App\Notifications\PqrsReceivedNotification;
use App\Support\Pqrs\TrackingCodeGenerator;
use App\Support\PublicSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CitizenAttentionController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $page = $this->publishedPageBySlug('atencion-ciudadano');
        $categories = $this->procedureCategories();

        $cards = [
            ['title' => 'Contáctenos', 'summary' => 'Canales de contacto institucional y horarios de atencion.', 'route' => 'atencion.contactenos', 'icon' => 'contact_phone'],
            ['title' => 'PQRS', 'summary' => 'Formulario para peticiones, quejas, reclamos y sugerencias con radicado.', 'route' => 'atencion.pqrs', 'icon' => 'inbox'],
            ['title' => 'Tramites y servicios', 'summary' => 'Consulta requisitos, canales y tiempos de respuesta.', 'route' => 'atencion.tramites', 'icon' => 'assignment'],
            ['title' => 'Preguntas frecuentes', 'summary' => 'Respuestas rapidas para orientacion ciudadana.', 'route' => 'atencion.faq', 'icon' => 'quiz'],
            ['title' => 'Mapa del sitio', 'summary' => 'Estructura completa de navegacion del portal.', 'route' => 'atencion.mapa-sitio', 'icon' => 'account_tree'],
            ['title' => 'Participacion', 'summary' => 'Mecanismos para la participacion ciudadana.', 'route' => 'atencion.participacion', 'icon' => 'groups'],
        ];

        return view('public.atencion.index', [
            'title' => $page?->title ?: 'Atención al Ciudadano',
            'lead' => $page?->summary ?: 'Canales institucionales de contacto, PQRS, trámites, participación y orientación ciudadana.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'cards' => $cards,
            'procedureCategories' => $categories,
            'featuredFaqs' => $this->featuredFaqs(),
        ]);
    }

    public function contact(): View
    {
        $page = $this->publishedPageBySlug('atencion-contactenos');

        return view('public.atencion.contacto', [
            'title' => $page?->title ?: 'Contáctenos',
            'lead' => $page?->summary ?: 'Consulta nuestros canales de atención institucional y horarios de servicio.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'contact' => PublicSettings::contact(),
        ]);
    }

    public function pqrs(): View
    {
        $page = $this->publishedPageBySlug('atencion-pqrs');

        return view('public.atencion.pqrs', [
            'title' => $page?->title ?: 'PQRS',
            'lead' => $page?->summary ?: 'Radica peticiones, quejas, reclamos, sugerencias, felicitaciones o trámites.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'typeOptions' => [
                'peticion' => 'Peticion',
                'queja' => 'Queja',
                'reclamo' => 'Reclamo',
                'sugerencia' => 'Sugerencia',
                'felicitacion' => 'Felicitacion',
                'tramite' => 'Tramite',
            ],
        ]);
    }

    public function submitPqrs(Request $request, TrackingCodeGenerator $trackingCodeGenerator): RedirectResponse
    {
        abort_unless($this->canQueryTable('pqrs_requests'), 404);

        $validated = $request->validate([
            'type' => ['required', 'in:peticion,queja,reclamo,sugerencia,felicitacion,tramite'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'origin' => ['nullable', 'in:contact,pqrs'],
            'website' => ['nullable', 'string', 'max:0'],
            'is_anonymous' => ['nullable', 'boolean'],
            'applicant_name' => ['required_unless:is_anonymous,1', 'nullable', 'string', 'max:255'],
            'applicant_email' => ['required', 'email:rfc', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:80'],
            'applicant_document' => ['nullable', 'string', 'max:120'],
            'applicant_address' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,docx', 'max:2048'],
            'consent_habeas_data' => ['accepted'],
        ], [
            'consent_habeas_data.accepted' => 'Debes aceptar el tratamiento de datos personales para continuar.',
        ]);

        $isAnonymous = (bool) ($validated['is_anonymous'] ?? false);
        $trackingCode = $trackingCodeGenerator->generate();
        $attachmentPath = $request->file('attachment')?->store('pqrs-attachments', 'local');

        $pqrs = PqrsRequest::query()->create([
            'tracking_code' => $trackingCode,
            'type' => $validated['type'],
            'is_anonymous' => $isAnonymous,
            'status' => 'received',
            'priority' => 'medium',
            'attachment_path' => $attachmentPath,
            'message' => $validated['message'],
            'applicant_name' => $isAnonymous ? null : ($validated['applicant_name'] ?? null),
            'applicant_email' => $validated['applicant_email'],
            'applicant_phone' => $isAnonymous ? null : ($validated['applicant_phone'] ?? null),
            'applicant_document' => $isAnonymous ? null : ($validated['applicant_document'] ?? null),
            'applicant_address' => $isAnonymous ? null : ($validated['applicant_address'] ?? null),
            'consent_habeas_data' => true,
            'submitted_at' => now(),
        ]);

        if ($this->canQueryTable('pqrs_messages')) {
            PqrsMessage::query()->create([
                'pqrs_request_id' => $pqrs->id,
                'author_name' => $pqrs->is_anonymous ? 'Anonimo' : $pqrs->applicant_name,
                'author_email' => $pqrs->applicant_email,
                'message' => $pqrs->message,
                'is_internal' => false,
            ]);
        }

        $pqrs->notify(new PqrsReceivedNotification($pqrs));

        $redirectRoute = ($validated['origin'] ?? null) === 'contact'
            ? 'atencion.contactenos'
            : 'atencion.pqrs';

        return redirect()
            ->route($redirectRoute)
            ->with('pqrs_success', "Solicitud radicada correctamente. Codigo de seguimiento: {$trackingCode}");
    }

    public function trackPqrs(): View
    {
        $page = $this->publishedPageBySlug('atencion-pqrs');

        return view('public.atencion.pqrs-consulta', [
            'title' => 'Consulta PQRS',
            'lead' => 'Ingresa tu código de seguimiento para conocer el estado de tu solicitud.',
            'attentionPages' => $this->attentionPages(),
            'pqrs' => null,
            'messages' => collect(),
        ]);
    }

    public function showPqrsStatus(Request $request): View
    {
        $page = $this->publishedPageBySlug('atencion-pqrs');

        $validated = $request->validate([
            'tracking_code' => ['required', 'string', 'max:50'],
            'applicant_email' => ['required', 'email', 'max:255'],
        ]);

        $pqrs = null;
        $messages = collect();

        if ($this->canQueryTable('pqrs_requests')) {
            $pqrs = PqrsRequest::query()
                ->where('tracking_code', $validated['tracking_code'])
                ->where('applicant_email', $validated['applicant_email'])
                ->first();

            if ($pqrs && $this->canQueryTable('pqrs_messages')) {
                $messages = $pqrs->messages()
                    ->where('is_internal', false)
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn (PqrsMessage $msg): array => [
                        'author' => $msg->author_name ?? 'Institucion',
                        'message' => $msg->message,
                        'date' => $msg->created_at?->translatedFormat('d M Y H:i'),
                    ]);
            }
        }

        return view('public.atencion.pqrs-consulta', [
            'title' => 'Consulta PQRS',
            'lead' => 'Ingresa tu código de seguimiento para conocer el estado de tu solicitud.',
            'attentionPages' => $this->attentionPages(),
            'pqrs' => $pqrs,
            'messages' => $messages,
            'trackingCode' => $validated['tracking_code'],
            'applicantEmail' => $validated['applicant_email'],
        ]);
    }

    public function procedures(Request $request): View
    {
        $page = $this->publishedPageBySlug('atencion-tramites-servicios');
        $categories = $this->procedureCategories();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'online' => trim((string) $request->query('online', '')),
        ];

        $items = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 10,
            currentPage: max(1, (int) $request->query('page', 1)),
            options: [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        if ($this->canQueryTable('procedures')) {
            $query = Procedure::query()
                ->with('category')
                ->where('status', 'published');

            if ($filters['category'] !== '') {
                $query->whereHas('category', function (Builder $categoryQuery) use ($filters): void {
                    $categoryQuery->where('slug', $filters['category']);
                });
            }

            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', $search)
                        ->orWhere('summary', 'like', $search)
                        ->orWhere('requirements', 'like', $search);
                });
            }

            if (in_array($filters['online'], ['1', '0'], true)) {
                $query->where('is_online', $filters['online'] === '1');
            }

            $items = $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Procedure $procedure): array => [
                    'name' => $procedure->name,
                    'slug' => $procedure->slug,
                    'summary' => $procedure->summary,
                    'requirements' => $procedure->requirements,
                    'response_time' => $procedure->response_time,
                    'cost' => $procedure->cost,
                    'channel' => $procedure->channel,
                    'is_online' => $procedure->is_online,
                    'application_url' => $procedure->application_url,
                    'contact_email' => $procedure->contact_email,
                    'contact_phone' => $procedure->contact_phone,
                    'category' => $procedure->category?->name,
                    'published_at' => $procedure->published_at?->translatedFormat('d M Y'),
                    'updated_at' => $procedure->updated_at?->translatedFormat('d M Y H:i'),
                ]);
        }

        return view('public.atencion.tramites', [
            'title' => $page?->title ?: 'Trámites y Servicios',
            'lead' => $page?->summary ?: 'Consulta requisitos, costos, canales y tiempos de respuesta de trámites institucionales.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'categories' => $categories,
            'filters' => $filters,
            'items' => $items,
        ]);
    }

    public function faqs(Request $request): View
    {
        $page = $this->publishedPageBySlug('atencion-preguntas-frecuentes');
        $categories = $this->faqCategories();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
        ];

        $items = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 12,
            currentPage: max(1, (int) $request->query('page', 1)),
            options: [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        if ($this->canQueryTable('faqs')) {
            $query = Faq::query()
                ->with('category')
                ->where('status', 'published');

            if ($filters['category'] !== '') {
                $query->whereHas('category', function (Builder $categoryQuery) use ($filters): void {
                    $categoryQuery->where('slug', $filters['category']);
                });
            }

            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('question', 'like', $search)
                        ->orWhere('answer', 'like', $search);
                });
            }

            $items = $query
                ->orderBy('sort_order')
                ->orderBy('question')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Faq $faq): array => [
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'category' => $faq->category?->name,
                    'published_at' => $faq->published_at?->translatedFormat('d M Y'),
                    'updated_at' => $faq->updated_at?->translatedFormat('d M Y H:i'),
                ]);
        }

        return view('public.atencion.faq', [
            'title' => $page?->title ?: 'Preguntas Frecuentes',
            'lead' => $page?->summary ?: 'Respuestas rápidas a consultas comunes de estudiantes, familias y ciudadanos.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'categories' => $categories,
            'filters' => $filters,
            'items' => $items,
        ]);
    }

    public function sitemap(): View
    {
        $page = $this->publishedPageBySlug('atencion-mapa-sitio');

        return view('public.atencion.mapa-sitio', [
            'title' => $page?->title ?: 'Mapa del Sitio',
            'lead' => $page?->summary ?: 'Estructura de navegacion del portal institucional para consulta rapida.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
            'sitemap' => $this->sitemapSections(),
        ]);
    }

    public function participation(): View
    {
        $page = $this->publishedPageBySlug('atencion-participacion');

        return view('public.atencion.participacion', [
            'title' => $page?->title ?: 'Participacion',
            'lead' => $page?->summary ?: 'Canales de participacion ciudadana y mecanismos de interlocucion con la institucion.',
            'content' => $page?->content,
            'attentionPages' => $this->attentionPages(),
        ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function attentionPages(): Collection
    {
        return collect([
            ['title' => 'Landing Atencion', 'route' => 'atencion.index'],
            ['title' => 'Contáctenos', 'route' => 'atencion.contactenos'],
            ['title' => 'PQRS', 'route' => 'atencion.pqrs'],
            ['title' => 'Trámites y servicios', 'route' => 'atencion.tramites'],
            ['title' => 'Preguntas frecuentes', 'route' => 'atencion.faq'],
            ['title' => 'Mapa del sitio', 'route' => 'atencion.mapa-sitio'],
            ['title' => 'Participacion', 'route' => 'atencion.participacion'],
            ['title' => 'Portal de Egresados', 'route' => 'egresados.index'],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function procedureCategories(): Collection
    {
        if (! $this->canQueryTable('categories') || ! $this->canQueryTable('procedures')) {
            return collect();
        }

        return Category::query()
            ->where('status', 'published')
            ->whereHas('procedures', function (Builder $query): void {
                $query->where('status', 'published');
            })
            ->withCount([
                'procedures as published_count' => function (Builder $query): void {
                    $query->where('status', 'published');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) $category->published_count,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function faqCategories(): Collection
    {
        if (! $this->canQueryTable('categories') || ! $this->canQueryTable('faqs')) {
            return collect();
        }

        return Category::query()
            ->where('status', 'published')
            ->whereHas('faqs', function (Builder $query): void {
                $query->where('status', 'published');
            })
            ->withCount([
                'faqs as published_count' => function (Builder $query): void {
                    $query->where('status', 'published');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) $category->published_count,
            ]);
    }

    /**
     * @return Collection<int, array<string, string|null>>
     */
    private function featuredFaqs(): Collection
    {
        if (! $this->canQueryTable('faqs')) {
            return collect();
        }

        return Faq::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get()
            ->map(fn (Faq $faq): array => [
                'question' => $faq->question,
                'answer' => Str::limit(strip_tags((string) $faq->answer), 180),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function sitemapSections(): Collection
    {
        return collect([
            [
                'title' => 'Inicio',
                'icon' => 'home',
                'items' => [
                    ['label' => 'Inicio', 'route' => 'home'],
                ],
            ],
            [
                'title' => 'Institucion',
                'icon' => 'apartment',
                'items' => [
                    ['label' => 'Institucion', 'route' => 'institucion.index'],
                    ['label' => 'Historia', 'route' => 'institucion.historia'],
                    ['label' => 'Mision y Vision', 'route' => 'institucion.mision-vision'],
                    ['label' => 'Simbolos', 'route' => 'institucion.simbolos'],
                    ['label' => 'Equipo Institucional', 'route' => 'institucion.equipo-institucional'],
                    ['label' => 'Sedes', 'route' => 'institucion.sedes'],
                    ['label' => 'PEI', 'route' => 'institucion.pei'],
                    ['label' => 'Manual de Convivencia', 'route' => 'institucion.manual-convivencia'],
                ],
            ],
            [
                'title' => 'Academico',
                'icon' => 'school',
                'items' => [
                    ['label' => 'Academico', 'route' => 'academico.index'],
                    ['label' => 'Niveles Educativos', 'route' => 'academico.niveles-educativos'],
                    ['label' => PublicSettings::academicModality()['label'], 'route' => 'academico.modalidad'],
                    ['label' => 'Planes de Area', 'route' => 'academico.planes-area'],
                    ['label' => 'Sistema de Evaluacion', 'route' => 'academico.sistema-evaluacion'],
                    ['label' => 'Proyectos Pedagogicos', 'route' => 'academico.proyectos-pedagogicos'],
                    ['label' => 'Calendario Academico', 'route' => 'academico.calendario-academico'],
                ],
            ],
            [
                'title' => 'Noticias y Transparencia',
                'icon' => 'newspaper',
                'items' => [
                    ['label' => 'Noticias', 'route' => 'noticias.index'],
                    ['label' => 'Transparencia', 'route' => 'transparencia.index'],
                    ['label' => 'Documentos de Transparencia', 'route' => 'transparencia.documentos'],
                ],
            ],
            [
                'title' => 'Atención al Ciudadano',
                'icon' => 'support_agent',
                'items' => $this->attentionPages()->map(fn (array $item): array => [
                    'label' => $item['title'],
                    'route' => $item['route'],
                ])->all(),
            ],
        ]);
    }
}
