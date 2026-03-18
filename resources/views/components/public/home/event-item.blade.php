@props([
    'day',
    'month',
    'title',
    'meta' => null,
    'url' => '#',
])

<a href="{{ $url }}" class="group flex items-start gap-4 rounded-2xl border border-slate-100 bg-white px-4 py-5 transition hover:border-ied-primary/30 hover:bg-ied-gray-100/35 sm:gap-6 sm:px-6">
    <div class="grid size-24 shrink-0 place-items-center rounded-xl bg-ied-primary px-2 py-2 text-white shadow-sm">
        <span class="public-heading text-3xl font-black leading-none tracking-[-0.02em]">{{ $day }}</span>
        <span class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-white/90">{{ $month }}</span>
    </div>

    <div class="min-w-0 flex-1 pt-1">
        <h3 class="public-heading text-lg font-bold tracking-[-0.01em] text-slate-900 transition group-hover:text-ied-primary-dark sm:text-xl">{{ $title }}</h3>
        @if ($meta)
            <p class="mt-1 text-sm text-slate-500">{{ $meta }}</p>
        @endif
    </div>

    <span class="material-symbols-outlined mt-1 hidden !text-[20px] text-slate-300 transition group-hover:text-ied-primary sm:block" aria-hidden="true">calendar_add_on</span>
</a>
