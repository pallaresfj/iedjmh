@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="transparencia" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" :active-category="$filters['category']" />

            <x-public.filter-panel :action="route('transparencia.documentos')" target="#documents-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Titulo, descripcion o numero" class="public-filter-input">
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
                    <span class="public-filter-label">Ano</span>
                    <select name="year" class="public-filter-input">
                        <option value="">Todos</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($filters['year'] === $year)>{{ $year }}</option>
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
                        {{ $documents->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
