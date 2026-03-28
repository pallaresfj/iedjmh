@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="proyectos" :replace-header-with-banner="true" :force-banner-title-style="true">
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
                        <a href="{{ route('academico.proyectos-pedagogicos') }}" class="block rounded-md px-3 py-2 text-ied-gray-700 transition hover:bg-ied-gray-100 hover:text-ied-primary-dark">
                            Proyectos pedagogicos
                        </a>
                    </li>
                </ul>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="public-surface p-5 sm:p-6">
                <form action="{{ route('proyectos.index') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" data-auto-filter-form data-auto-filter-target="#projects-results">
                    <label class="xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Buscar</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Titulo o descripcion del proyecto"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Categoria</span>
                        <select
                            name="category"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>
                                    {{ $category['name'] }} ({{ $category['count'] }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Orden</span>
                        <select
                            name="sort"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                        <noscript>
                            <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                Aplicar filtros
                            </button>
                        </noscript>
                        <a href="{{ route('proyectos.index') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                            Limpiar
                        </a>
                    </div>
                </form>
            </section>

            <section id="projects-results" class="space-y-4">
                @if ($featuredProject)
                    <section class="public-surface overflow-hidden border-ied-primary/25 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">Proyecto destacado</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-[7.5rem_minmax(0,1fr)] sm:items-center">
                            @if (! empty($featuredProject['image_url']))
                                <img src="{{ $featuredProject['image_url'] }}" alt="{{ $featuredProject['title'] }}" class="h-28 w-full rounded-xl object-cover sm:h-24" loading="lazy" />
                            @else
                                <div class="h-28 w-full rounded-xl bg-linear-to-br from-ied-primary-light/35 via-ied-primary/15 to-ied-gray-100 sm:h-24"></div>
                            @endif

                            <div>
                                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">{{ $featuredProject['title'] }}</h2>
                                @if (! empty($featuredProject['summary']))
                                    <p class="mt-1 text-sm leading-relaxed text-ied-gray-700">{{ $featuredProject['summary'] }}</p>
                                @endif
                                <a href="{{ $featuredProject['detail_url'] }}" class="mt-3 inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($projects->count() === 0)
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No se encontraron proyectos con los filtros aplicados.
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($projects as $item)
                            <article class="public-surface overflow-hidden">
                                @if (! empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="h-40 w-full object-cover" loading="lazy" />
                                @else
                                    <div class="h-40 w-full bg-linear-to-br from-ied-primary-light/35 via-ied-primary/15 to-ied-gray-100"></div>
                                @endif

                                <div class="p-5">
                                    @if ($item['is_featured'])
                                        <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-ied-primary-dark">Destacado</span>
                                    @endif

                                    <h3 class="public-heading mt-2 text-lg font-semibold text-ied-gray-900">
                                        <a href="{{ $item['detail_url'] }}" class="transition hover:text-ied-primary-dark">{{ $item['title'] }}</a>
                                    </h3>

                                    @if (! empty($item['summary']))
                                        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
                                    @endif

                                    @if (! empty($item['period']))
                                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ied-primary-dark">{{ $item['period'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        {{ $projects->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
