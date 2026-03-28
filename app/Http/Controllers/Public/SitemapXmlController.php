<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Document;
use App\Models\Event;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapXmlController extends Controller
{
    use ResolvesPublicContent;

    public function __invoke(): Response
    {
        $urls = collect();

        $urls->push($this->entry(route('home'), now(), 'daily', '1.0'));

        $staticRoutes = [
            'institucion.index', 'institucion.historia', 'institucion.mision-vision',
            'institucion.simbolos', 'institucion.equipo-directivo', 'institucion.sedes',
            'institucion.pei', 'institucion.manual-convivencia', 'institucion.directorio',
            'academico.index', 'academico.niveles-educativos', 'academico.modalidad-agropecuaria',
            'academico.planes-area', 'academico.sistema-evaluacion', 'academico.proyectos-pedagogicos',
            'academico.calendario-academico',
            'proyectos.index', 'noticias.index',
            'transparencia.index', 'transparencia.documentos', 'transparencia.contratacion.index',
            'atencion.index', 'atencion.contactenos', 'atencion.pqrs', 'atencion.pqrs.track',
            'atencion.tramites', 'atencion.faq', 'atencion.mapa-sitio', 'atencion.participacion',
        ];

        foreach ($staticRoutes as $routeName) {
            $urls->push($this->entry(route($routeName), null, 'weekly', '0.8'));
        }

        if ($this->canQueryTable('posts')) {
            Post::query()
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->limit(200)
                ->get(['slug', 'updated_at'])
                ->each(function (Post $post) use ($urls): void {
                    $urls->push($this->entry(
                        route('noticias.show', $post->slug),
                        $post->updated_at,
                        'weekly',
                        '0.6',
                    ));
                });
        }

        if ($this->canQueryTable('projects')) {
            Project::query()
                ->where('status', 'published')
                ->limit(100)
                ->get(['slug', 'updated_at'])
                ->each(function (Project $project) use ($urls): void {
                    $urls->push($this->entry(
                        route('proyectos.show', $project->slug),
                        $project->updated_at,
                        'monthly',
                        '0.6',
                    ));
                });
        }

        if ($this->canQueryTable('events')) {
            Event::query()
                ->where('status', 'published')
                ->orderByDesc('starts_at')
                ->limit(100)
                ->get(['slug', 'updated_at'])
                ->each(function (Event $event) use ($urls): void {
                    $urls->push($this->entry(
                        route('eventos.show', $event->slug),
                        $event->updated_at,
                        'weekly',
                        '0.5',
                    ));
                });
        }

        if ($this->canQueryTable('documents')) {
            Document::query()
                ->where('status', 'published')
                ->limit(200)
                ->get(['slug', 'updated_at'])
                ->each(function (Document $doc) use ($urls): void {
                    $urls->push($this->entry(
                        route('transparencia.documento', $doc->slug),
                        $doc->updated_at,
                        'monthly',
                        '0.5',
                    ));
                });
        }

        $xml = $this->buildXml($urls);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    private function entry(string $url, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $url,
            'lastmod' => $lastmod ? $lastmod->toDateString() : null,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * @param  Collection<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>  $urls
     */
    private function buildXml(Collection $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1)."</loc>\n";
            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}
