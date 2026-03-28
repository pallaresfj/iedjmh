<?php

namespace App\Support\Dashboard;

use App\Filament\Resources\Contracts\ContractResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Post;
use App\Models\PqrsRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Throwable;

class AdminDashboardData
{
    /**
     * @var array<int, string>
     */
    private const PQRS_CLOSED_STATUSES = [
        'resolved',
        'closed',
        'resuelto',
        'cerrado',
        'finalizado',
    ];

    private CarbonImmutable $now;

    public function __construct()
    {
        $this->now = CarbonImmutable::now('America/Bogota');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function kpis(): array
    {
        $publishedContractsQuery = Contract::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $this->now);

        $pendingPqrs = PqrsRequest::query()
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('resolved_at')
                    ->orWhereNotIn('status', self::PQRS_CLOSED_STATUSES);
            })
            ->count();

        $eventsThisWeek = Event::query()
            ->where('status', 'published')
            ->whereNotNull('starts_at')
            ->whereBetween('starts_at', [
                $this->now->startOfWeek(),
                $this->now->endOfWeek(),
            ])
            ->count();

        $publishedPosts = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $this->now)
            ->count();

        $activeContracts = (clone $publishedContractsQuery)
            ->whereIn('process_status', ['en_curso', 'adjudicado'])
            ->count();

        return [
            [
                'label' => 'Pendientes',
                'value' => (int) $pendingPqrs,
                'description' => 'PQRS pendientes',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'badge_class' => 'agro-badge agro-badge--emerald',
                'url' => $this->resourceIndexUrl(PqrsRequestResource::class, 'filament.admin.resources.pqrs-requests.index'),
            ],
            [
                'label' => 'Esta semana',
                'value' => (int) $eventsThisWeek,
                'description' => 'Eventos esta semana',
                'icon' => 'heroicon-o-calendar-days',
                'badge_class' => 'agro-badge agro-badge--blue',
                'url' => $this->resourceIndexUrl(EventResource::class, 'filament.admin.resources.events.index'),
            ],
            [
                'label' => 'Publicadas',
                'value' => (int) $publishedPosts,
                'description' => 'Noticias publicadas',
                'icon' => 'heroicon-o-newspaper',
                'badge_class' => 'agro-badge agro-badge--amber',
                'url' => $this->resourceIndexUrl(PostResource::class, 'filament.admin.resources.posts.index'),
            ],
            [
                'label' => 'Activos',
                'value' => (int) $activeContracts,
                'description' => 'Contratos activos',
                'icon' => 'heroicon-o-document-text',
                'badge_class' => 'agro-badge agro-badge--light',
                'highlight' => true,
                'url' => $this->resourceIndexUrl(ContractResource::class, 'filament.admin.resources.contracts.index'),
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentNews(int $limit = 5): Collection
    {
        return Post::query()
            ->with('creator')
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (Post $post): array {
                $publishedAt = $post->published_at?->timezone('America/Bogota');
                $fallbackDate = $post->updated_at?->timezone('America/Bogota');
                $statusLabel = $this->contentStatusLabel((string) $post->status);

                return [
                    'title' => $post->title,
                    'date' => $publishedAt?->format('d M, Y') ?? $fallbackDate?->format('d M, Y') ?? '-',
                    'author' => $post->creator?->name ?? 'Sin autor',
                    'status_label' => $statusLabel,
                    'status_class' => $this->contentStatusClass((string) $post->status),
                    'edit_url' => $this->resourceEditUrl(
                        PostResource::class,
                        $post,
                        'filament.admin.resources.posts.edit',
                        ['record' => $post],
                    ),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentPqrs(int $limit = 4): Collection
    {
        return PqrsRequest::query()
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (PqrsRequest $request): array {
                $status = $this->pqrsStatusData((string) $request->status);
                $submittedAt = $request->submitted_at?->timezone('America/Bogota');

                return [
                    'subject' => $request->subject,
                    'applicant' => $request->applicant_name,
                    'submitted_at' => $submittedAt?->format('d/m/Y H:i') ?? 'Sin fecha',
                    'status_label' => $status['label'],
                    'status_badge_class' => $status['badge_class'],
                    'stripe_class' => $status['stripe_class'],
                    'record_url' => $this->pqrsRecordUrl($request),
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function contractingStatus(): array
    {
        $year = (int) $this->now->year;

        $baseQuery = Contract::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $this->now)
            ->where('fiscal_year', $year);

        $inProgress = (clone $baseQuery)
            ->where('process_status', 'en_curso')
            ->count();

        $awarded = (clone $baseQuery)
            ->where('process_status', 'adjudicado')
            ->count();

        $finalized = (clone $baseQuery)
            ->where('process_status', 'finalizado')
            ->count();

        $total = (clone $baseQuery)->count();
        $completed = $awarded + $finalized;
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'year' => $year,
            'total' => (int) $total,
            'progress' => max(0, min(100, $progress)),
            'in_progress' => (int) $inProgress,
            'awarded' => (int) $awarded,
            'finalized' => (int) $finalized,
            'index_url' => $this->resourceIndexUrl(ContractResource::class, 'filament.admin.resources.contracts.index'),
            'create_url' => $this->resourceCreateUrl(ContractResource::class, 'filament.admin.resources.contracts.create'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function upcomingEvents(int $limit = 4): Collection
    {
        return Event::query()
            ->where('status', 'published')
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', $this->now)
            ->orderBy('starts_at')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                $startsAt = $event->starts_at?->timezone('America/Bogota');
                $month = $startsAt ? mb_strtoupper($startsAt->translatedFormat('M')) : '--';
                $timeLabel = $event->is_all_day ? 'Todo el dia' : ($startsAt?->format('h:i A') ?? '--:--');
                $details = collect([$timeLabel, $event->location])
                    ->filter(fn (?string $value): bool => filled($value))
                    ->implode(' • ');

                return [
                    'title' => $event->title,
                    'month' => $month,
                    'day' => $startsAt?->format('d') ?? '--',
                    'details' => $details !== '' ? $details : 'Sin detalles',
                    'edit_url' => $this->resourceEditUrl(
                        EventResource::class,
                        $event,
                        'filament.admin.resources.events.edit',
                        ['record' => $event],
                    ),
                ];
            });
    }

    public function eventsIndexUrl(): ?string
    {
        return $this->resourceIndexUrl(EventResource::class, 'filament.admin.resources.events.index');
    }

    public function eventsCreateUrl(): ?string
    {
        return $this->resourceCreateUrl(EventResource::class, 'filament.admin.resources.events.create');
    }

    public function postsIndexUrl(): ?string
    {
        return $this->resourceIndexUrl(PostResource::class, 'filament.admin.resources.posts.index');
    }

    public function postsCreateUrl(): ?string
    {
        return $this->resourceCreateUrl(PostResource::class, 'filament.admin.resources.posts.create');
    }

    /**
     * @return array{count: int, items: Collection<int, array<string, mixed>>, index_url: ?string}
     */
    public function pendingNewsModeration(int $limit = 5): array
    {
        $baseQuery = Post::query()
            ->with('creator')
            ->where('status', 'draft')
            ->whereHas('creator.roles', function (Builder $query): void {
                $query->where('name', 'colaborador');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $count = (clone $baseQuery)->count();

        $items = (clone $baseQuery)
            ->limit($limit)
            ->get()
            ->map(function (Post $post): array {
                $createdAt = $post->created_at?->timezone('America/Bogota');

                return [
                    'title' => $post->title,
                    'author' => $post->creator?->name ?? 'Sin autor',
                    'created_at' => $createdAt?->format('d/m/Y H:i') ?? 'Sin fecha',
                    'edit_url' => $this->resourceEditUrl(
                        PostResource::class,
                        $post,
                        'filament.admin.resources.posts.edit',
                        ['record' => $post],
                    ),
                ];
            });

        return [
            'count' => (int) $count,
            'items' => $items,
            'index_url' => $this->postsIndexUrl(),
        ];
    }

    private function contentStatusLabel(string $status): string
    {
        return match ($status) {
            'published' => 'Publicado',
            'archived' => 'Archivado',
            default => 'Borrador',
        };
    }

    private function contentStatusClass(string $status): string
    {
        return match ($status) {
            'published' => 'agro-status agro-status--published',
            'archived' => 'agro-status agro-status--archived',
            default => 'agro-status agro-status--draft',
        };
    }

    private function pqrsRecordUrl(PqrsRequest $request): ?string
    {
        $editUrl = $this->resourceEditUrl(
            PqrsRequestResource::class,
            $request,
            'filament.admin.resources.pqrs-requests.edit',
            ['record' => $request],
        );

        if (filled($editUrl)) {
            return $editUrl;
        }

        return $this->resourceViewUrl(
            PqrsRequestResource::class,
            $request,
            'filament.admin.resources.pqrs-requests.view',
            ['record' => $request],
        );
    }

    /**
     * @return array{label: string, badge_class: string, stripe_class: string}
     */
    private function pqrsStatusData(string $status): array
    {
        return match ($status) {
            'in_process' => [
                'label' => 'En proceso',
                'badge_class' => 'agro-badge agro-badge--blue',
                'stripe_class' => 'agro-pqrs-item--blue',
            ],
            'resolved', 'resuelto' => [
                'label' => 'Resuelto',
                'badge_class' => 'agro-badge agro-badge--emerald',
                'stripe_class' => 'agro-pqrs-item--emerald',
            ],
            'closed', 'cerrado', 'finalizado' => [
                'label' => 'Cerrado',
                'badge_class' => 'agro-badge agro-badge--slate',
                'stripe_class' => 'agro-pqrs-item--slate',
            ],
            default => [
                'label' => 'Pendiente',
                'badge_class' => 'agro-badge agro-badge--amber',
                'stripe_class' => 'agro-pqrs-item--amber',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function routeIfExists(string $name, array $parameters = []): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        return route($name, $parameters);
    }

    private function resourceIndexUrl(string $resourceClass, string $routeName): ?string
    {
        if (! $this->resourceCanViewAny($resourceClass) || ! $this->resourceHasPage($resourceClass, 'index')) {
            return null;
        }

        return $this->routeIfExists($routeName);
    }

    private function resourceCreateUrl(string $resourceClass, string $routeName): ?string
    {
        if (! $this->resourceCanCreate($resourceClass) || ! $this->resourceHasPage($resourceClass, 'create')) {
            return null;
        }

        return $this->routeIfExists($routeName);
    }

    /**
     * @param  array<string, mixed>  $routeParameters
     */
    private function resourceEditUrl(
        string $resourceClass,
        Model $record,
        string $routeName,
        array $routeParameters = [],
    ): ?string {
        if (! $this->resourceCanEdit($resourceClass, $record) || ! $this->resourceHasPage($resourceClass, 'edit')) {
            return null;
        }

        if (! $this->resourceCanResolveRecord($resourceClass, $record)) {
            return null;
        }

        return $this->routeIfExists($routeName, $routeParameters);
    }

    /**
     * @param  array<string, mixed>  $routeParameters
     */
    private function resourceViewUrl(
        string $resourceClass,
        Model $record,
        string $routeName,
        array $routeParameters = [],
    ): ?string {
        if (! $this->resourceCanView($resourceClass, $record) || ! $this->resourceHasPage($resourceClass, 'view')) {
            return null;
        }

        if (! $this->resourceCanResolveRecord($resourceClass, $record)) {
            return null;
        }

        return $this->routeIfExists($routeName, $routeParameters);
    }

    private function resourceCanViewAny(string $resourceClass): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'canViewAny')) {
            return false;
        }

        try {
            return (bool) $resourceClass::canViewAny();
        } catch (Throwable) {
            return false;
        }
    }

    private function resourceCanCreate(string $resourceClass): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'canCreate')) {
            return false;
        }

        try {
            return (bool) $resourceClass::canCreate();
        } catch (Throwable) {
            return false;
        }
    }

    private function resourceCanEdit(string $resourceClass, Model $record): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'canEdit')) {
            return false;
        }

        try {
            return (bool) $resourceClass::canEdit($record);
        } catch (Throwable) {
            return false;
        }
    }

    private function resourceCanView(string $resourceClass, Model $record): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'canView')) {
            return false;
        }

        try {
            return (bool) $resourceClass::canView($record);
        } catch (Throwable) {
            return false;
        }
    }

    private function resourceHasPage(string $resourceClass, string $page): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'hasPage')) {
            return false;
        }

        try {
            return (bool) $resourceClass::hasPage($page);
        } catch (Throwable) {
            return false;
        }
    }

    private function resourceCanResolveRecord(string $resourceClass, Model $record): bool
    {
        if (! class_exists($resourceClass) || ! method_exists($resourceClass, 'getRecordRouteBindingEloquentQuery')) {
            return true;
        }

        try {
            $query = $resourceClass::getRecordRouteBindingEloquentQuery();

            if (! $query instanceof Builder) {
                return true;
            }

            return (clone $query)->whereKey($record->getKey())->exists();
        } catch (Throwable) {
            return false;
        }
    }
}
