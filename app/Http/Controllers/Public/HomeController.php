<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Banner;
use App\Models\Event;
use App\Models\Post;
use App\Models\Project;
use App\Support\PublicSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    use ResolvesPublicContent;

    public function __invoke(): View
    {
        return view('public.home', [
            'hero' => $this->resolveHero(),
            'quickLinks' => $this->resolveQuickLinks(),
            'newsItems' => $this->resolveNews(),
            'featuredProject' => $this->resolveFeaturedProject(),
            'upcomingEvents' => $this->resolveUpcomingEvents(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveHero(): array
    {
        $institutionName = (string) PublicSettings::get('institution_name', 'IED Agropecuaria Jose Maria Herrera');

        $fallback = [
            'eyebrow' => $institutionName,
            'title' => 'Formando lideres para el agro y la vida',
            'description' => 'Educacion de calidad con enfoque tecnico agropecuario para el desarrollo sostenible de nuestra comunidad.',
            'cta_label' => 'Conoce nuestra matricula 2026',
            'cta_url' => route('atencion.index'),
            'cta_target' => '_self',
            'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDMUNlu1vZSYgs1mJ8XI2JBGdEGv7h77-FKsinYr5EjYaApSudFf0jhOBzLc6yoEXKGCF-tewz8MJIFovX4aKHbA0O3FnBStuhctqyV0oVkBdASloF8K2rO8VVM18nBjgTP2zwD60uTY7U6Vw-bB3w4vymqId0y98mtqnopTqtBAvch6WRWhfF7lV9eqtrHoQxcCTLHXNxBGP1xnxW6D-Hw4cLmuICL4qmBewmK1UqmRBf9D7Wau-xa_o7aSt4rGKkayqhXd0Sj8bxl',
        ];

        $settingsImagePath = PublicSettings::get('home_hero_image_path');
        $settingsImageUrl = is_string($settingsImagePath)
            ? PublicSettings::mediaUrl($settingsImagePath)
            : null;
        $homeBanner = $this->resolveHomeBanner();
        $cta = $this->resolveHeroCta($fallback, $homeBanner);

        return [
            'eyebrow' => trim((string) PublicSettings::get('home_hero_eyebrow', '')) ?: ($homeBanner['eyebrow'] ?? $fallback['eyebrow']),
            'title' => trim((string) PublicSettings::get('home_hero_title', '')) ?: ($homeBanner['title'] ?? $fallback['title']),
            'description' => trim((string) PublicSettings::get('home_hero_description', '')) ?: ($homeBanner['description'] ?? $fallback['description']),
            'cta_label' => $cta['cta_label'],
            'cta_url' => $cta['cta_url'],
            'cta_target' => $cta['cta_target'],
            'image_url' => $settingsImageUrl ?: ($homeBanner['image_url'] ?? $fallback['image_url']),
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function resolveHomeBanner(): ?array
    {
        if (! $this->canQueryTable('banners')) {
            return null;
        }

        /** @var Banner|null $banner */
        $bannerQuery = Banner::query()
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('starts_at')
            ->orderByDesc('id');

        if ($this->canQueryColumn('banners', 'page_id')) {
            $bannerQuery->whereNull('page_id');
        }

        /** @var Banner|null $banner */
        $banner = $bannerQuery->first();

        if (! $banner) {
            return null;
        }

        return [
            'eyebrow' => (string) ($banner->subtitle ?? ''),
            'title' => (string) ($banner->title ?? ''),
            'description' => (string) ($banner->description ?? ''),
            'cta_label' => (string) ($banner->cta_label ?? ''),
            'cta_url' => (string) ($banner->cta_url ?? ''),
            'cta_target' => $this->normalizeLinkTarget($banner->target),
            'image_url' => (string) ($this->resolveMediaUrl($banner->image_path) ?? ''),
        ];
    }

    /**
     * @param  array<string, string>  $fallback
     * @param  array<string, string>|null  $homeBanner
     * @return array{cta_label: string, cta_url: string, cta_target: string}
     */
    private function resolveHeroCta(array $fallback, ?array $homeBanner): array
    {
        $settingsLabel = trim((string) PublicSettings::get('home_hero_cta_label', ''));
        $settingsUrl = trim((string) PublicSettings::get('home_hero_cta_url', ''));

        if ($settingsLabel !== '' && $settingsUrl !== '') {
            return [
                'cta_label' => $settingsLabel,
                'cta_url' => $settingsUrl,
                'cta_target' => $this->normalizeLinkTarget(PublicSettings::get('home_hero_cta_target', '_self')),
            ];
        }

        $bannerLabel = trim((string) ($homeBanner['cta_label'] ?? ''));
        $bannerUrl = trim((string) ($homeBanner['cta_url'] ?? ''));

        if ($bannerLabel !== '' && $bannerUrl !== '') {
            return [
                'cta_label' => $bannerLabel,
                'cta_url' => $bannerUrl,
                'cta_target' => $this->normalizeLinkTarget($homeBanner['cta_target'] ?? '_self'),
            ];
        }

        return [
            'cta_label' => $fallback['cta_label'],
            'cta_url' => $fallback['cta_url'],
            'cta_target' => $fallback['cta_target'],
        ];
    }

    private function normalizeLinkTarget(mixed $target): string
    {
        return $target === '_blank' ? '_blank' : '_self';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function resolveQuickLinks(): array
    {
        return [
            [
                'title' => 'Calendario academico',
                'description' => 'Fechas institucionales y actividades',
                'route' => route('academico.calendario-academico'),
                'icon' => 'calendar_month',
            ],
            [
                'title' => 'Recursos academicos',
                'description' => 'Lineamientos, planes y modalidad',
                'route' => route('academico.index'),
                'icon' => 'library_books',
            ],
            [
                'title' => 'Matricula',
                'description' => 'Proceso y requisitos institucionales',
                'route' => route('atencion.index'),
                'icon' => 'app_registration',
            ],
            [
                'title' => 'Portal PQRS',
                'description' => 'Atencion y participacion ciudadana',
                'route' => route('atencion.pqrs'),
                'icon' => 'forum',
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveNews(): Collection
    {
        $fallbackImages = [
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAA6PZ6wLaG30mRe_OOce73zoeXaZ8gXw7rrSofLFMd4m5s2H___8aVCIiTj137lMWCIwYF4fkk6SGve5f6BtE1a9dnxskXu-NeRrQVCHd_FIycLSKbl3fOpWRQiPrhQG3-YCcGInDmBbm6vlIS6xitPt4ZnZQE5BQs4Nlb6Hw-AS2HqcVtaYHJL-zyvnXLWZCYNScZUyVap4XXdlyLaRaJTCQO3yrd0-0hrJWYIvk-wEwMgumuSGRMxOfXsALhsYHoX9HpFOg7kKI0',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuABUOLDoda-HJpC2tHiwP-BrAJojb-62tRmzckHt_F0QT_2uuJ7vrmIeCs00mmVgSBE2iqhflIl2EBbU6nEaiZdejqY6zoMge86MvxHDs11lelN0WU_wSEQsu72tBJr146dfX-GBCSe0nche217_g-khOv8VkiPBHBIH7LjhV-mmtGhPbxaYloV5y_7R6b8Lcxd2HJPNE6d1FFEtfGUN_qBtfF6pbfL4j12vOrWAMgMP4izycJKWEV_-gHUN4J2dJ0Rl6ERpvNO5SjB',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuDD5dwJU3UvqE6sOTkTbHbOSlWfBrG5AhIrAgfN4HvLtUPsD4nLw66vKOx6Ke8C1GABj04JJZ69Knb5z_vhRb0GrCXSU6FMyyb-pJVPL4xrcOgCGIfq0-ksiujJ1xpZcActSrMHHSinxo9InUldiYdEl7cE4PyhdkyOI7iCjlZi2eb4oUOocEsZNyoZRZPJ4uNV0foet5hnuaP1Cpa_BVOVZMv16zJmHZWaIloVzyqvKL2ArI6ByFr1BRqNsoW9ucFq6I5wtn-a9aG_',
        ];

        $fallbackBadges = ['AGRO', 'ACADEMIA', 'INSTITUCIONAL'];

        if ($this->canQueryTable('posts')) {
            $news = Post::query()
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->values()
                ->map(function (Post $post, int $index) use ($fallbackImages, $fallbackBadges): array {
                    return [
                        'title' => $post->title,
                        'excerpt' => $post->excerpt ?: Str::limit(strip_tags((string) $post->content), 120),
                        'date' => optional($post->published_at)->translatedFormat('d M Y') ?? 'Publicacion institucional',
                        'image_url' => $this->resolveMediaUrl($post->cover_image_path) ?: ($fallbackImages[$index] ?? null),
                        'badge' => $fallbackBadges[$index] ?? 'NOTICIAS',
                        'url' => route('noticias.show', ['slug' => $post->slug]),
                    ];
                });

            if ($news->isNotEmpty()) {
                return $news;
            }
        }

        return collect([
            [
                'title' => 'Nueva cosecha en la Granja Experimental: resultados destacados',
                'excerpt' => 'Estudiantes y docentes compartieron logros del semestre en procesos de cultivo sostenible.',
                'date' => 'Comunidad educativa',
                'image_url' => $fallbackImages[0],
                'badge' => $fallbackBadges[0],
                'url' => route('noticias.index'),
            ],
            [
                'title' => 'Prueba Saber 11: estrategia institucional para el fortalecimiento academico',
                'excerpt' => 'Se presento una ruta de acompanamiento para estudiantes de los grados superiores.',
                'date' => 'Area academica',
                'image_url' => $fallbackImages[1],
                'badge' => $fallbackBadges[1],
                'url' => route('noticias.index'),
            ],
            [
                'title' => 'Inscripciones de nuevos ingresos 2026 ya estan abiertas',
                'excerpt' => 'La institucion habilito cronograma, requisitos y canales de orientacion para familias.',
                'date' => 'Secretaria academica',
                'image_url' => $fallbackImages[2],
                'badge' => $fallbackBadges[2],
                'url' => route('noticias.index'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFeaturedProject(): array
    {
        $fallback = [
            'title' => 'Granja Experimental',
            'subtitle' => 'Conoce de cerca nuestro enfoque tecnico agropecuario.',
            'description' => 'Espacio de aprendizaje practico para fortalecer competencias en produccion sostenible, trabajo colaborativo y emprendimiento rural.',
            'cta_label' => 'Conoce nuestros proyectos',
            'cta_url' => route('academico.proyectos-pedagogicos'),
            'gallery' => [
                'https://lh3.googleusercontent.com/aida-public/AB6AXuBp5Jf5GiM_ORrSUAg9AHZcRDIPObAgv8VB2zTDvbKbakIKmZb9CeCRUOQIsvvWcMC2LDJPSjKdhLyBy2dhjzlCBPL6Q1DWg8CPhKeFH5a00y5q-4t-nhgI0igs62ok1Z7FSogCLbtJEvq6WFjCXbjdyPk0g16xdt_X12g5RlHsVjXC4j0lXkE3rsEfedsoLEJt9k4AjGEy84dVjEdTu0mcYxptkS8fh7FFkomGrXZ9iq77kRRM6b1huAy2jcCRoz-bE2D1wHzxlaQk',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuBI98FibxKjA9PEzq3JTDeHU3RDuo2wTAYbWM6lUGZq6j-0_NVChu_uTaug0fn2g56rJjt-Bzcyet-GDDm7dlzkbEXzP6vEK7HIyZh5ba-0dPW_Df2d1O1D0kBmNRTVjpGYgpR0qQbtLujgyJKmZVukUk5FT91cVL6nJEYQ9RmDdKoc3k3I59aATmuQiERcZ8SvqF5EneIL3ID_aWPzpjkuQkKGcQLzTTY8ts4Qo-gbgULJgkmHtHuEJOZZ1_P8RBMH19TKbCw8Ry1I',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuB2AE3dssA5MxblPmghgKjccU4POrISqUBD0l4ecum5faMYCbX507FfQkOQ9tre8794L_tG3rOM2IMn-KWa9kwK3UnaSkWbnwa-dfwSBOGTyrrfMlaGwBnvcTLC_Vd-TrnU1DLL5O504cTaHilbfL7W_hOBc1t8mv1ERYI3GG1mkaMa3Z3XfwLPlf2jQpbVnOLNr_jstFQahmNT8Q677_M9KO2FIyJ8tqzXpD4PLznCjPKU-fV2RsuGzX8jBYXLIBzr1dUWwGIw-11h',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuBXg3sEfFCgxTRrLYjV_HsUEdNxuO-pMzf7mnleN79imtpbwSYMDoDPVXaOewPEZet9FrdExaH6CYlrwI9w68I9kRRR6f9k8jSZJQHL1hX-lQMFEaFM4-ZWldJAT6xrHdQW5MEFQFlnznIAMevdtuTeZucTQpMd5EV0o0HqtCjJHqGXuHy1kpuOa6-BfGAQNkVP_MHIZGw0_MnRlxx2Y214kYGhwdrzoYxWepgxAGaCvlhQlxhu3s6C1IYQjIoGlV0YHdzEODldtk9y',
            ],
        ];

        if (! $this->canQueryTable('projects')) {
            return $fallback;
        }

        /** @var Project|null $project */
        $project = Project::query()
            ->where('status', 'published')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->first();

        if (! $project) {
            return $fallback;
        }

        $projectGallery = collect(is_array($project->gallery_image_paths) ? $project->gallery_image_paths : [])
            ->filter(fn (mixed $path): bool => is_string($path) && trim($path) !== '')
            ->map(fn (string $path): ?string => $this->resolveMediaUrl($path))
            ->filter(fn (?string $url): bool => filled($url))
            ->unique()
            ->values();

        $gallery = $projectGallery
            ->take(4)
            ->values()
            ->pipe(function (Collection $items) use ($fallback): Collection {
                if ($items->count() >= 4) {
                    return $items;
                }

                return $items->concat(collect($fallback['gallery'])->take(4 - $items->count()));
            })
            ->values();

        return [
            'title' => $project->title ?: $fallback['title'],
            'subtitle' => $project->summary ?: $fallback['subtitle'],
            'description' => $project->description
                ? Str::words(strip_tags((string) $project->description), 72)
                : $fallback['description'],
            'cta_label' => $fallback['cta_label'],
            'cta_url' => route('academico.proyectos-pedagogicos'),
            'gallery' => $gallery->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveUpcomingEvents(): Collection
    {
        if ($this->canQueryTable('events')) {
            $events = Event::query()
                ->where('status', 'published')
                ->whereNotNull('starts_at')
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(3)
                ->get()
                ->map(function (Event $event): array {
                    return [
                        'day' => $event->starts_at->format('d'),
                        'month' => Str::upper($event->starts_at->translatedFormat('M')),
                        'title' => $event->title,
                        'time' => $this->formatEventTimeRange($event->starts_at, $event->ends_at, (bool) $event->is_all_day),
                        'location' => $this->normalizeEventLocation($event->location),
                        'url' => route('eventos.show', ['slug' => $event->slug]),
                    ];
                });

            if ($events->isNotEmpty()) {
                return $events;
            }
        }

        return collect([
            [
                'day' => '24',
                'month' => 'ABR',
                'title' => 'Dia del Campesino Institucional',
                'time' => '08:00 AM - 04:00 PM',
                'location' => 'Sede Principal - Granja',
                'url' => route('academico.calendario-academico'),
            ],
            [
                'day' => '15',
                'month' => 'MAY',
                'title' => 'Feria Tecnologica y de Emprendimiento',
                'time' => '09:00 AM - 02:00 PM',
                'location' => 'Polideportivo Institucional',
                'url' => route('academico.calendario-academico'),
            ],
            [
                'day' => '05',
                'month' => 'JUN',
                'title' => 'Ceremonia de Graduacion 2026',
                'time' => '04:00 PM',
                'location' => 'Centro de Eventos Municipal',
                'url' => route('academico.calendario-academico'),
            ],
        ]);
    }
}
