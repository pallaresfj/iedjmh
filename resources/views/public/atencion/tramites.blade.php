@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="public-surface p-5 sm:p-6">
                <form action="{{ route('atencion.tramites') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" data-auto-filter-form data-auto-filter-target="#procedures-results">
                    <label class="xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Buscar</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Nombre, resumen o requisitos del tramite"
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
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Canal</span>
                        <select
                            name="online"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                            <option value="">Todos</option>
                            <option value="1" @selected($filters['online'] === '1')>En linea</option>
                            <option value="0" @selected($filters['online'] === '0')>Presencial u otros</option>
                        </select>
                    </label>

                    <div class="md:col-span-2 xl:col-span-4 flex items-end gap-2">
                        <noscript>
                            <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                Aplicar filtros
                            </button>
                        </noscript>
                        <a href="{{ route('atencion.tramites') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                            Limpiar
                        </a>
                    </div>
                </form>
            </section>

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
