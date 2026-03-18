@props([
    'title',
    'lead' => null,
    'sectionKey' => null,
])

@php($primaryNav = config('institution.navigation.primary', []))

<section class="border-b border-ied-gray-200 bg-white">
    <div class="public-container py-10 sm:py-12">
        <p class="text-sm font-medium uppercase tracking-wide text-ied-primary-dark">Seccion institucional</p>
        <h1 class="public-heading mt-2 text-3xl font-semibold text-ied-gray-900 sm:text-4xl">{{ $title }}</h1>
        @if ($lead)
            <p class="mt-3 max-w-3xl text-base leading-relaxed text-ied-gray-700 sm:text-lg">{{ $lead }}</p>
        @endif
    </div>
</section>

<section class="public-container py-8 sm:py-10 lg:py-12">
    <div class="grid gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="space-y-4">
            @isset($sidebar)
                {{ $sidebar }}
            @else
                <div class="public-surface p-4 sm:p-5">
                    <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Navegacion</p>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach ($primaryNav as $item)
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
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="public-surface p-4 sm:p-5">
                    <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Accesos rapidos</p>
                    <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                        <li>
                            <a href="{{ route('zona-academica.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                                Zona Academica
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('atencion.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                                Atencion al Ciudadano
                            </a>
                        </li>
                    </ul>
                </div>
            @endisset
        </aside>

        <article class="public-surface p-5 sm:p-7 lg:p-8">
            {{ $slot }}
        </article>
    </div>
</section>
