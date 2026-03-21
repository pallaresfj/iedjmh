@props([
    'title',
    'lead' => null,
    'banner' => null,
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

@if (is_array($banner))
    @php($opensInNewTab = ($banner['target'] ?? '_self') === '_blank')
    @php($bannerHasImage = filled($banner['image_url'] ?? null))
    <section class="border-b border-ied-gray-200 bg-ied-gray-100/40">
        <div class="public-container py-6 sm:py-7">
            <div class="public-surface overflow-hidden p-0">
                <div @class([
                    'grid gap-0',
                    'md:grid-cols-[minmax(0,1fr)_19rem]' => $bannerHasImage,
                ])>
                    <div class="space-y-3 p-5 sm:p-6">
                        @if (filled($banner['subtitle'] ?? null))
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ied-primary-dark">
                                {{ $banner['subtitle'] }}
                            </p>
                        @endif

                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900 sm:text-2xl">
                            {{ $banner['title'] }}
                        </h2>

                        @if (filled($banner['description'] ?? null))
                            <p class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                                {{ $banner['description'] }}
                            </p>
                        @endif

                        @if (filled($banner['cta_url'] ?? null) && filled($banner['cta_label'] ?? null))
                            <a
                                href="{{ $banner['cta_url'] }}"
                                target="{{ $opensInNewTab ? '_blank' : '_self' }}"
                                @if ($opensInNewTab) rel="noopener noreferrer" @endif
                                class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark"
                            >
                                {{ $banner['cta_label'] }}
                            </a>
                        @endif
                    </div>

                    @if ($bannerHasImage)
                        <div class="h-full min-h-44 border-t border-ied-gray-200 md:border-t-0 md:border-l">
                            <img src="{{ $banner['image_url'] }}" alt="{{ $banner['title'] }}" class="h-full w-full object-cover" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

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
                            <a href="{{ route('academico.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                                Academico
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
