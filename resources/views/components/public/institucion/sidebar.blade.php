@props([
    'pages' => collect(),
])

<div class="public-surface p-4 sm:p-5">
    <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Institucion</p>
    <ul class="mt-3 space-y-1 text-sm">
        @foreach ($pages as $item)
            @php($isActive = request()->routeIs($item['route']))
            <li>
                <a
                    href="{{ route($item['route']) }}"
                    @if ($isActive) aria-current="page" @endif
                    @class([
                        'block rounded-md px-3 py-2 transition',
                        'bg-ied-primary text-white' => $isActive,
                        'text-ied-gray-700 hover:bg-ied-gray-100 hover:text-ied-primary-dark' => ! $isActive,
                    ])
                >
                    {{ $item['title'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
