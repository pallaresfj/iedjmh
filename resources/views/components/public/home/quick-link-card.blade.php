@props([
    'title',
    'description' => null,
    'url' => '#',
    'icon' => 'link',
])

<a href="{{ $url }}" class="group block rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40 transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-slate-200/60">
    <span class="mb-4 inline-flex size-11 items-center justify-center rounded-xl bg-ied-primary/10 text-ied-primary transition group-hover:bg-ied-primary group-hover:text-white">
        <span class="material-symbols-outlined !text-[24px]" aria-hidden="true">{{ $icon }}</span>
    </span>

    <div>
        <p class="public-heading text-base font-bold tracking-[-0.01em] text-slate-900 sm:text-[17px]">{{ $title }}</p>
        @if ($description)
            <p class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-[13px]">{{ $description }}</p>
        @endif
    </div>
</a>
