@props([
    'item',
])

<article class="public-surface p-5">
    <h3 class="public-heading text-lg font-semibold text-ied-gray-900">{{ $item['title'] }}</h3>

    @if (! empty($item['summary']))
        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
    @endif

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ied-gray-600">
        @if (! empty($item['number']))
            <span><span class="font-semibold text-ied-gray-900">Codigo:</span> {{ $item['number'] }}</span>
        @endif
        @if (! empty($item['date']))
            <span><span class="font-semibold text-ied-gray-900">Fecha:</span> {{ $item['date'] }}</span>
        @endif
    </div>

    @if (! empty($item['url']))
        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">
            Ver documento
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06l2.97-2.97H5a.75.75 0 010-1.5h8.19l-2.97-2.97a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </a>
    @endif
</article>
