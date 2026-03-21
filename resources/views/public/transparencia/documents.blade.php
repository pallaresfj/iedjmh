@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="transparencia">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" :active-category="$filters['category']" />
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                <form action="{{ route('transparencia.documentos') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5" data-auto-filter-form data-auto-filter-target="#documents-results">
                    <label class="xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Buscar</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Titulo, descripcion o numero"
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
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Ano</span>
                        <select
                            name="year"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                            <option value="">Todos</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected($filters['year'] === $year)>{{ $year }}</option>
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

                    <div class="flex items-end gap-2 md:col-span-2 xl:col-span-5">
                        <noscript>
                            <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                Aplicar filtros
                            </button>
                        </noscript>
                        <a href="{{ route('transparencia.documentos') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                            Limpiar
                        </a>
                    </div>
                </form>
            </section>

            <section id="documents-results" class="space-y-4">
                @if ($documents->count() === 0)
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No se encontraron documentos con los filtros aplicados.
                    </div>
                @else
                    @foreach ($documents as $document)
                        <x-public.transparencia.document-card :document="$document" />
                    @endforeach

                    <div class="pt-2">
                        {{ $documents->links() }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
