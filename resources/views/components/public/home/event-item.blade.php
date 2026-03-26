@props([
    'day',
    'month',
    'title',
    'time' => null,
    'location' => null,
    'meta' => null,
    'url' => null,
    'highlightDate' => false,
    'tone' => 'default',
])

@php($wrapperTag = filled($url) ? 'a' : 'article')
@php($wrapperAttrs = filled($url) ? ['href' => $url] : [])
@php($hasStructuredMeta = filled($time) || filled($location))
@php($metaFallback = filled($meta) ? $meta : null)
@php($monthLabel = strtoupper(rtrim(trim((string) $month), '. ')))

<{{ $wrapperTag }} {{ $attributes->merge($wrapperAttrs)->class([
    'group flex items-center gap-6 rounded-2xl px-6 py-6 transition-all sm:px-7',
    'border border-slate-100 bg-white hover:border-ied-primary/30 hover:bg-slate-50/35' => $tone !== 'home',
    'public-home-event-item border' => $tone === 'home',
]) }}>
    <div @class([
        'flex size-24 shrink-0 flex-col items-center justify-center rounded-[1.6rem] border px-2 py-2 text-center leading-none shadow-sm',
        'border-ied-primary bg-ied-primary text-white' => $highlightDate && $tone !== 'home',
        'border-slate-200 bg-slate-100 text-slate-900' => ! $highlightDate && $tone !== 'home',
        'public-home-event-item__date--highlight' => $highlightDate && $tone === 'home',
        'public-home-event-item__date--default' => ! $highlightDate && $tone === 'home',
    ])>
        <span class="public-heading text-4xl font-black leading-none tracking-[-0.03em]">{{ $day }}</span>
        <span @class([
            'mt-0.5 text-[0.66rem] font-extrabold uppercase leading-none tracking-[0.18em]',
            'text-white/90' => $highlightDate && $tone !== 'home',
            'text-slate-800' => ! $highlightDate && $tone !== 'home',
            'public-home-event-item__date-month--highlight' => $highlightDate && $tone === 'home',
            'public-home-event-item__date-month--default' => ! $highlightDate && $tone === 'home',
        ])>{{ $monthLabel }}</span>
    </div>

    <div class="min-w-0 flex-1">
        <h3 @class([
            'public-heading text-xl font-bold leading-tight tracking-[-0.01em] transition',
            'text-slate-900 group-hover:text-ied-primary-dark' => $tone !== 'home',
            'public-home-event-item__title' => $tone === 'home',
        ])>{{ $title }}</h3>

        @if ($hasStructuredMeta)
            <p @class([
                'mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm font-medium',
                'text-slate-500' => $tone !== 'home',
                'public-home-event-item__meta' => $tone === 'home',
            ])>
                @if (filled($time))
                    <span class="inline-flex items-center gap-1.5">
                        <span @class([
                            'material-symbols-outlined !text-[16px]',
                            'text-slate-400' => $tone !== 'home',
                            'public-home-event-item__meta-icon' => $tone === 'home',
                        ]) aria-hidden="true">schedule</span>
                        <span>{{ $time }}</span>
                    </span>
                @endif

                @if (filled($location))
                    <span class="inline-flex items-center gap-1.5">
                        <span @class([
                            'material-symbols-outlined !text-[16px]',
                            'text-slate-400' => $tone !== 'home',
                            'public-home-event-item__meta-icon' => $tone === 'home',
                        ]) aria-hidden="true">location_on</span>
                        <span>{{ $location }}</span>
                    </span>
                @endif
            </p>
        @elseif ($metaFallback)
            <p @class([
                'mt-1.5 text-sm font-normal',
                'text-slate-500' => $tone !== 'home',
                'public-home-event-item__meta' => $tone === 'home',
            ])>{{ $metaFallback }}</p>
        @endif
    </div>

    @if (filled($url))
        <span @class([
            'material-symbols-outlined hidden shrink-0 !text-[24px] transition sm:block',
            'text-slate-300 group-hover:text-ied-primary' => $tone !== 'home',
            'public-home-event-item__arrow' => $tone === 'home',
        ]) aria-hidden="true">calendar_add_on</span>
    @endif
</{{ $wrapperTag }}>
