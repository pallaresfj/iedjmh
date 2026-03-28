@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="institucion" :replace-header-with-banner="true">
        <x-slot:sidebar>
            <x-public.institucion.sidebar :pages="$institutionPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('institucion.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Landing Institucion
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

            @if ($pageKey === 'simbolos')
                <section class="space-y-6 border-t border-ied-gray-200 pt-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <article class="public-surface public-symbols-card p-5 sm:p-6">
                            <div class="public-symbols-flag-art" aria-hidden="true">
                                @foreach ($symbols['flag_stripes'] as $stripe)
                                    <div class="public-symbols-flag-art__stripe" style="background-color: {{ $stripe['color_hex'] }};"></div>
                                @endforeach
                            </div>

                            <div class="mt-5 space-y-4 sm:mt-6">
                                <h2 class="public-symbols-card__title">
                                    <span class="material-symbols-outlined" aria-hidden="true">flag</span>
                                    La Bandera
                                </h2>
                                <p class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">{{ $symbols['flag_intro'] }}</p>

                                <ul class="space-y-3" aria-label="Significado de los colores de la bandera">
                                    @foreach ($symbols['flag_stripes'] as $stripe)
                                        <li class="public-symbols-legend-item">
                                            <span class="public-symbols-legend-item__swatch" style="background-color: {{ $stripe['color_hex'] }};"></span>
                                            <div>
                                                <p class="text-sm font-semibold text-ied-gray-900">{{ $stripe['name'] }}</p>
                                                <p class="text-xs leading-relaxed text-ied-gray-700 sm:text-sm">{{ $stripe['description'] }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>

                        <article class="public-surface public-symbols-card p-5 sm:p-6">
                            <div class="public-symbols-shield-art">
                                @if (! empty($symbols['shield_image_url']))
                                    <img
                                        src="{{ $symbols['shield_image_url'] }}"
                                        alt="Escudo institucional"
                                        class="public-symbols-shield-art__image"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="public-symbols-shield-art__placeholder" role="status" aria-live="polite">
                                        Escudo no cargado
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 space-y-4 sm:mt-6">
                                <h2 class="public-symbols-card__title">
                                    <span class="material-symbols-outlined" aria-hidden="true">shield</span>
                                    El Escudo
                                </h2>
                                <p class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">{{ $symbols['shield_intro'] }}</p>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($symbols['shield_items'] as $item)
                                        <article class="public-symbols-shield-item">
                                            <div class="public-symbols-shield-item__heading">
                                                <span class="material-symbols-outlined" aria-hidden="true">{{ $item['icon'] }}</span>
                                                <p class="text-sm font-semibold text-ied-primary-dark">{{ $item['title'] }}</p>
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-ied-gray-700">{{ $item['description'] }}</p>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    </div>

                    <section class="public-symbols-hymn" aria-labelledby="symbols-hymn-title">
                        <div class="public-symbols-hymn__header">
                            <span class="material-symbols-outlined" aria-hidden="true">music_note</span>
                            <h2 id="symbols-hymn-title" class="public-symbols-hymn__title">{{ $symbols['hymn_title'] }}</h2>
                        </div>

                        <div class="public-symbols-audio">
                            @if (! empty($symbols['hymn_audio_url']))
                                <audio controls preload="none" class="public-symbols-audio__player" data-symbols-audio-player>
                                    <source src="{{ $symbols['hymn_audio_url'] }}">
                                    Tu navegador no soporta la reproduccion de audio.
                                </audio>
                            @else
                                <div class="public-symbols-audio__empty" role="status" aria-live="polite">
                                    No hay un archivo de audio del himno cargado. Puedes agregarlo desde el panel administrativo.
                                </div>
                            @endif
                        </div>

                        <article class="public-symbols-lyrics public-symbols-lyrics--single">
                            <h3 class="sr-only">Letra del himno institucional</h3>
                            @php($hymnStanzas = preg_split('/\R{2,}/u', trim((string) ($symbols['hymn_lyrics'] ?? ''))) ?: [])
                            <div class="public-symbols-lyrics__content text-sm text-ied-gray-700 sm:text-base">
                                @forelse ($hymnStanzas as $stanza)
                                    @if (trim($stanza) !== '')
                                        <p class="public-symbols-lyrics__stanza">{!! nl2br(e(trim($stanza))) !!}</p>
                                    @endif
                                @empty
                                    <p class="public-symbols-lyrics__stanza">Letra del himno no disponible.</p>
                                @endforelse
                            </div>
                        </article>
                    </section>
                </section>
            @endif

            @if ($pageKey === 'sedes')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Nuestras sedes</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($campuses as $campus)
                            <x-public.institucion.campus-card :campus="$campus" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'equipo-directivo')
                <section class="space-y-5 border-t border-ied-gray-200 pt-6">
                    <section class="public-surface p-5 sm:p-6">
                        <form action="{{ route('institucion.equipo-directivo') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" data-auto-filter-form data-auto-filter-target="#staff-results">
                            <label class="xl:col-span-2">
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Buscar</span>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $staffFilters['q'] }}"
                                    placeholder="Buscar por nombre o cargo..."
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                >
                            </label>

                            <label>
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Sede</span>
                                <select
                                    name="campus"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                >
                                    <option value="">Todas las Sedes</option>
                                    @foreach ($staffCampuses as $campus)
                                        <option value="{{ $campus['slug'] }}" @selected($staffFilters['campus'] === $campus['slug'])>{{ $campus['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                                <noscript>
                                    <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                        Aplicar filtros
                                    </button>
                                </noscript>
                                <a href="{{ route('institucion.equipo-directivo') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </section>

                    <section id="staff-results" class="space-y-4">
                        @if ($hasStaffActiveFilters)
                            <div class="rounded-xl border border-ied-gray-200 bg-white p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Filtros activos</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($staffFilters['q'] !== '')
                                        <span class="inline-flex items-center rounded-full bg-ied-primary/10 px-3 py-1 text-xs font-semibold text-ied-primary-dark">
                                            Busqueda: {{ $staffFilters['q'] }}
                                        </span>
                                    @endif
                                    @if ($staffFilters['campus'] !== '')
                                        <span class="inline-flex items-center rounded-full bg-ied-primary/10 px-3 py-1 text-xs font-semibold text-ied-primary-dark">
                                            Sede:
                                            {{ data_get(collect($staffCampuses)->firstWhere('slug', $staffFilters['campus']), 'name', $staffFilters['campus']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($directiveStaff->isEmpty())
                            <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                                No se encontraron integrantes del equipo directivo con los filtros aplicados.
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($directiveStaff as $member)
                                    <x-public.institucion.staff-card :member="$member" />
                                @endforeach
                            </div>
                        @endif
                    </section>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
