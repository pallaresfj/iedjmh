<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use ResolvesPublicContent;

    public function index(Request $request): View
    {
        $sectionConfig = config('institution.sections.academico', []);
        $listingPage = $this->publishedPageBySlug('academico-proyectos-pedagogicos');
        $sortOptions = $this->sortOptions();
        $filters = $this->extractFilters($request, $sortOptions);

        $projects = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 9,
            currentPage: max(1, (int) $request->query('page', 1)),
            options: [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        $featuredProject = null;
        $categories = collect();

        if ($this->canQueryTable('projects')) {
            $categories = $this->publishedProjectCategories();

            $baseQuery = $this->publishedProjectsQuery()
                ->with('categories')
                ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                    $searchTerm = '%'.$filters['q'].'%';

                    $query->where(function (Builder $searchQuery) use ($searchTerm): void {
                        $searchQuery
                            ->where('title', 'like', $searchTerm)
                            ->orWhere('summary', 'like', $searchTerm)
                            ->orWhere('description', 'like', $searchTerm);
                    });
                })
                ->when($filters['category'] !== '', function (Builder $query) use ($filters): void {
                    $query->whereHas('categories', function (Builder $categoryQuery) use ($filters): void {
                        $categoryQuery->where('categories.slug', $filters['category']);
                    });
                });

            $this->applySort($baseQuery, $filters['sort']);

            $projects = $baseQuery
                ->paginate(9)
                ->withQueryString()
                ->through(fn (Project $project): array => $this->mapProject($project));

            if ($this->shouldShowFeaturedProject($filters)) {
                /** @var Project|null $featuredModel */
                $featuredModel = $this->publishedProjectsQuery()
                    ->with('categories')
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at')
                    ->first();

                $featuredProject = $featuredModel ? $this->mapProject($featuredModel) : null;
            }
        }

        return view('public.proyectos.index', [
            'title' => $listingPage?->title ?: 'Proyectos Pedagógicos',
            'lead' => $listingPage?->summary ?: 'Iniciativas de aula y de institución para fortalecer aprendizaje significativo.',
            'banner' => $this->resolvePageBanner($listingPage),
            'content' => $listingPage?->content,
            'featuredProject' => $featuredProject,
            'projects' => $projects,
            'filters' => $filters,
            'categories' => $categories,
            'sortOptions' => $sortOptions,
            'academicPages' => $this->academicNavigationItems(),
        ]);
    }

    public function show(string $slug): View
    {
        abort_unless($this->canQueryTable('projects'), 404);
        $listingPage = $this->publishedPageBySlug('academico-proyectos-pedagogicos');

        /** @var Project $project */
        $project = $this->publishedProjectsQuery()
            ->with('categories')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = $this->publishedProjectsQuery()
            ->with('categories')
            ->whereKeyNot($project->getKey())
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get()
            ->map(fn (Project $relatedProject): array => $this->mapProject($relatedProject));

        return view('public.proyectos.show', [
            'project' => $this->mapProject($project, includeDescription: true),
            'related' => $related,
            'banner' => $this->resolvePageBanner($listingPage),
            'academicPages' => $this->academicNavigationItems(),
        ]);
    }

    private function publishedProjectsQuery(): Builder
    {
        return Project::query()->where('status', 'published');
    }

    /**
     * @param  array<string, string>  $sortOptions
     * @return array{q: string, category: string, sort: string}
     */
    private function extractFilters(Request $request, array $sortOptions): array
    {
        $sort = trim((string) $request->query('sort', 'recent'));
        $sort = array_key_exists($sort, $sortOptions) ? $sort : 'recent';

        return [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'sort' => $sort,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'recent' => 'Mas recientes',
            'oldest' => 'Mas antiguos',
            'title_asc' => 'Titulo A-Z',
            'title_desc' => 'Titulo Z-A',
        ];
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query
                ->orderByDesc('is_featured')
                ->orderBy('published_at')
                ->orderBy('sort_order')
                ->orderBy('title'),
            'title_asc' => $query
                ->orderBy('title')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at'),
            'title_desc' => $query
                ->orderByDesc('title')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at'),
            default => $query
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
        };
    }

    /**
     * @return Collection<int, array{name: string, slug: string, count: int}>
     */
    private function publishedProjectCategories(): Collection
    {
        if (! $this->canQueryTable('categories')) {
            return collect();
        }

        return Category::query()
            ->where('status', 'published')
            ->whereHas('projects', function (Builder $query): void {
                $query->where('projects.status', 'published');
            })
            ->withCount([
                'projects as projects_count' => function (Builder $query): void {
                    $query->where('projects.status', 'published');
                },
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) $category->projects_count,
            ])
            ->values();
    }

    /**
     * @param  array{q: string, category: string, sort: string}  $filters
     */
    private function shouldShowFeaturedProject(array $filters): bool
    {
        return $filters['q'] === ''
            && $filters['category'] === ''
            && $filters['sort'] === 'recent';
    }

    /**
     * @return Collection<int, array{key: string, title: string, route: string}>
     */
    private function academicNavigationItems(): Collection
    {
        return collect(app(AcademicController::class)->publicPageDefinitions())
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                'title' => $definition['title'],
                'route' => $definition['route'],
            ])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProject(Project $project, bool $includeDescription = false): array
    {
        $coverImageUrl = $this->resolveMediaUrl($project->cover_image_path);
        $galleryImages = $this->resolveGalleryImages($project, $coverImageUrl);
        $period = trim(collect([
            $project->starts_on?->translatedFormat('d M Y'),
            $project->ends_on?->translatedFormat('d M Y'),
        ])->filter()->join(' - '));

        return [
            'title' => $project->title,
            'slug' => $project->slug,
            'summary' => $project->summary ?: Str::limit(strip_tags((string) $project->description), 180),
            'description' => $includeDescription ? $project->description : null,
            'period' => $period ?: null,
            'is_featured' => (bool) $project->is_featured,
            'image_url' => $coverImageUrl,
            'external_url' => $this->sanitizeExternalUrl($project->external_url),
            'gallery_images' => $galleryImages,
            'published_at' => $project->published_at?->translatedFormat('d M Y'),
            'detail_url' => route('academico.proyectos-pedagogicos.show', ['slug' => $project->slug]),
            'categories' => $project->categories
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->values(),
        ];
    }

    private function sanitizeExternalUrl(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '/')) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * @return array<int, string>
     */
    private function resolveGalleryImages(Project $project, ?string $coverImageUrl): array
    {
        if (! is_array($project->gallery_image_paths)) {
            return [];
        }

        $maxGalleryImages = $coverImageUrl ? 4 : 5;

        return collect($project->gallery_image_paths)
            ->filter(fn (mixed $path): bool => is_string($path) && trim($path) !== '')
            ->map(fn (string $path): ?string => $this->resolveMediaUrl($path))
            ->filter(fn (?string $url): bool => filled($url))
            ->reject(fn (string $url): bool => $coverImageUrl !== null && $url === $coverImageUrl)
            ->unique()
            ->take($maxGalleryImages)
            ->values()
            ->all();
    }
}
