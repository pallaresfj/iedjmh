@props([
    'title',
    'lead' => null,
    'banner' => null,
    'sectionKey' => null,
    'replaceHeaderWithBanner' => false,
    'forceBannerTitleStyle' => false,
])

@php($primaryNav = config('institution.navigation.primary', []))
@php($hasBanner = is_array($banner))
@php($forceBannerTitleStyle = (bool) $forceBannerTitleStyle)
@php($effectiveBanner = $banner)

@if ($forceBannerTitleStyle && ! $hasBanner)
    @php($effectiveBanner = [
        'title' => $title,
        'subtitle' => null,
        'description' => $lead,
        'image_url' => null,
        'cta_label' => null,
        'cta_url' => null,
        'target' => '_self',
        'is_fallback' => true,
    ])
@endif

@php($hasEffectiveBanner = is_array($effectiveBanner))
@php($hideClassicHeader = $forceBannerTitleStyle || ($replaceHeaderWithBanner && $hasBanner))

@if (! $hideClassicHeader)
    <section class="border-b border-ied-gray-200 bg-white">
        <div class="public-container py-10 sm:py-12">
            <p class="text-sm font-medium uppercase tracking-wide text-ied-primary-dark">Seccion institucional</p>
            <h1 class="public-heading mt-2 text-3xl font-semibold text-ied-gray-900 sm:text-4xl">{{ $title }}</h1>
            @if ($lead)
                <p class="mt-3 max-w-3xl text-base leading-relaxed text-ied-gray-700 sm:text-lg">{{ $lead }}</p>
            @endif
        </div>
    </section>
@endif

@if ($hasEffectiveBanner)
    @php($opensInNewTab = ($effectiveBanner['target'] ?? '_self') === '_blank')
    @php($bannerHasImage = filled($effectiveBanner['image_url'] ?? null))
    @php($bannerIsFallback = (bool) ($effectiveBanner['is_fallback'] ?? false))
    <section class="public-internal-banner-section public-banner-full-bleed border-b border-ied-gray-200 bg-ied-gray-100/40">
        <div @class([
            'public-internal-banner',
            'public-internal-banner--with-image' => $bannerHasImage,
            'public-internal-banner--without-image' => ! $bannerHasImage,
            'public-internal-banner--fallback' => $bannerIsFallback,
        ])>
            @if ($bannerHasImage)
                <img src="{{ $effectiveBanner['image_url'] }}" alt="" class="public-internal-banner__image" loading="lazy" aria-hidden="true" />
            @endif

            <div class="public-internal-banner__overlay" aria-hidden="true"></div>

            <div class="public-internal-banner__content">
                <div class="public-container w-full">
                    <div class="max-w-3xl space-y-4 sm:space-y-5">
                        @if (filled($effectiveBanner['subtitle'] ?? null))
                            <p class="public-internal-banner__eyebrow">{{ $effectiveBanner['subtitle'] }}</p>
                        @endif

                        <h2 class="public-internal-banner__title">
                            {{ $effectiveBanner['title'] ?? $title }}
                        </h2>

                        @if (filled($effectiveBanner['description'] ?? null))
                            <p class="public-internal-banner__description">
                                {{ $effectiveBanner['description'] }}
                            </p>
                        @endif

                        @if (filled($effectiveBanner['cta_url'] ?? null) && filled($effectiveBanner['cta_label'] ?? null))
                            <a
                                href="{{ $effectiveBanner['cta_url'] }}"
                                target="{{ $opensInNewTab ? '_blank' : '_self' }}"
                                @if ($opensInNewTab) rel="noopener noreferrer" @endif
                                class="public-internal-banner__cta"
                            >
                                {{ $effectiveBanner['cta_label'] }}
                            </a>
                        @endif
                    </div>
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
