@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page
        :title="$title"
        :lead="$lead"
        :banner="$banner"
        section-key="academico"
        :replace-header-with-banner="true"
        :force-banner-title-style="true"
    >
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            @if ($pageKey === 'calendario-academico')
                <x-public.filter-panel :action="route('academico.calendario-academico')" target="#academic-calendar-results">
                    <label>
                        <span class="public-filter-label">Buscar</span>
                        <input type="text" name="q" value="{{ $calendarFilters['q'] }}" placeholder="Evento, descripcion o lugar" class="public-filter-input">
                    </label>

                    <label>
                        <span class="public-filter-label">Mes</span>
                        <select name="month" class="public-filter-input">
                            <option value="">Todos los meses</option>
                            @foreach ($calendarMonths as $month)
                                <option value="{{ $month['value'] }}" @selected($calendarFilters['month'] === $month['value'])>{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                </x-public.filter-panel>
            @endif

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
            @if ($pageKey !== 'planes-area')
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
            @endif

            @if ($pageKey === 'sistema-evaluacion')
                <section class="grid gap-4 border-t border-ied-gray-200 pt-6 md:grid-cols-2">
                    {{-- Tarjeta Documento SIEE --}}
                    <article class="flex flex-col rounded-2xl border border-ied-gray-200 bg-white p-6">
                        <span class="inline-flex size-12 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">docs</span>
                        </span>

                        <h3 class="mt-4 text-lg font-bold text-ied-gray-900">Sistema Institucional de Evaluacion de los Estudiantes (SIEE)</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ied-gray-600">
                            Documento oficial que define los criterios, procedimientos y estrategias de valoracion del aprendizaje adoptados por la institucion educativa.
                        </p>

                        @if ($sieeDocumentUrl)
                            <a href="{{ $sieeDocumentUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
                                Ver documento
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <p class="mt-5 text-xs font-bold uppercase tracking-wider text-ied-gray-400">Documento no disponible</p>
                        @endif
                    </article>

                    {{-- Tarjeta Plataforma Academica --}}
                    <article class="flex flex-col rounded-2xl border border-ied-gray-200 bg-white p-6">
                        <span class="inline-flex size-12 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">attach_file</span>
                        </span>

                        <h3 class="mt-4 text-lg font-bold text-ied-gray-900">Plataforma Academica{{ $sieePlatformName ? ' - ' . $sieePlatformName : '' }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ied-gray-600">
                            Plataforma digital para la gestion de notas, seguimiento academico y consulta de resultados por parte de docentes, estudiantes y familias.
                        </p>

                        @if ($sieePlatformUrl)
                            <a href="{{ $sieePlatformUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
                                Acceder
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <p class="mt-5 text-xs font-bold uppercase tracking-wider text-ied-gray-400">Plataforma no disponible</p>
                        @endif
                    </article>
                </section>
            @endif

            @if ($pageKey === 'planes-area')
                <section class="space-y-5">
                    <form
                        action="{{ route('academico.planes-area') }}"
                        method="GET"
                        data-auto-filter-form
                        data-auto-filter-target="#area-plans-results"
                    >
                        <div id="area-plans-results">
                            @if ($plans->isEmpty())
                                <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                                    No hay planes de area publicados en este momento.
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($plans as $item)
                                        <x-public.academico.area-plan-item :item="$item" />
                                    @endforeach
                                </div>

                                @if ($plans instanceof \Illuminate\Contracts\Pagination\Paginator && $plans->hasPages())
                                    <div class="pt-2">
                                        {{ $plans->links('vendor.pagination.public') }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </form>
                </section>
            @endif

            @if ($pageKey === 'zona-academica')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    @if ($academicZone['platforms']->isNotEmpty())
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Plataformas institucionales</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($academicZone['platforms'] as $platform)
                                <a
                                    href="{{ $platform['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-3 rounded-xl border border-ied-gray-200 bg-white p-4 transition hover:border-ied-primary hover:shadow-sm"
                                    data-academic-zone-platform
                                >
                                    <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">{{ $platform['icon'] }}</span>
                                    <span class="text-sm font-semibold text-ied-gray-900">{{ $platform['label'] }}</span>
                                    <svg class="ml-auto size-4 text-ied-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($academicZone['documents']->isNotEmpty())
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900 {{ $academicZone['platforms']->isNotEmpty() ? 'mt-6' : '' }}">Recursos descargables</h2>
                        <div class="divide-y divide-ied-gray-200 rounded-xl border border-ied-gray-200 bg-white">
                            @foreach ($academicZone['documents'] as $doc)
                                <div class="flex items-start gap-3 p-4" data-academic-zone-document>
                                    <span class="material-symbols-outlined mt-0.5 text-lg text-ied-primary" aria-hidden="true">description</span>
                                    <div class="min-w-0 flex-1">
                                        @if ($doc['url'])
                                            <a href="{{ $doc['url'] }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-ied-gray-900 hover:text-ied-primary">
                                                {{ $doc['title'] }}
                                            </a>
                                        @else
                                            <p class="text-sm font-semibold text-ied-gray-900">{{ $doc['title'] }}</p>
                                        @endif
                                        @if ($doc['summary'])
                                            <p class="mt-0.5 text-xs text-ied-gray-600">{{ $doc['summary'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($academicZone['platforms']->isEmpty() && $academicZone['documents']->isEmpty())
                        <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                            Proximamente se publicaran recursos y plataformas academicas en esta seccion.
                        </div>
                    @endif
                </section>
            @endif

            @if ($pageKey === 'calendario-academico')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
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
