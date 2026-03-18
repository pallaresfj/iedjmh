@props([
    'title',
    'summary' => null,
    'route',
])

<a href="{{ route($route) }}" class="public-surface group block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
    <p class="public-heading text-lg font-semibold text-ied-gray-900 transition group-hover:text-ied-primary-dark">{{ $title }}</p>
    @if ($summary)
        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $summary }}</p>
    @endif
    <p class="mt-4 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">
        Ver pagina
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
        </svg>
    </p>
</a>
