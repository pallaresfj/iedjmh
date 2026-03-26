@props([
    'title',
    'subtitle' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'tone' => 'default',
])

<div class="mb-10 flex flex-wrap items-end justify-between gap-3 sm:mb-12">
    <div>
        <h2 @class([
            'public-heading text-3xl font-black tracking-[-0.02em] sm:text-4xl',
            'text-slate-900' => $tone !== 'home',
            'public-home-section-heading__title' => $tone === 'home',
        ])>{{ $title }}</h2>
        <div class="mt-2 h-1 w-20 rounded bg-ied-primary"></div>
        @if ($subtitle)
            <p @class([
                'mt-3 max-w-2xl text-sm sm:text-base',
                'text-slate-600' => $tone !== 'home',
                'public-home-section-heading__subtitle' => $tone === 'home',
            ])>{{ $subtitle }}</p>
        @endif
    </div>

    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" @class([
            'inline-flex items-center gap-1 text-sm font-bold transition hover:underline',
            'text-ied-primary' => $tone !== 'home',
            'public-home-section-heading__action' => $tone === 'home',
        ])>
            <span>{{ $actionLabel }}</span>
            <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">chevron_right</span>
        </a>
    @endif
</div>
