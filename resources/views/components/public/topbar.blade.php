@props([
    'homeThemeable' => false,
])

@php($contact = \App\Support\PublicSettings::contact())
@php($email = $contact['email'])
@php($phone = $contact['phone'])
@php($govLabel = config('institution.govbar.label', 'GOV.CO'))
@php($location = $contact['location'])
@php($locationLatitude = is_numeric($contact['latitude'] ?? null) ? number_format((float) $contact['latitude'], 6, '.', '') : null)
@php($locationLongitude = is_numeric($contact['longitude'] ?? null) ? number_format((float) $contact['longitude'], 6, '.', '') : null)
@php($hasLocationCoordinates = filled($locationLatitude) && filled($locationLongitude))

<div @class([
    'public-topbar',
    'public-topbar--home' => $homeThemeable,
])>
    <div class="public-container py-1.5">
        <div class="public-shell flex flex-col gap-2 text-[11px] sm:flex-row sm:items-center sm:justify-between">
            <p class="inline-flex items-center gap-2 font-semibold uppercase tracking-[0.18em] text-white/95">
                <span class="material-symbols-outlined text-sm !text-[14px]" aria-hidden="true">flag</span>
                {{ $govLabel }}
            </p>

            <div class="flex flex-wrap items-center gap-2 text-white/85 sm:justify-end">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    @if ($hasLocationCoordinates)
                        <button
                            type="button"
                            class="public-icon-button public-icon-button--topbar"
                            data-location-map-open
                            data-location-latitude="{{ $locationLatitude }}"
                            data-location-longitude="{{ $locationLongitude }}"
                            aria-label="{{ filled($location) ? "Ver ubicacion de {$location} en el mapa" : 'Ver ubicacion en el mapa' }}"
                            title="Ver ubicacion en el mapa"
                        >
                            <span class="material-symbols-outlined" aria-hidden="true">map</span>
                            <span class="sr-only">Ver ubicacion en el mapa</span>
                        </button>
                    @elseif (filled($location))
                        <span>{{ $location }}</span>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="transition hover:text-white focus-visible:text-white">
                            {{ $email }}
                        </a>
                    @endif
                    @if ($phone)
                        <span>{{ $phone }}</span>
                    @endif
                </div>
                <button
                    type="button"
                    class="public-icon-button public-icon-button--topbar public-home-theme-toggle--icon"
                    data-public-theme-toggle
                    aria-label="Cambiar tema del sitio"
                    aria-pressed="false"
                >
                    <span class="material-symbols-outlined" data-public-theme-toggle-icon aria-hidden="true">dark_mode</span>
                </button>
            </div>
        </div>
    </div>
</div>
