@props([
    'title',
    'summary' => null,
    'route',
    'icon' => 'article',
])

<a href="{{ route($route) }}" class="group flex flex-col rounded-2xl border border-ied-gray-200 bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-md">
    <span class="inline-flex size-12 items-center justify-center rounded-xl bg-ied-primary-light/30">
        <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">{{ $icon }}</span>
    </span>

    <p class="mt-4 text-lg font-bold text-ied-gray-900">{{ $title }}</p>

    @if ($summary)
        <p class="mt-2 flex-1 text-sm leading-relaxed text-ied-gray-600">{{ $summary }}</p>
    @endif

    <p class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition group-hover:text-ied-primary">
        Ver contenido
        <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
        </svg>
    </p>
</a>
