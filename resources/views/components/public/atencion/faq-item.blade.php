@props([
    'item',
])

<details class="public-surface group p-5">
    <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
        <div>
            <p class="public-heading text-base font-semibold text-ied-gray-900">{{ $item['question'] }}</p>
            @if (! empty($item['category']))
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-ied-primary-dark">{{ $item['category'] }}</p>
            @endif
        </div>
        <span class="mt-0.5 inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-ied-gray-100 text-ied-gray-600 transition group-open:rotate-180">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </span>
    </summary>

    <div class="mt-3 border-t border-ied-gray-200 pt-3 text-sm leading-relaxed text-ied-gray-700">
        {!! nl2br(e($item['answer'])) !!}
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ied-gray-600">
            @if (! empty($item['published_at']))
                <span><span class="font-semibold text-ied-gray-900">Publicado:</span> {{ $item['published_at'] }}</span>
            @endif
            @if (! empty($item['updated_at']))
                <span><span class="font-semibold text-ied-gray-900">Actualizado:</span> {{ $item['updated_at'] }}</span>
            @endif
        </div>
    </div>
</details>
