@extends('layouts.public.app')

@section('title', $title)
@section('meta_description', $lead)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="noticias" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Navegacion</p>
                <ul class="mt-3 space-y-1 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="block rounded-md px-3 py-2 text-ied-gray-700 transition hover:bg-ied-gray-100 hover:text-ied-primary-dark">
                            Inicio
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('noticias.index') }}" class="block rounded-md bg-ied-primary px-3 py-2 text-white">
                            Noticias
                        </a>
                    </li>
                </ul>
            </div>

            <x-public.filter-panel :action="route('noticias.index')" target="#news-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Titulo, resumen o contenido" class="public-filter-input">
                </label>

                <label>
                    <span class="public-filter-label">Categoria</span>
                    <select name="category" class="public-filter-input">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>
                                {{ $category['name'] }} ({{ $category['count'] }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="public-filter-label">Orden</span>
                    <select name="sort" class="public-filter-input">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </x-public.filter-panel>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section id="news-results" class="space-y-4">
                @if ($featuredNews->isNotEmpty())
                    <section class="space-y-4 border-b border-ied-gray-200 pb-6">
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Noticias destacadas</h2>
                        <div @class([
                            'grid gap-6',
                            'md:grid-cols-2' => $featuredNews->count() >= 2,
                            'xl:grid-cols-3' => $featuredNews->count() >= 3,
                        ])>
                            @foreach ($featuredNews as $item)
                                <x-public.home.news-card
                                    :title="$item['title']"
                                    :excerpt="$item['excerpt']"
                                    :date="$item['published_at']"
                                    :url="$item['detail_url']"
                                    :image-url="$item['image_url']"
                                    :badge="$item['categories'][0]['name'] ?? 'Destacada'"
                                />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($items->count() === 0 && $featuredNews->isEmpty())
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No se encontraron noticias con los filtros aplicados.
                    </div>
                @elseif ($items->count() > 0)
                    @if ($featuredNews->isNotEmpty())
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Todas las noticias</h2>
                    @endif

                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $item)
                            <x-public.home.news-card
                                :title="$item['title']"
                                :excerpt="$item['excerpt']"
                                :date="$item['published_at']"
                                :url="$item['detail_url']"
                                :image-url="$item['image_url']"
                                :badge="$item['categories'][0]['name'] ?? null"
                            />
                        @endforeach
                    </div>

                    <div class="pt-2">
                        {{ $items->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
