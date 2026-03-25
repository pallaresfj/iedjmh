<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    use ResolvesPublicContent;

    public function show(string $slug): View
    {
        abort_unless($this->canQueryTable('events'), 404);
        $calendarPage = $this->publishedPageBySlug('academico-calendario-academico');

        /** @var Event $event */
        $event = Event::query()
            ->with('categories')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedEvents = Event::query()
            ->where('status', 'published')
            ->whereNotNull('starts_at')
            ->whereKeyNot($event->getKey())
            ->orderBy('starts_at')
            ->limit(4)
            ->get()
            ->map(fn (Event $relatedEvent): array => [
                'day' => $relatedEvent->starts_at?->format('d') ?? '--',
                'month' => $relatedEvent->starts_at?->translatedFormat('M') ? Str::upper($relatedEvent->starts_at->translatedFormat('M')) : '---',
                'title' => $relatedEvent->title,
                'time' => $this->formatEventTimeRange($relatedEvent->starts_at, $relatedEvent->ends_at, (bool) $relatedEvent->is_all_day),
                'location' => $this->normalizeEventLocation($relatedEvent->location),
                'url' => route('eventos.show', ['slug' => $relatedEvent->slug]),
            ]);

        return view('public.eventos.show', [
            'event' => [
                'title' => $event->title,
                'summary' => $event->summary,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->translatedFormat('d M Y h:i A'),
                'ends_at' => $event->ends_at?->translatedFormat('d M Y h:i A'),
                'is_all_day' => $event->is_all_day,
                'registration_url' => $event->registration_url,
                'published_at' => $event->published_at?->translatedFormat('d M Y'),
                'categories' => $event->categories
                    ->map(fn (Category $category): array => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values(),
            ],
            'relatedEvents' => $relatedEvents,
            'banner' => $this->resolvePageBanner($calendarPage),
        ]);
    }
}
