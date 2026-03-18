@props([
    'campus',
])

<article class="public-surface p-5 sm:p-6">
    <h3 class="public-heading text-lg font-semibold text-ied-gray-900">{{ $campus['name'] }}</h3>

    @if (! empty($campus['description']))
        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $campus['description'] }}</p>
    @endif

    <ul class="mt-4 space-y-1 text-sm text-ied-gray-700">
        @if (! empty($campus['address']))
            <li><span class="font-semibold text-ied-gray-900">Direccion:</span> {{ $campus['address'] }}</li>
        @endif
        @if (! empty($campus['phone']))
            <li><span class="font-semibold text-ied-gray-900">Telefono:</span> {{ $campus['phone'] }}</li>
        @endif
        @if (! empty($campus['email']))
            <li>
                <span class="font-semibold text-ied-gray-900">Correo:</span>
                <a href="mailto:{{ $campus['email'] }}" class="text-ied-primary-dark hover:text-ied-primary">{{ $campus['email'] }}</a>
            </li>
        @endif
    </ul>

    @if (! empty($campus['map_url']))
        <a href="{{ $campus['map_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">
            Ver ubicacion
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06l2.97-2.97H5a.75.75 0 010-1.5h8.19l-2.97-2.97a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </a>
    @endif
</article>
