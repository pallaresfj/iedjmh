@props([
    'latitude' => null,
    'longitude' => null,
    'locationLabel' => null,
])

@php($lat = is_numeric($latitude) ? number_format((float) $latitude, 6, '.', '') : null)
@php($lng = is_numeric($longitude) ? number_format((float) $longitude, 6, '.', '') : null)
@php($hasCoordinates = filled($lat) && filled($lng))
@php($mapQuery = $hasCoordinates ? "{$lat},{$lng}" : null)
@php($externalUrl = $hasCoordinates ? "https://www.google.com/maps?q={$mapQuery}" : null)
@php($embedUrl = $hasCoordinates ? "https://maps.google.com/maps?hl=es&q={$mapQuery}&z=17&output=embed" : null)
@php($label = filled($locationLabel) ? "Ubicacion: {$locationLabel}" : 'Ubicacion institucional')

@if ($hasCoordinates)
    <div
        class="fixed inset-0 z-[80] grid place-items-center bg-ied-gray-900/75 p-4 backdrop-blur-sm sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-hidden="true"
        aria-labelledby="public-location-map-title"
        data-location-map-modal
        data-location-map-default-latitude="{{ $lat }}"
        data-location-map-default-longitude="{{ $lng }}"
        data-location-map-default-embed-url="{{ $embedUrl }}"
        data-location-map-default-external-url="{{ $externalUrl }}"
        hidden
    >
        <div class="w-full max-w-4xl rounded-2xl border border-ied-gray-200 bg-white shadow-2xl" data-location-map-panel>
            <div class="flex items-center justify-between border-b border-ied-gray-200 px-4 py-3 sm:px-5">
                <h2 id="public-location-map-title" class="public-heading text-base font-semibold text-ied-gray-900 sm:text-lg">
                    {{ $label }}
                </h2>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full border border-ied-gray-200 p-2 text-ied-gray-700 transition hover:bg-ied-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                    aria-label="Cerrar mapa"
                    data-location-map-close
                >
                    <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">close</span>
                </button>
            </div>
            <div class="px-4 pb-4 pt-3 sm:px-5 sm:pb-5">
                <iframe
                    class="h-[22rem] w-full rounded-xl border border-ied-gray-200 bg-ied-gray-100 sm:h-[28rem]"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa de ubicacion institucional"
                    data-location-map-iframe
                ></iframe>
                <div class="mt-3 flex justify-end">
                    <a
                        href="{{ $externalUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-full border border-ied-primary/35 px-4 py-2 text-sm font-semibold text-ied-primary-dark transition hover:bg-ied-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                        data-location-map-external
                    >
                        <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">open_in_new</span>
                        Abrir en Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
