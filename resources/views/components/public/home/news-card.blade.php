@props([
    'title',
    'excerpt' => null,
    'date' => null,
    'url' => '#',
    'imageUrl' => null,
    'badge' => null,
    'tone' => 'default',
])

<article @class([
    'group flex h-full flex-col gap-4',
    'public-home-news-card rounded-2xl border p-4 sm:p-5' => $tone === 'home',
])>
    <a href="{{ $url }}" class="block">
        @if ($imageUrl)
            <div @class([
                'relative overflow-hidden rounded-xl',
                'public-home-news-card__media' => $tone === 'home',
            ])>
                <img src="{{ $imageUrl }}" alt="{{ $title }}" class="aspect-video w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                @if ($badge)
                    <span class="absolute left-3 top-3 rounded bg-ied-primary px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white">
                        {{ $badge }}
                    </span>
                @endif
            </div>
        @else
            <div @class([
                'relative aspect-video rounded-xl bg-linear-to-br from-ied-primary-light/35 via-ied-primary/15 to-ied-gray-100',
                'public-home-news-card__fallback' => $tone === 'home',
            ])>
                @if ($badge)
                    <span class="absolute left-3 top-3 rounded bg-ied-primary px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white">
                        {{ $badge }}
                    </span>
                @endif
            </div>
        @endif
    </a>

    <div class="space-y-2">
        <h3 @class([
            'public-heading text-lg font-bold leading-tight tracking-[-0.01em] transition sm:text-xl',
            'text-slate-900 group-hover:text-ied-primary-dark' => $tone !== 'home',
            'public-home-news-card__title' => $tone === 'home',
        ])>
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>
        @if ($excerpt)
            <p @class([
                'line-clamp-2 text-sm leading-relaxed',
                'text-slate-500' => $tone !== 'home',
                'public-home-news-card__excerpt' => $tone === 'home',
            ])>{{ $excerpt }}</p>
        @endif
        <div class="flex items-center justify-between pt-1">
            <a href="{{ $url }}" @class([
                'inline-flex items-center gap-1 text-sm font-bold',
                'text-ied-primary' => $tone !== 'home',
                'public-home-news-card__link' => $tone === 'home',
            ])>
                Leer mas
                <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">arrow_forward</span>
            </a>
            @if ($date)
                <p @class([
                    'text-xs font-medium',
                    'text-slate-500' => $tone !== 'home',
                    'public-home-news-card__date' => $tone === 'home',
                ])>{{ $date }}</p>
            @endif
        </div>
    </div>
</article>
