@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="academico" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            <x-public.filter-panel :action="route('academico.proyectos-pedagogicos')" target="#projects-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Titulo o descripcion del proyecto" class="public-filter-input">
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

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('academico.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Landing Académico
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

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
                    <div @class([
                        'grid gap-4',
                        'md:grid-cols-2' => $projects->count() >= 2,
                        'xl:grid-cols-3' => $projects->count() >= 3,
                    ])>
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
