@props([
    'item',
])

<article class="public-surface overflow-hidden">
    @if (! empty($item['image_url']))
        <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="h-40 w-full object-cover" loading="lazy" />
    @else
        <div class="h-40 w-full bg-linear-to-br from-ied-primary-light/35 via-ied-primary/15 to-ied-gray-100"></div>
    @endif

    <div class="p-5">
        <h3 class="public-heading text-lg font-semibold text-ied-gray-900">{{ $item['title'] }}</h3>
        @if (! empty($item['summary']))
            <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
        @endif
        @if (! empty($item['period']))
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ied-primary-dark">{{ $item['period'] }}</p>
        @endif
    </div>
</article>
