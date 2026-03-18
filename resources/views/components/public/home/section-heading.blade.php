@props([
    'title',
    'subtitle' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="mb-10 flex flex-wrap items-end justify-between gap-3 sm:mb-12">
    <div>
        <h2 class="public-heading text-3xl font-black tracking-[-0.02em] text-slate-900 sm:text-4xl">{{ $title }}</h2>
        <div class="mt-2 h-1 w-20 rounded bg-ied-primary"></div>
        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1 text-sm font-bold text-ied-primary transition hover:underline">
            <span>{{ $actionLabel }}</span>
            <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">chevron_right</span>
        </a>
    @endif
</div>
