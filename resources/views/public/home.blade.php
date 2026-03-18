@extends('layouts.public.app')

@section('title', 'Inicio')

@section('content')
    <section class="relative flex min-h-[37.5rem] items-center overflow-hidden bg-slate-900 text-white">
        @if ($hero['image_url'])
            <img
                src="{{ $hero['image_url'] }}"
                alt="{{ $hero['title'] }}"
                class="absolute inset-0 h-full w-full object-cover opacity-65"
                loading="eager"
            />
        @else
            <div class="absolute inset-0 h-full w-full bg-linear-to-r from-[#08131d] via-[#143329] to-[#38680d]" aria-hidden="true"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/45 to-transparent" aria-hidden="true"></div>

        <div class="public-container relative py-20">
            <div class="public-content-shell">
                <div class="max-w-2xl">
                    <span class="inline-block rounded-full border border-ied-primary/30 bg-ied-primary/25 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-white">
                        {{ $hero['eyebrow'] }}
                    </span>
                    <h1 class="public-heading mt-6 text-4xl font-black leading-[1.04] tracking-[-0.03em] text-white sm:text-5xl lg:text-6xl">
                        {{ $hero['title'] }}
                    </h1>
                    <p class="mt-5 max-w-xl text-[17px] leading-relaxed text-slate-200 sm:text-lg">
                        {{ $hero['description'] }}
                    </p>

                    <div class="mt-10">
                        <a
                            href="{{ $hero['cta_url'] }}"
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

    <section class="relative z-20 -mt-16 pb-20">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($quickLinks as $item)
                    <x-public.home.quick-link-card
                        :title="$item['title']"
                        :description="$item['description']"
                        :url="$item['route']"
                        :icon="$item['icon']"
                    />
                @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#f5f7f6] py-20">
        <div class="public-container">
            <div class="public-content-shell">
                <x-public.home.section-heading
                    title="Muro de Actualidad"
                    action-label="Ver todas las noticias"
                    :action-url="route('comunidad.index')"
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
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-ied-primary/5 py-24">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="space-y-10">
                        <div>
                            <h2 class="public-heading text-4xl font-black tracking-[-0.02em] text-slate-900 lg:text-5xl">{{ $featuredProject['title'] }}</h2>
                            <p class="mt-2 text-sm font-bold text-ied-primary">{{ $featuredProject['subtitle'] }}</p>
                        </div>

                        <p class="text-lg leading-relaxed text-slate-600 lg:text-xl">
                            {{ $featuredProject['description'] }}
                        </p>

                        <ul class="grid gap-4 sm:grid-cols-2">
                            @foreach ($featuredProject['highlights'] as $highlight)
                                <li class="flex items-start gap-3 text-sm text-slate-600">
                                    <span class="material-symbols-outlined rounded-lg bg-ied-primary/10 p-2 text-ied-primary !text-[18px]" aria-hidden="true">
                                        {{ $highlight['icon'] ?? 'eco' }}
                                    </span>
                                    <span>{{ $highlight['label'] ?? $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a
                            href="{{ $featuredProject['cta_url'] }}"
                            class="inline-flex items-center rounded-full bg-ied-primary px-8 py-4 text-base font-bold text-white transition hover:bg-ied-primary-dark"
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

    <section class="bg-white py-20">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="mb-12 flex items-center gap-3">
                    <span class="material-symbols-outlined text-ied-primary !text-[30px]" aria-hidden="true">event</span>
                    <h2 class="public-heading text-3xl font-black tracking-[-0.02em] text-slate-900">Proximos Eventos</h2>
                </div>

                <div class="flex flex-col gap-4">
                    @foreach ($upcomingEvents as $event)
                        <x-public.home.event-item
                            :day="$event['day']"
                            :month="$event['month']"
                            :title="$event['title']"
                            :meta="$event['meta']"
                            :url="$event['url']"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
