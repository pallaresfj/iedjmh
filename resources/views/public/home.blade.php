@extends('layouts.public.app')

@section('title', 'Inicio')

@section('content')
    @php($heroOpensInNewTab = ($hero['cta_target'] ?? '_self') === '_blank')

    <section class="public-home-hero relative flex min-h-[37.5rem] items-center overflow-hidden text-white">
        @if ($hero['image_url'])
            <img
                src="{{ $hero['image_url'] }}"
                alt="{{ $hero['title'] }}"
                class="absolute inset-0 z-0 h-full w-full object-cover opacity-65"
                loading="eager"
            />
        @else
            <div class="public-home-hero__fallback absolute inset-0 z-0 h-full w-full" aria-hidden="true"></div>
        @endif

        <div class="public-home-hero__overlay absolute inset-0 z-10" aria-hidden="true"></div>

        <div class="public-container relative z-20 py-20">
            <div class="public-content-shell">
                <div class="max-w-2xl">
                    <span class="public-home-hero__eyebrow inline-block rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-[0.16em]">
                        {{ $hero['eyebrow'] }}
                    </span>
                    <h1 class="public-heading mt-6 text-4xl font-black leading-[1.04] tracking-[-0.03em] text-white sm:text-5xl lg:text-6xl">
                        {{ $hero['title'] }}
                    </h1>
                    <p class="public-home-hero__description mt-5 max-w-xl text-[17px] leading-relaxed sm:text-lg">
                        {{ $hero['description'] }}
                    </p>

                    <div class="mt-10">
                        <a
                            href="{{ $hero['cta_url'] }}"
                            target="{{ $heroOpensInNewTab ? '_blank' : '_self' }}"
                            @if ($heroOpensInNewTab) rel="noopener noreferrer" @endif
                            class="inline-flex items-center gap-2 rounded-full bg-ied-primary px-8 py-4 text-base font-bold text-white shadow-lg shadow-ied-primary/30 transition hover:bg-ied-primary-dark"
                        >
                            <span>{{ $hero['cta_label'] }}</span>
                            <span class="material-symbols-outlined !text-[20px]" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-home-quick-links-section relative z-20 -mt-16 pb-20">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($quickLinks as $item)
                    <x-public.home.quick-link-card
                        :title="$item['title']"
                        :description="$item['description']"
                        :url="$item['route']"
                        :icon="$item['icon']"
                        tone="home"
                    />
                @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="public-home-news-section py-20">
        <div class="public-container">
            <div class="public-content-shell">
                <x-public.home.section-heading
                    title="Muro de Actualidad"
                    action-label="Ver todas las noticias"
                    :action-url="route('noticias.index')"
                    tone="home"
                />

                <div class="grid gap-10 md:grid-cols-3">
                    @foreach ($newsItems as $item)
                        <x-public.home.news-card
                            :title="$item['title']"
                            :excerpt="$item['excerpt']"
                            :date="$item['date']"
                            :url="$item['url']"
                            :image-url="$item['image_url']"
                            :badge="$item['badge'] ?? null"
                            tone="home"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="public-home-featured-section py-24">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="space-y-10">
                        <div>
                            <h2 class="public-home-featured__title public-heading text-4xl font-black tracking-[-0.02em] lg:text-5xl">{{ $featuredProject['title'] }}</h2>
                            <p class="public-home-featured__subtitle mt-2 text-sm font-bold">{{ $featuredProject['subtitle'] }}</p>
                        </div>

                        <p class="public-home-featured__description text-lg leading-relaxed lg:text-xl">
                            {{ $featuredProject['description'] }}
                        </p>

                        <a
                            href="{{ $featuredProject['cta_url'] }}"
                            class="public-home-featured__cta inline-flex items-center rounded-full px-8 py-4 text-base font-bold transition"
                        >
                            {{ $featuredProject['cta_label'] }}
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <x-public.home.media-tile
                                :image-url="$featuredProject['gallery'][0]"
                                alt="Proyecto destacado 1"
                                class="aspect-[4/5]"
                            />
                            <x-public.home.media-tile
                                :image-url="$featuredProject['gallery'][1]"
                                alt="Proyecto destacado 2"
                                class="aspect-square"
                            />
                        </div>
                        <div class="space-y-4 pt-8">
                            <x-public.home.media-tile
                                :image-url="$featuredProject['gallery'][2]"
                                alt="Proyecto destacado 3"
                                class="aspect-square"
                            />
                            <x-public.home.media-tile
                                :image-url="$featuredProject['gallery'][3]"
                                alt="Proyecto destacado 4"
                                class="aspect-[4/5]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-home-events-section py-20">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="mb-12 flex items-center gap-3">
                    <span class="public-home-events__icon material-symbols-outlined !text-[30px]" aria-hidden="true">event</span>
                    <h2 class="public-home-events__title public-heading text-3xl font-black tracking-[-0.02em]">Próximos Eventos</h2>
                </div>

                <div class="flex flex-col gap-6">
                    @foreach ($upcomingEvents as $event)
                        <x-public.home.event-item
                            :day="$event['day']"
                            :month="$event['month']"
                            :title="$event['title']"
                            :time="$event['time'] ?? null"
                            :location="$event['location'] ?? null"
                            :meta="$event['meta'] ?? null"
                            :url="$event['url']"
                            :highlight-date="$loop->first"
                            tone="home"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
