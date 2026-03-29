@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="transparencia" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" active-section="contratacion" />

            <x-public.filter-panel :action="route('transparencia.contratacion.index')" target="#contracts-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="ID proceso, objeto o contratista" class="public-filter-input">
                </label>

                <label>
                    <span class="public-filter-label">Vigencia</span>
                    <select name="fiscal_year" class="public-filter-input">
                        <option value="">Todas</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($filters['fiscal_year'] === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="public-filter-label">Estado</span>
                    <select name="process_status" class="public-filter-input">
                        <option value="">Todos</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['process_status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="public-filter-label">Tipo de contrato</span>
                    <select name="type" class="public-filter-input">
                        <option value="">Todos</option>
                        @foreach ($types as $type)
                            <option value="{{ $type['slug'] }}" @selected($filters['type'] === $type['slug'])>{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </label>
            </x-public.filter-panel>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if ($manualDocument)
                <section class="public-surface p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">Manual de Contratacion</p>
                    <h2 class="public-heading mt-2 text-xl font-semibold text-ied-gray-900">{{ $manualDocument['title'] }}</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ $manualDocument['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                            Abrir manual
                        </a>
                        <a href="{{ $manualDocument['detail_url'] }}" class="inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                            Ver ficha del documento
                        </a>
                    </div>
                </section>
            @endif

            <section id="contracts-results" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ied-gray-700">
                        {{ $contractsTotal }} resultado(s)
                    </p>
                    @if ($hasActiveFilters)
                        <a href="{{ route('transparencia.contratacion.index') }}" class="inline-flex items-center rounded-full border border-ied-gray-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                            Limpiar todos los filtros
                        </a>
                    @endif
                </div>

                @if ($hasActiveFilters)
                    <div class="rounded-xl border border-ied-gray-200 bg-white p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Filtros activos</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($activeFilters as $activeFilter)
                                <span class="inline-flex items-center gap-1 rounded-full bg-ied-primary/10 px-3 py-1 text-xs font-semibold text-ied-primary-dark">
                                    <span>{{ $activeFilter['label'] }}:</span>
                                    <span>{{ $activeFilter['value'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($contracts->count() === 0)
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        @if ($hasActiveFilters)
                            No se encontraron procesos de contratacion con los filtros actuales. Limpia los filtros para ver todos los procesos publicados.
                        @else
                            No se encontraron procesos de contratacion publicados.
                        @endif
                    </div>
                @else
                    <div class="hidden overflow-x-auto rounded-2xl border border-ied-gray-200 bg-white md:block">
                        <table class="min-w-full divide-y divide-ied-gray-200 text-sm">
                            <thead class="bg-ied-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-ied-gray-700">
                                <tr>
                                    <th class="px-4 py-3">ID Proceso</th>
                                    <th class="px-4 py-3">Objeto</th>
                                    <th class="px-4 py-3">Cuantia</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Documentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ied-gray-200 bg-white">
                                @foreach ($contracts as $contract)
                                    @php($amountLabel = $contract['official_budget'] !== null ? '$'.number_format($contract['official_budget'], 0, ',', '.') : 'No definido')
                                    <tr class="align-top text-ied-gray-700">
                                        <td class="px-4 py-3">
                                            <a href="{{ $contract['detail_url'] }}" class="font-semibold text-ied-primary-dark hover:text-ied-primary">
                                                {{ $contract['process_code'] }}
                                            </a>
                                            <p class="mt-1 text-xs text-ied-gray-600">Vigencia {{ $contract['fiscal_year'] }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-ied-gray-900">{{ $contract['object'] }}</p>
                                            @if (! empty($contract['type']))
                                                <p class="mt-1 text-xs text-ied-gray-600">{{ $contract['type'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-ied-gray-900">{{ $amountLabel }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 text-xs font-semibold text-ied-primary-dark">
                                                {{ $contract['process_status_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-xs text-ied-gray-700">{{ $contract['documents_count'] }} documento(s)</p>
                                            <a href="{{ $contract['detail_url'] }}" class="mt-1 inline-flex items-center text-xs font-semibold text-ied-primary-dark hover:text-ied-primary">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-3 md:hidden">
                        @foreach ($contracts as $contract)
                            @php($amountLabel = $contract['official_budget'] !== null ? '$'.number_format($contract['official_budget'], 0, ',', '.') : 'No definido')
                            <article class="public-surface p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">{{ $contract['process_code'] }}</p>
                                <h3 class="public-heading mt-1 text-base font-semibold text-ied-gray-900">{{ $contract['object'] }}</h3>
                                <p class="mt-2 text-sm text-ied-gray-700">{{ $amountLabel }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 font-semibold text-ied-primary-dark">{{ $contract['process_status_label'] }}</span>
                                    <span class="text-ied-gray-600">{{ $contract['documents_count'] }} documento(s)</span>
                                </div>
                                <a href="{{ $contract['detail_url'] }}" class="mt-3 inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                                    Ver proceso
                                </a>
                            </article>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        {{ $contracts->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>

            <section class="public-surface p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Directorio de contratistas adjudicados</h2>
                    <p class="text-xs text-ied-gray-600">Actualizado con procesos publicados en estado adjudicado</p>
                </div>

                @if ($contractors->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        Aun no hay contratistas adjudicados publicados para mostrar en el directorio.
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-ied-gray-200 text-sm">
                            <thead class="bg-ied-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-ied-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Contratista</th>
                                    <th class="px-4 py-3">NIT</th>
                                    <th class="px-4 py-3">Objeto social</th>
                                    <th class="px-4 py-3">Adjudicaciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ied-gray-200 bg-white text-ied-gray-700">
                                @foreach ($contractors as $contractor)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-ied-gray-900">{{ $contractor['name'] }}</td>
                                        <td class="px-4 py-3">{{ $contractor['nit'] }}</td>
                                        <td class="px-4 py-3">{{ $contractor['social_object'] }}</td>
                                        <td class="px-4 py-3">{{ $contractor['adjudications'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
