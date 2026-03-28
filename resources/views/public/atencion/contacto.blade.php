@extends('layouts.public.app')

@section('title', $title)

@section('content')
    @php($locationLatitude = is_numeric($contact['latitude'] ?? null) ? number_format((float) $contact['latitude'], 6, '.', '') : null)
    @php($locationLongitude = is_numeric($contact['longitude'] ?? null) ? number_format((float) $contact['longitude'], 6, '.', '') : null)
    @php($hasLocationCoordinates = filled($locationLatitude) && filled($locationLongitude))
    @php($mapLabel = collect([$contact['location'] ?? null, $contact['address'] ?? null])->filter()->implode(', '))
    @php($googleMapsUrl = $hasLocationCoordinates ? 'https://www.google.com/maps?q='.$locationLatitude.','.$locationLongitude : (filled($mapLabel) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapLabel) : null))

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

            @if (session('pqrs_success'))
                <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                    {{ session('pqrs_success') }}
                </section>
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,22rem)_minmax(0,1fr)]">
                <div class="space-y-6">
                    <article class="public-surface p-5 sm:p-6">
                        <h2 class="public-heading text-2xl font-bold tracking-[-0.01em] text-ied-gray-900">Informacion institucional</h2>

                        <ul class="mt-5 space-y-5">
                            <li class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800" aria-hidden="true">
                                    <span class="material-symbols-outlined text-[20px]">location_on</span>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ied-gray-600">Direccion fisica</p>
                                    <p class="mt-1 text-lg font-semibold leading-tight text-ied-gray-900">{{ $contact['address'] ?: 'No disponible' }}</p>
                                    <p class="mt-1 text-sm text-ied-gray-700">{{ $contact['location'] ?: 'No disponible' }}</p>
                                </div>
                            </li>

                            <li class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-800" aria-hidden="true">
                                    <span class="material-symbols-outlined text-[20px]">call</span>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ied-gray-600">Linea telefonica</p>
                                    <p class="mt-1 text-lg font-semibold leading-tight text-ied-gray-900">{{ $contact['phone'] ?: 'No disponible' }}</p>
                                </div>
                            </li>

                            <li class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800" aria-hidden="true">
                                    <span class="material-symbols-outlined text-[20px]">mail</span>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ied-gray-600">Correo electronico</p>
                                    @if ($contact['email'])
                                        <a href="mailto:{{ $contact['email'] }}" class="mt-1 inline-block text-lg font-semibold leading-tight text-ied-gray-900 underline-offset-2 hover:text-ied-primary-dark hover:underline">
                                            {{ $contact['email'] }}
                                        </a>
                                    @else
                                        <p class="mt-1 text-lg font-semibold leading-tight text-ied-gray-900">No disponible</p>
                                    @endif
                                </div>
                            </li>

                            <li class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ied-primary-dark text-white" aria-hidden="true">
                                    <svg
                                        data-contact-clock-icon
                                        class="h-5 w-5 text-white"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                        focusable="false"
                                    >
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" />
                                        <path d="M12 7.5V12L15 13.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ied-gray-600">Horario de atencion</p>
                                    <p class="mt-1 text-lg font-semibold leading-tight text-ied-gray-900">{{ $contact['hours'] ?: 'No disponible' }}</p>
                                </div>
                            </li>
                        </ul>
                    </article>

                    <article class="public-surface p-5 sm:p-6">
                        <div class="rounded-xl border border-ied-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-ied-gray-600">Ubicanos en el mapa</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @if ($hasLocationCoordinates)
                                    <button
                                        type="button"
                                        class="public-icon-button public-icon-button--surface"
                                        data-location-map-open
                                        data-location-latitude="{{ $locationLatitude }}"
                                        data-location-longitude="{{ $locationLongitude }}"
                                        aria-label="Ver ubicacion en el mapa"
                                        title="Ver mapa"
                                    >
                                        <span class="material-symbols-outlined" aria-hidden="true">map</span>
                                        <span class="sr-only">Ver mapa institucional</span>
                                    </button>
                                    <p class="text-sm text-ied-gray-700">Abrir mapa institucional</p>
                                @elseif($googleMapsUrl)
                                    <p class="text-sm text-ied-gray-700">La ubicacion solo esta disponible en Google Maps desde el popup del mapa.</p>
                                @else
                                    <p class="text-sm text-ied-gray-600">Ubicacion no disponible.</p>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>

                <article class="public-surface p-5 sm:p-6 lg:p-7">
                    <h2 class="public-heading text-3xl font-bold tracking-[-0.02em] text-ied-gray-900">Envianos un mensaje</h2>
                    <p class="mt-2 text-sm text-ied-gray-700 sm:text-base">
                        Completa el formulario y radicaremos tu solicitud para darle respuesta institucional.
                    </p>

                    <form action="{{ route('atencion.pqrs.store') }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        <div class="hidden" aria-hidden="true">
                            <label for="website">No diligenciar este campo</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
                        </div>

                        <input type="hidden" name="type" value="peticion" />
                        <input type="hidden" name="origin" value="contact" />

                        <label>
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Nombre completo</span>
                            <input
                                type="text"
                                name="applicant_name"
                                value="{{ old('applicant_name') }}"
                                required
                                maxlength="255"
                                autocomplete="name"
                                placeholder="Ej. Juan Perez"
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            />
                            @error('applicant_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label>
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Correo electronico</span>
                                <input
                                    type="email"
                                    name="applicant_email"
                                    value="{{ old('applicant_email') }}"
                                    maxlength="255"
                                    autocomplete="email"
                                    placeholder="nombre@ejemplo.com"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                />
                                @error('applicant_email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>

                            <label>
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Telefono</span>
                                <input
                                    type="text"
                                    name="applicant_phone"
                                    value="{{ old('applicant_phone') }}"
                                    maxlength="80"
                                    autocomplete="tel"
                                    placeholder="3001234567"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                />
                                @error('applicant_phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <label>
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Asunto del mensaje</span>
                            <select
                                name="subject"
                                required
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                                @foreach ($contactSubjectOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('subject', 'Informacion general') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('subject')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label>
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Tu mensaje</span>
                            <textarea
                                name="message"
                                rows="7"
                                required
                                minlength="20"
                                maxlength="5000"
                                placeholder="Escribe aqui tu consulta detalladamente..."
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >{{ old('message') }}</textarea>
                            @error('message')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="inline-flex items-start gap-2 text-sm text-ied-gray-700">
                            <input
                                type="checkbox"
                                name="consent_habeas_data"
                                value="1"
                                required
                                @checked(old('consent_habeas_data'))
                                class="mt-0.5 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary/30"
                            />
                            <span>
                                Acepto el tratamiento de mis datos personales segun la politica de privacidad institucional.
                                @error('consent_habeas_data')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </span>
                        </label>

                        <div class="pt-2">
                            <button
                                type="submit"
                                data-contact-submit
                                class="flex w-full min-h-12 items-center justify-center gap-2 rounded-xl bg-ied-primary-dark px-5 py-3.5 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-ied-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ied-primary-dark"
                            >
                                Enviar mensaje
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">send</span>
                            </button>
                        </div>
                    </form>
                </article>
            </section>
        </div>
    </x-public.internal-page>
@endsection
