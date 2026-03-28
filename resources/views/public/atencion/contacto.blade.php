@extends('layouts.public.app')

@section('title', $title)

@section('content')
    @php($locationLatitude = is_numeric($contact['latitude'] ?? null) ? number_format((float) $contact['latitude'], 6, '.', '') : null)
    @php($locationLongitude = is_numeric($contact['longitude'] ?? null) ? number_format((float) $contact['longitude'], 6, '.', '') : null)
    @php($hasLocationCoordinates = filled($locationLatitude) && filled($locationLongitude))

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

            <section class="grid gap-4 sm:grid-cols-2">
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Dirección</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">{{ $contact['address'] ?: 'No disponible' }}</p>
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Teléfono</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">{{ $contact['phone'] ?: 'No disponible' }}</p>
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Correo</h2>
                    @if ($contact['email'])
                        <a href="mailto:{{ $contact['email'] }}" class="mt-2 inline-block text-sm text-ied-primary-dark hover:text-ied-primary">{{ $contact['email'] }}</a>
                    @else
                        <p class="mt-2 text-sm text-ied-gray-700">No disponible</p>
                    @endif
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Ubicación</h2>
                    <div class="mt-2 inline-flex items-center gap-2">
                        @if ($hasLocationCoordinates)
                            <button
                                type="button"
                                class="public-icon-button public-icon-button--surface"
                                data-location-map-open
                                data-location-latitude="{{ $locationLatitude }}"
                                data-location-longitude="{{ $locationLongitude }}"
                                aria-label="Ver ubicación en el mapa"
                                title="Ver mapa"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">map</span>
                                <span class="sr-only">Ver mapa</span>
                            </button>
                        @endif
                        <p class="text-sm text-ied-gray-700">{{ $contact['location'] ?: 'No disponible' }}</p>
                    </div>
                </article>
            </section>

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Radica tu solicitud</h2>
                <p class="mt-2 text-sm text-ied-gray-700">
                    Para peticiones formales y trazabilidad institucional, utiliza el canal PQRS con radicado.
                </p>
                <a href="{{ route('atencion.pqrs') }}" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                    Ir a PQRS
                </a>
            </section>
        </div>
    </x-public.internal-page>
@endsection
