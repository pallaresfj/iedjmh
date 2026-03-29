@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />

            <x-public.filter-panel :action="route('atencion.tramites')" target="#procedures-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nombre, resumen o requisitos" class="public-filter-input">
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
                    <span class="public-filter-label">Canal</span>
                    <select name="online" class="public-filter-input">
                        <option value="">Todos</option>
                        <option value="1" @selected($filters['online'] === '1')>En linea</option>
                        <option value="0" @selected($filters['online'] === '0')>Presencial u otros</option>
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

            <section id="procedures-results" class="space-y-4">
                @if ($items->count() === 0)
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No se encontraron tramites con los filtros aplicados.
                    </div>
                @else
                    @foreach ($items as $item)
                        <x-public.atencion.procedure-card :item="$item" />
                    @endforeach

                    <div class="pt-2">
                        {{ $items->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
