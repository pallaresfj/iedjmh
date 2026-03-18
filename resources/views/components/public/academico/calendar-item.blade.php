@props([
    'item',
])

@php($wrapperTag = ! empty($item['url']) ? 'a' : 'article')
@php($wrapperAttrs = ! empty($item['url']) ? ['href' => $item['url'], 'target' => '_blank', 'rel' => 'noopener noreferrer'] : [])

<{{ $wrapperTag }} {{ $attributes->merge($wrapperAttrs)->class('group flex items-start gap-4 rounded-2xl px-1 py-4 transition hover:bg-ied-gray-100/70 sm:gap-5') }}>
    <div class="grid min-w-14 place-items-center rounded-xl bg-ied-primary px-2 py-2 text-white shadow-sm">
        <span class="public-heading text-xl font-semibold leading-none">{{ $item['day'] }}</span>
        <span class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-white/90">{{ $item['month'] }}</span>
    </div>

    <div class="min-w-0 flex-1 pt-1">
        <h3 class="public-heading text-base font-semibold text-ied-gray-900 transition group-hover:text-ied-primary-dark sm:text-lg">{{ $item['title'] }}</h3>
        @if (! empty($item['meta']))
            <p class="mt-1 text-sm text-ied-gray-600">{{ $item['meta'] }}</p>
        @endif
    </div>

    @if (! empty($item['url']))
        <svg class="mt-1 hidden size-5 shrink-0 text-ied-gray-400 transition group-hover:text-ied-primary sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06l2.97-2.97H5a.75.75 0 010-1.5h8.19l-2.97-2.97a.75.75 0 010-1.06z" clip-rule="evenodd" />
        </svg>
    @endif
</{{ $wrapperTag }}>
