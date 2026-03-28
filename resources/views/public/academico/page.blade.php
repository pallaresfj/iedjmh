@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page
        :title="$title"
        :lead="$lead"
        :banner="$banner"
        section-key="academico"
        :replace-header-with-banner="true"
        :force-banner-title-style="in_array($pageKey, ['niveles-educativos', 'modalidad-agropecuaria', 'planes-area', 'sistema-evaluacion', 'proyectos-pedagogicos', 'calendario-academico'], true)"
    >
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('academico.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Landing Academico
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            @foreach ($blocks as $block)
                <section class="space-y-3">
                    @if (! empty($block['title']))
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">{{ $block['title'] }}</h2>
                    @endif
                    @if (! empty($block['is_html']))
                        <div class="public-rich-content text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                            {!! $block['body'] !!}
                        </div>
                    @else
                        <div class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                            {!! nl2br(e($block['body'])) !!}
                        </div>
                    @endif
                </section>
            @endforeach

            @if ($pageKey === 'planes-area')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Planes disponibles</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($plans as $item)
                            <x-public.academico.document-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'proyectos-pedagogicos')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Proyectos pedagogicos</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($projects as $item)
                            <x-public.academico.project-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'calendario-academico')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <section class="public-surface p-5 sm:p-6">
                        <form action="{{ route('academico.calendario-academico') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3" data-auto-filter-form data-auto-filter-target="#academic-calendar-results">
                            <label class="xl:col-span-2">
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Cajon de busqueda</span>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $calendarFilters['q'] }}"
                                    placeholder="Buscar por evento, descripcion o lugar"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                >
                            </label>

                            <label>
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Filtro por fecha</span>
                                <select
                                    name="month"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                >
                                    <option value="">Todos los meses</option>
                                    @foreach ($calendarMonths as $month)
                                        <option value="{{ $month['value'] }}" @selected($calendarFilters['month'] === $month['value'])>{{ $month['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-3">
                                <noscript>
                                    <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                        Aplicar filtros
                                    </button>
                                </noscript>
                                <a href="{{ route('academico.calendario-academico') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </section>

                    <div id="academic-calendar-results">
                        @if ($calendarEvents->isEmpty())
                            <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                                No se encontraron eventos con los filtros aplicados.
                            </div>
                        @else
                            <div class="divide-y divide-ied-gray-200">
                                @foreach ($calendarEvents as $item)
                                    <x-public.academico.calendar-item :item="$item" />
                                @endforeach
                            </div>

                            @if ($calendarEvents instanceof \Illuminate\Contracts\Pagination\Paginator && $calendarEvents->hasPages())
                                <div class="pt-2">
                                    {{ $calendarEvents->links('vendor.pagination.public') }}
                                </div>
                            @endif
                        @endif
                    </div>
                </section>
            @endif

        </div>
    </x-public.internal-page>
@endsection
