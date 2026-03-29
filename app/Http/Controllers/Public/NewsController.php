<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    use ResolvesPublicContent;

    public function index(Request $request): View
    {
        $sectionConfig = config('institution.sections.noticias', []);
        $listingPage = $this->publishedPageBySlug('noticias');
        $sortOptions = $this->sortOptions();
        $filters = $this->extractFilters($request, $sortOptions);

        $items = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 9,
            currentPage: max(1, (int) $request->query('page', 1)),
            options: [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        $newsParentCategoryId = $this->resolveNewsParentCategoryId();
        $categories = $this->publishedNewsCategories($newsParentCategoryId);
        $featuredNews = collect();

        if ($this->canQueryTable('posts')) {
            $showFeaturedNews = $this->shouldShowFeaturedNews($filters);

            if ($showFeaturedNews) {
                $featuredNews = $this->publishedPostsQuery()
                    ->with('categories')
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at')
                    ->limit(Post::MAX_PUBLISHED_FEATURED)
                    ->get()
                    ->map(fn (Post $post): array => $this->mapPost($post));
            }

            $query = $this->publishedPostsQuery()
                ->with('categories')
                ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                    $searchTerm = '%'.$filters['q'].'%';

                    $query->where(function (Builder $searchQuery) use ($searchTerm): void {
                        $searchQuery
                            ->where('title', 'like', $searchTerm)
                            ->orWhere('excerpt', 'like', $searchTerm)
                            ->orWhere('content', 'like', $searchTerm);
                    });
                })
                ->when($filters['category'] !== '', function (Builder $query) use ($filters, $newsParentCategoryId): void {
                    if ($newsParentCategoryId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('categories', function (Builder $categoryQuery) use ($filters, $newsParentCategoryId): void {
                        $categoryQuery->where('categories.slug', $filters['category']);
                        $categoryQuery->where('categories.parent_id', $newsParentCategoryId);
                    });
                });

            if ($showFeaturedNews && $featuredNews->isNotEmpty()) {
                $query->where('is_featured', false);
            }

            $this->applySort($query, $filters['sort']);

            $items = $query
                ->paginate(3)
                ->withQueryString()
                ->through(fn (Post $post): array => $this->mapPost($post));
        }

        return view('public.noticias.index', [
            'title' => $listingPage?->title ?: ($sectionConfig['title'] ?? 'Noticias'),
            'lead' => $listingPage?->summary ?: ($sectionConfig['description'] ?? 'Consulta novedades, comunicados y actualizaciones institucionales.'),
            'banner' => $this->resolvePageBanner($listingPage),
            'content' => $listingPage?->content,
            'items' => $items,
            'filters' => $filters,
            'categories' => $categories,
            'featuredNews' => $featuredNews,
            'sortOptions' => $sortOptions,
        ]);
    }

    public function show(string $slug): View
    {
        abort_unless($this->canQueryTable('posts'), 404);
        $listingPage = $this->publishedPageBySlug('noticias');

        /** @var Post $post */
        $post = $this->publishedPostsQuery()
            ->with('categories')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedQuery = $this->publishedPostsQuery()
            ->with('categories')
            ->whereKeyNot($post->getKey());

        $categoryIds = $post->categories->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            $relatedQuery->whereHas('categories', function (Builder $query) use ($categoryIds): void {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        $related = $relatedQuery
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get()
            ->map(fn (Post $relatedPost): array => $this->mapPost($relatedPost));

        return view('public.noticias.show', [
            'news' => $this->mapPost($post, includeContent: true),
            'related' => $related,
            'banner' => $this->resolvePageBanner($listingPage),
        ]);
    }

    private function publishedPostsQuery(): Builder
    {
        return Post::query()->where('status', 'published');
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
            default => $query
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
        };
    }

    /**
     * @param  array{q: string, category: string, sort: string}  $filters
     */
    private function shouldShowFeaturedNews(array $filters): bool
    {
        return $filters['q'] === ''
            && $filters['category'] === ''
            && $filters['sort'] === 'recent';
    }

    /**
     * @return Collection<int, array{name: string, slug: string, count: int}>
     */
    private function publishedNewsCategories(?int $newsParentCategoryId): Collection
    {
        if (! $this->canQueryTable('categories') || ! $this->canQueryTable('posts') || $newsParentCategoryId === null) {
            return collect();
        }

        return Category::query()
            ->where('status', 'published')
            ->where('parent_id', $newsParentCategoryId)
            ->whereHas('posts', function (Builder $query): void {
                $query->where('posts.status', 'published');
            })
            ->withCount([
                'posts as posts_count' => function (Builder $query): void {
                    $query->where('posts.status', 'published');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) $category->posts_count,
            ])
            ->values();
    }

    private function resolveNewsParentCategoryId(): ?int
    {
        if (! $this->canQueryTable('categories')) {
            return null;
        }

        /** @var int|null $parentCategoryId */
        $parentCategoryId = Category::query()
            ->whereNull('parent_id')
            ->whereIn('slug', ['noticia', 'noticias'])
            ->value('id');

        if ($parentCategoryId !== null) {
            return $parentCategoryId;
        }

        return Category::query()
            ->whereNull('parent_id')
            ->whereIn('name', ['Noticia', 'Noticias'])
            ->value('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPost(Post $post, bool $includeContent = false): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?: Str::limit(strip_tags((string) $post->content), 180),
            'content' => $includeContent ? (string) ($post->content ?? '') : null,
            'image_url' => $this->resolveMediaUrl($post->cover_image_path),
            'published_at' => $post->published_at?->translatedFormat('d M Y'),
            'is_featured' => (bool) $post->is_featured,
            'detail_url' => route('noticias.show', ['slug' => $post->slug]),
            'categories' => $post->categories
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->values(),
        ];
    }
}
