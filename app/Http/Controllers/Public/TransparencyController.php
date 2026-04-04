<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransparencyController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $landingPage = $this->publishedPageBySlug('transparencia');
        $categories = $this->resolveDocumentCategories();

        $recentDocuments = collect();

        if ($this->canQueryTable('documents')) {
            $recentDocuments = $this->documentsBaseQuery()
                ->with('categories')
                ->orderByDesc('published_at')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (Document $document): array => $this->mapDocument($document));
        }

        return view('public.transparencia.index', [
            'title' => $landingPage?->title ?: 'Transparencia',
            'lead' => $landingPage?->summary ?: 'Consulta información pública institucional y documentos oficiales para control social y rendición de cuentas.',
            'categories' => $categories,
            'recentDocuments' => $recentDocuments,
        ]);
    }

    public function documents(Request $request): View
    {
        $categories = $this->resolveDocumentCategories();
        $listingPage = $this->publishedPageBySlug('transparencia-documentos');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => trim((string) $request->query('category', '')),
            'year' => trim((string) $request->query('year', '')),
            'sort' => trim((string) $request->query('sort', 'recent')),
        ];

        if (! in_array($filters['sort'], ['recent', 'oldest', 'updated', 'title'], true)) {
            $filters['sort'] = 'recent';
        }

        $documents = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 10,
            currentPage: max(1, (int) $request->query('page', 1)),
            options: [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        $years = collect();

        if ($this->canQueryTable('documents')) {
            $query = $this->documentsBaseQuery()->with('categories');

            if ($filters['category'] !== '') {
                $query->whereHas('categories', function (Builder $categoryQuery) use ($filters): void {
                    $categoryQuery->where('slug', $filters['category']);
                });
            }

            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', $search)
                        ->orWhere('summary', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('document_number', 'like', $search);
                });
            }

            if (preg_match('/^\d{4}$/', $filters['year']) === 1) {
                $year = (int) $filters['year'];

                $query->where(function (Builder $yearQuery) use ($year): void {
                    $yearQuery
                        ->whereYear('document_date', $year)
                        ->orWhereYear('published_at', $year);
                });
            }

            match ($filters['sort']) {
                'oldest' => $query->orderBy('published_at')->orderBy('updated_at'),
                'updated' => $query->orderByDesc('updated_at')->orderByDesc('published_at'),
                'title' => $query->orderBy('title'),
                default => $query->orderByDesc('published_at')->orderByDesc('updated_at'),
            };

            $documents = $query
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Document $document): array => $this->mapDocument($document));

            $years = $this->documentsBaseQuery()
                ->get(['document_date', 'published_at'])
                ->map(function (Document $document): ?string {
                    $date = $document->document_date ?: $document->published_at;

                    return $date?->format('Y');
                })
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();
        }

        return view('public.transparencia.documents', [
            'title' => $listingPage?->title ?: 'Documentos de Transparencia',
            'lead' => $listingPage?->summary ?: 'Filtra, busca y consulta documentos institucionales oficiales.',
            'filters' => $filters,
            'categories' => $categories,
            'years' => $years,
            'documents' => $documents,
            'sortOptions' => [
                'recent' => 'Mas recientes',
                'oldest' => 'Mas antiguos',
                'updated' => 'Actualizacion reciente',
                'title' => 'Titulo (A-Z)',
            ],
        ]);
    }

    public function showDocument(string $slug): View
    {
        abort_unless($this->canQueryTable('documents'), 404);
        $listingPage = $this->publishedPageBySlug('transparencia-documentos')
            ?: $this->publishedPageBySlug('transparencia');

        $document = $this->documentsBaseQuery()
            ->with('categories')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedQuery = $this->documentsBaseQuery()
            ->with('categories')
            ->whereKeyNot($document->getKey());

        $categoryIds = $document->categories->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            $relatedQuery->whereHas('categories', function (Builder $query) use ($categoryIds): void {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        $related = $relatedQuery
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Document $relatedDocument): array => $this->mapDocument($relatedDocument));

        return view('public.transparencia.show', [
            'document' => $this->mapDocument($document, includeDescription: true),
            'categories' => $this->resolveDocumentCategories(),
            'related' => $related,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveDocumentCategories(): Collection
    {
        if (! $this->canQueryTable('categories') || ! $this->canQueryTable('documents')) {
            return collect();
        }

        return Category::query()
            ->where('status', 'published')
            ->whereHas('documents', function (Builder $documentQuery): void {
                $documentQuery->where('status', 'published');
            })
            ->withCount([
                'documents as published_documents_count' => function (Builder $documentQuery): void {
                    $documentQuery->where('status', 'published');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category): array {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => (int) $category->published_documents_count,
                    'url' => route('transparencia.documentos', ['category' => $category->slug]),
                ];
            })
            ->values();
    }

    private function documentsBaseQuery(): Builder
    {
        return Document::query()->where('status', 'published');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDocument(Document $document, bool $includeDescription = false): array
    {
        return [
            'title' => $document->title,
            'slug' => $document->slug,
            'summary' => $document->summary ?: Str::limit(strip_tags((string) $document->description), 180),
            'description' => $includeDescription ? $document->description : null,
            'number' => $document->document_number,
            'published_at' => $document->published_at?->translatedFormat('d M Y'),
            'updated_at' => $document->updated_at?->translatedFormat('d M Y H:i'),
            'document_date' => $document->document_date?->translatedFormat('d M Y'),
            'detail_url' => route('transparencia.documento', ['slug' => $document->slug]),
            'file_url' => $this->resolveDocumentUrl($document),
            'categories' => $document->categories
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->values(),
        ];
    }

    private function resolveDocumentUrl(Document $document): ?string
    {
        if ($document->external_url) {
            return $document->external_url;
        }

        if (! $document->file_path) {
            return null;
        }

        if (Str::startsWith($document->file_path, ['http://', 'https://', '/'])) {
            return $document->file_path;
        }

        return '/storage/'.ltrim($document->file_path, '/');
    }
}
