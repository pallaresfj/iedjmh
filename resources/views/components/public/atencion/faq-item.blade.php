@props([
    'item',
])

<details class="group rounded-2xl border border-ied-gray-200 bg-white p-5">
    <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
        <div class="flex gap-4">
            <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30 sm:inline-flex">
                <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">help</span>
            </span>

            <div class="min-w-0">
                <p class="public-heading text-base font-semibold text-ied-gray-900">{{ $item['question'] }}</p>
                @if (! empty($item['category']))
                    <span class="mt-1.5 inline-block rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-0.5 text-[11px] font-semibold text-ied-primary-dark">{{ $item['category'] }}</span>
                @endif
            </div>
        </div>
        <span class="mt-0.5 inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-ied-primary-light/30 text-ied-primary transition group-open:rotate-180">
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
