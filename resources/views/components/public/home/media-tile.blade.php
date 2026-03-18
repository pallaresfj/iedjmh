@props([
    'imageUrl' => null,
    'alt' => '',
])

<div {{ $attributes->class(['overflow-hidden rounded-2xl shadow-sm']) }}>
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $alt }}" class="h-full w-full object-cover transition duration-300 hover:scale-105" loading="lazy" />
    @else
        <div class="h-full min-h-28 w-full bg-linear-to-br from-ied-primary-light/40 via-ied-primary/15 to-ied-gray-100"></div>
    @endif
</div>
