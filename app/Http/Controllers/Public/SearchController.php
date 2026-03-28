<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Document;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Procedure;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    use ResolvesPublicContent;

    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = collect();

        if (mb_strlen($query) >= 3) {
            $term = '%'.$query.'%';

            $results = collect()
                ->merge($this->searchPosts($term))
                ->merge($this->searchPages($term))
                ->merge($this->searchDocuments($term))
                ->merge($this->searchEvents($term))
                ->merge($this->searchProjects($term))
                ->merge($this->searchFaqs($term))
                ->merge($this->searchProcedures($term));
        }

        return view('public.buscar', [
            'title' => 'Busqueda',
            'query' => $query,
            'results' => $results,
            'resultCount' => $results->count(),
        ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchPosts(string $term): Collection
    {
        if (! $this->canQueryTable('posts')) {
            return collect();
        }

        return Post::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('title COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('excerpt COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('content COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Post $item): array => [
                'type' => 'Noticia',
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags((string) ($item->excerpt ?: $item->content)), 150),
                'url' => route('noticias.show', $item->slug),
                'date' => $item->published_at?->translatedFormat('d M Y'),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchPages(string $term): Collection
    {
        if (! $this->canQueryTable('pages')) {
            return collect();
        }

        return Page::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('title COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('content COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Page $item): array => [
                'type' => 'Pagina',
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags((string) $item->content), 150),
                'url' => $item->menu_binding ? route($item->menu_binding) : '#',
                'date' => $item->updated_at?->translatedFormat('d M Y'),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchDocuments(string $term): Collection
    {
        if (! $this->canQueryTable('documents')) {
            return collect();
        }

        return Document::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('title COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('summary COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Document $item): array => [
                'type' => 'Documento',
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags((string) $item->summary), 150),
                'url' => route('transparencia.documento', $item->slug),
                'date' => $item->published_at?->translatedFormat('d M Y'),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchEvents(string $term): Collection
    {
        if (! $this->canQueryTable('events')) {
            return collect();
        }

        return Event::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('title COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('summary COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('description COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Event $item): array => [
                'type' => 'Evento',
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags((string) ($item->summary ?: $item->description)), 150),
                'url' => route('eventos.show', $item->slug),
                'date' => $item->starts_at?->translatedFormat('d M Y'),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchProjects(string $term): Collection
    {
        if (! $this->canQueryTable('projects')) {
            return collect();
        }

        return Project::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('title COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('summary COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('description COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Project $item): array => [
                'type' => 'Proyecto',
                'title' => $item->title,
                'excerpt' => Str::limit(strip_tags((string) ($item->summary ?: $item->description)), 150),
                'url' => route('academico.proyectos-pedagogicos.show', $item->slug),
                'date' => $item->published_at?->translatedFormat('d M Y'),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchFaqs(string $term): Collection
    {
        if (! $this->canQueryTable('faqs')) {
            return collect();
        }

        return Faq::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('question COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('answer COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Faq $item): array => [
                'type' => 'Pregunta frecuente',
                'title' => $item->question,
                'excerpt' => Str::limit(strip_tags((string) $item->answer), 150),
                'url' => route('atencion.faq', ['q' => $item->question]),
                'date' => null,
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchProcedures(string $term): Collection
    {
        if (! $this->canQueryTable('procedures')) {
            return collect();
        }

        return Procedure::query()
            ->where('status', 'published')
            ->where(fn (Builder $q) => $q
                ->whereRaw('name COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('summary COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term])
                ->orWhereRaw('requirements COLLATE utf8mb4_0900_ai_ci LIKE ?', [$term]))
            ->limit(10)
            ->get()
            ->map(fn (Procedure $item): array => [
                'type' => 'Tramite',
                'title' => $item->name,
                'excerpt' => Str::limit(strip_tags((string) ($item->summary ?: $item->requirements)), 150),
                'url' => route('atencion.tramites', ['q' => $item->name]),
                'date' => null,
            ]);
    }
}
