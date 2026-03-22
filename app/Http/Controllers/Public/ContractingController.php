<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractParticipant;
use App\Models\ContractType;
use App\Models\Document;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ContractingController extends Controller
{
    use ResolvesPublicContent;

    public function index(Request $request): View
    {
        $page = $this->publishedPageBySlug('transparencia-contratacion')
            ?: $this->publishedPageBySlug('transparencia');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'fiscal_year' => trim((string) $request->query('fiscal_year', '')),
            'process_status' => trim((string) $request->query('process_status', '')),
            'type' => trim((string) $request->query('type', '')),
        ];

        if (! array_key_exists($filters['process_status'], Contract::PROCESS_STATUS_OPTIONS)) {
            $filters['process_status'] = '';
        }

        if (preg_match('/^\d{4}$/', $filters['fiscal_year']) !== 1) {
            $filters['fiscal_year'] = '';
        }

        $contracts = new LengthAwarePaginator(
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
        $types = collect();
        $contractors = collect();
        $activeFilters = [];
        $hasActiveFilters = false;
        $contractsTotal = 0;

        if ($this->canQueryTable('contracts') && $this->canQueryTable('contract_types')) {
            $hasParticipantsTable = $this->canQueryTable('contract_participants');
            $types = ContractType::query()
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug'])
                ->map(fn (ContractType $type): array => [
                    'name' => $type->name,
                    'slug' => $type->slug,
                ]);

            $queryRelations = ['contractType', 'documents'];

            if ($hasParticipantsTable) {
                $queryRelations[] = 'participants';
            }

            $query = $this->publicContractsQuery()->with($queryRelations);

            if ($filters['fiscal_year'] !== '') {
                $query->where('fiscal_year', (int) $filters['fiscal_year']);
            }

            if ($filters['process_status'] !== '') {
                $query->where('process_status', $filters['process_status']);
            }

            if ($filters['type'] !== '') {
                $query->whereHas('contractType', function (Builder $typeQuery) use ($filters): void {
                    $typeQuery->where('slug', $filters['type']);
                });
            }

            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('process_code', 'like', $search)
                        ->orWhere('object', 'like', $search)
                        ->orWhere('contractor_name', 'like', $search)
                        ->orWhere('contractor_nit', 'like', $search);
                });
            }

            $contracts = $query
                ->orderByDesc('fiscal_year')
                ->orderByDesc('publication_date')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Contract $contract): array => $this->mapContractSummary($contract));

            $activeFilters = $this->resolveActiveFilters($filters, $types);
            $hasActiveFilters = $activeFilters !== [];
            $contractsTotal = $contracts->total();

            $years = Contract::query()
                ->published()
                ->select('fiscal_year')
                ->distinct()
                ->orderByDesc('fiscal_year')
                ->pluck('fiscal_year')
                ->map(fn (mixed $year): string => (string) $year)
                ->values();

            $contractors = $this->resolveContractorsDirectory();
        }

        return view('public.transparencia.contratacion.index', [
            'title' => $page?->title ?: 'Contratacion y Transparencia',
            'lead' => $page?->summary ?: 'Consulta ofertas de contratos FSE, adjudicaciones, documentos soporte y directorio de contratistas adjudicados.',
            'banner' => $this->resolvePageBanner($page),
            'categories' => $this->resolveDocumentCategories(),
            'manualDocument' => $this->resolveManualDocument(),
            'filters' => $filters,
            'statusOptions' => Contract::PROCESS_STATUS_OPTIONS,
            'years' => $years,
            'types' => $types,
            'activeFilters' => $activeFilters,
            'hasActiveFilters' => $hasActiveFilters,
            'contractsTotal' => $contractsTotal,
            'contracts' => $contracts,
            'contractors' => $contractors,
        ]);
    }

    public function show(string $processCode): View
    {
        abort_unless($this->canQueryTable('contracts'), 404);

        $page = $this->publishedPageBySlug('transparencia-contratacion')
            ?: $this->publishedPageBySlug('transparencia');

        $queryRelations = ['contractType', 'documents'];

        if ($this->canQueryTable('contract_participants')) {
            $queryRelations[] = 'participants';
        }

        $contract = $this->publicContractsQuery()
            ->with($queryRelations)
            ->where('process_code', $processCode)
            ->firstOrFail();

        $documentsByStage = collect(ContractDocument::STAGE_OPTIONS)
            ->mapWithKeys(function (string $label, string $stage) use ($contract): array {
                $items = $contract->documents
                    ->where('stage', $stage)
                    ->values()
                    ->map(fn (ContractDocument $document): array => $this->mapContractDocument($document));

                return [$stage => [
                    'label' => $label,
                    'items' => $items,
                ]];
            });

        return view('public.transparencia.contratacion.show', [
            'title' => $contract->process_code,
            'banner' => $this->resolvePageBanner($page),
            'categories' => $this->resolveDocumentCategories(),
            'contract' => $this->mapContractDetail($contract),
            'documentsByStage' => $documentsByStage,
        ]);
    }

    private function publicContractsQuery(): Builder
    {
        return Contract::query()->published();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContractSummary(Contract $contract): array
    {
        return [
            'process_code' => $contract->process_code,
            'object' => $contract->object,
            'official_budget' => $contract->official_budget !== null ? (float) $contract->official_budget : null,
            'process_status' => $contract->process_status,
            'process_status_label' => Contract::PROCESS_STATUS_OPTIONS[$contract->process_status] ?? $contract->process_status,
            'fiscal_year' => $contract->fiscal_year,
            'type' => $contract->contractType?->name,
            'documents_count' => $contract->documents->count(),
            'detail_url' => route('transparencia.contratacion.show', ['processCode' => $contract->process_code]),
            'publication_date' => $contract->publication_date?->translatedFormat('d M Y'),
            'award_date' => $contract->award_date?->translatedFormat('d M Y'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContractDetail(Contract $contract): array
    {
        $participants = collect();

        if ($contract->relationLoaded('participants')) {
            $participants = $contract->participants
                ->map(fn (ContractParticipant $participant): array => $this->mapContractParticipant($participant))
                ->values();
        }

        return [
            'process_code' => $contract->process_code,
            'object' => $contract->object,
            'official_budget' => $contract->official_budget !== null ? (float) $contract->official_budget : null,
            'process_status' => $contract->process_status,
            'process_status_label' => Contract::PROCESS_STATUS_OPTIONS[$contract->process_status] ?? $contract->process_status,
            'fiscal_year' => $contract->fiscal_year,
            'type' => $contract->contractType?->name,
            'publication_date' => $contract->publication_date?->translatedFormat('d M Y'),
            'offers_deadline_date' => $contract->offers_deadline_date?->translatedFormat('d M Y'),
            'evaluation_date' => $contract->evaluation_date?->translatedFormat('d M Y'),
            'award_date' => $contract->award_date?->translatedFormat('d M Y'),
            'contractor_name' => $contract->contractor_name,
            'contractor_nit' => $contract->contractor_nit,
            'contractor_social_object' => $contract->contractor_social_object,
            'secop_ii_url' => $this->sanitizeReferenceUrl($contract->secop_ii_url),
            'participants' => $participants,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContractDocument(ContractDocument $document): array
    {
        $label = ContractDocument::labelForType($document->document_type);

        return [
            'type' => $document->document_type,
            'type_label' => $label,
            'title' => $document->title ?: $label,
            'url' => $this->resolveContractDocumentUrl($document),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContractParticipant(ContractParticipant $participant): array
    {
        return [
            'name' => $participant->name,
            'nit' => $participant->nit,
            'social_object' => $participant->social_object,
            'evaluation_score' => $participant->evaluation_score !== null ? (float) $participant->evaluation_score : null,
            'is_awarded' => (bool) $participant->is_awarded,
        ];
    }

    /**
     * @return Collection<int, array{name: string, nit: string, social_object: string, adjudications: int}>
     */
    private function resolveContractorsDirectory(): Collection
    {
        $hasParticipantsTable = $this->canQueryTable('contract_participants');

        $query = $this->publicContractsQuery()
            ->where('process_status', 'adjudicado')
            ->where(function (Builder $contractQuery) use ($hasParticipantsTable): void {
                $contractQuery->whereNotNull('contractor_name');

                if ($hasParticipantsTable) {
                    $contractQuery->orWhereHas('participants', fn (Builder $participantsQuery): Builder => $participantsQuery->where('is_awarded', true));
                }
            });

        if ($hasParticipantsTable) {
            $query->with(['participants' => fn ($participantQuery) => $participantQuery
                ->where('is_awarded', true)
                ->orderBy('sort_order')
                ->orderBy('id')]);
        }

        $contracts = $query->get(['contractor_name', 'contractor_nit', 'contractor_social_object']);

        return $contracts
            ->map(function (Contract $contract): ?array {
                /** @var ContractParticipant|null $awardedParticipant */
                $awardedParticipant = $contract->relationLoaded('participants')
                    ? $contract->participants->first()
                    : null;

                $name = trim((string) ($awardedParticipant?->name ?: $contract->contractor_name));
                $nit = trim((string) ($awardedParticipant?->nit ?: $contract->contractor_nit));
                $socialObject = trim((string) ($awardedParticipant?->social_object ?: $contract->contractor_social_object));

                if ($name === '' && $nit === '') {
                    return null;
                }

                return [
                    'name' => $name !== '' ? $name : 'Sin nombre registrado',
                    'nit' => $nit !== '' ? $nit : 'Sin NIT registrado',
                    'social_object' => $socialObject !== '' ? $socialObject : 'No registrado',
                ];
            })
            ->filter()
            ->groupBy(function (array $contractor): string {
                $nit = trim((string) $contractor['nit']);
                $name = trim((string) $contractor['name']);

                return strtolower($nit !== '' ? $nit : $name);
            })
            ->map(function (Collection $items): array {
                /** @var array{name: string, nit: string, social_object: string} $first */
                $first = $items->first();

                return [
                    'name' => (string) $first['name'],
                    'nit' => (string) $first['nit'],
                    'social_object' => (string) $first['social_object'],
                    'adjudications' => $items->count(),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * @param  array{q: string, fiscal_year: string, process_status: string, type: string}  $filters
     * @param  Collection<int, array{name: string, slug: string}>  $types
     * @return array<int, array{key: string, label: string, value: string}>
     */
    private function resolveActiveFilters(array $filters, Collection $types): array
    {
        $activeFilters = [];

        if ($filters['q'] !== '') {
            $activeFilters[] = [
                'key' => 'q',
                'label' => 'Busqueda',
                'value' => $filters['q'],
            ];
        }

        if ($filters['fiscal_year'] !== '') {
            $activeFilters[] = [
                'key' => 'fiscal_year',
                'label' => 'Vigencia',
                'value' => $filters['fiscal_year'],
            ];
        }

        if ($filters['process_status'] !== '') {
            $activeFilters[] = [
                'key' => 'process_status',
                'label' => 'Estado',
                'value' => Contract::PROCESS_STATUS_OPTIONS[$filters['process_status']] ?? $filters['process_status'],
            ];
        }

        if ($filters['type'] !== '') {
            $typeLabel = $types
                ->first(fn (array $type): bool => ($type['slug'] ?? '') === $filters['type'])['name'] ?? $filters['type'];

            $activeFilters[] = [
                'key' => 'type',
                'label' => 'Tipo',
                'value' => $typeLabel,
            ];
        }

        return $activeFilters;
    }

    /**
     * @return array{title: string, url: string, detail_url: string}|null
     */
    private function resolveManualDocument(): ?array
    {
        if (! $this->canQueryColumn('settings', 'contracting_manual_document_id') || ! $this->canQueryTable('documents')) {
            return null;
        }

        $manualDocumentId = Setting::query()
            ->where('singleton', 1)
            ->value('contracting_manual_document_id');

        if (! is_numeric($manualDocumentId)) {
            return null;
        }

        $document = Document::query()
            ->where('status', 'published')
            ->whereKey((int) $manualDocumentId)
            ->first();

        if (! $document) {
            return null;
        }

        $url = $this->resolveDocumentUrl($document);

        if ($url === null) {
            return null;
        }

        return [
            'title' => $document->title,
            'url' => $url,
            'detail_url' => route('transparencia.documento', ['slug' => $document->slug]),
        ];
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

    private function resolveContractDocumentUrl(ContractDocument $document): ?string
    {
        return $this->sanitizeReferenceUrl($document->external_url);
    }

    private function resolveDocumentUrl(Document $document): ?string
    {
        $external = $this->sanitizeReferenceUrl($document->external_url);

        if ($external !== null) {
            return $external;
        }

        if (! filled($document->file_path)) {
            return null;
        }

        return $this->resolveMediaUrl((string) $document->file_path);
    }

    private function sanitizeReferenceUrl(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }
}
