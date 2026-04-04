@extends('layouts.public.app')

@section('title', $title)

@section('content')
    @php($locationLatitude = is_numeric($contact['latitude'] ?? null) ? number_format((float) $contact['latitude'], 6, '.', '') : null)
    @php($locationLongitude = is_numeric($contact['longitude'] ?? null) ? number_format((float) $contact['longitude'], 6, '.', '') : null)
    @php($hasLocationCoordinates = filled($locationLatitude) && filled($locationLongitude))
    @php($mapLabel = collect([$contact['location'] ?? null, $contact['address'] ?? null])->filter()->implode(', '))
    @php($googleMapsUrl = $hasLocationCoordinates ? 'https://www.google.com/maps?q='.$locationLatitude.','.$locationLongitude : (filled($mapLabel) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapLabel) : null))

    <x-public.internal-page :title="$title" :lead="$lead" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
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
                <div class="space-y-4">
                    <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                                <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">apartment</span>
                            </span>

                            <div class="min-w-0 space-y-0.5">
                                <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">Informacion institucional</h2>
                                <p class="text-sm leading-relaxed text-ied-gray-600">Canales de atencion y horarios de servicio.</p>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined mt-0.5 text-base text-ied-gray-400" aria-hidden="true">location_on</span>
                                <div>
                                    <dt class="sr-only">Direccion</dt>
                                    <dd class="text-ied-gray-700">{{ $contact['address'] ?: 'No disponible' }}</dd>
                                    @if (! empty($contact['location']))
                                        <dd class="text-xs text-ied-gray-600">{{ $contact['location'] }}</dd>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">call</span>
                                <div>
                                    <dt class="sr-only">Telefono</dt>
                                    <dd class="text-ied-gray-700">{{ $contact['phone'] ?: 'No disponible' }}</dd>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">mail</span>
                                <div>
                                    <dt class="sr-only">Correo</dt>
                                    <dd>
                                        @if ($contact['email'])
                                            <a href="mailto:{{ $contact['email'] }}" class="text-ied-primary-dark transition hover:text-ied-primary">{{ $contact['email'] }}</a>
                                        @else
                                            <span class="text-ied-gray-700">No disponible</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">schedule</span>
                                <div>
                                    <dt class="sr-only">Horario</dt>
                                    <dd class="text-ied-gray-700">{{ $contact['hours'] ?: 'No disponible' }}</dd>
                                </div>
                            </div>
                        </dl>

                        @if ($hasLocationCoordinates)
                            <button
                                type="button"
                                class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary"
                                data-location-map-open
                                data-location-latitude="{{ $locationLatitude }}"
                                data-location-longitude="{{ $locationLongitude }}"
                            >
                                Ver ubicacion
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @elseif ($googleMapsUrl)
                            <a href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
                                Ver ubicacion
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                    </article>
                </div>

                <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">mail</span>
                        </span>

                        <div class="min-w-0 space-y-0.5">
                            <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">Envianos un mensaje</h2>
                            <p class="text-sm leading-relaxed text-ied-gray-600">Completa el formulario y radicaremos tu solicitud para darle respuesta institucional.</p>
                        </div>
                    </div>

                    <form action="{{ route('atencion.pqrs.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf

                        <div class="hidden" aria-hidden="true">
                            <label for="website">No diligenciar este campo</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
                        </div>

                        <input type="hidden" name="type" value="peticion" />
                        <input type="hidden" name="origin" value="contact" />
                        <input type="hidden" name="is_anonymous" value="0" />

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Nombre completo</span>
                            <input
                                type="text"
                                name="applicant_name"
                                value="{{ old('applicant_name') }}"
                                required
                                maxlength="255"
                                autocomplete="name"
                                placeholder="Ej. Juan Perez"
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            />
                            @error('applicant_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Correo electronico</span>
                                <input
                                    type="email"
                                    name="applicant_email"
                                    value="{{ old('applicant_email') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="email"
                                    placeholder="nombre@ejemplo.com"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                />
                                @error('applicant_email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>

                            <label class="block">
                                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Telefono</span>
                                <input
                                    type="text"
                                    name="applicant_phone"
                                    value="{{ old('applicant_phone') }}"
                                    maxlength="80"
                                    autocomplete="tel"
                                    placeholder="3001234567"
                                    class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                                />
                                @error('applicant_phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Tu mensaje</span>
                            <textarea
                                name="message"
                                rows="5"
                                required
                                minlength="20"
                                maxlength="5000"
                                placeholder="Escribe aqui tu consulta detalladamente..."
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
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
                                class="mt-1 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary/30"
                            />
                            <span>
                                Acepto el tratamiento de mis datos personales segun la politica de privacidad institucional.
                                @error('consent_habeas_data')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                            </span>
                        </label>

                        <div>
                            <button
                                type="submit"
                                data-contact-submit
                                class="inline-flex items-center gap-2 rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark"
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

    <section class="bg-ied-primary-dark py-16 sm:py-20">
        <div class="public-container">
            <div class="public-content-shell">
                <div class="flex flex-col items-start gap-8 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-2xl space-y-3">
                        <h2 class="public-heading text-3xl font-black text-white sm:text-4xl">¿Eres egresado de nuestra institución?</h2>
                        <p class="text-base leading-relaxed text-white/80 sm:text-lg">Queremos mantener el contacto contigo. Actualiza tus datos para recibir invitaciones a eventos y noticias institucionales.</p>
                    </div>

                    <a
                        href="{{ route('egresados.index') }}"
                        class="inline-flex shrink-0 items-center rounded-full bg-ied-primary-light px-8 py-4 text-sm font-bold uppercase tracking-widest text-ied-primary-dark transition hover:brightness-110"
                    >
                        Actualizar datos
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
