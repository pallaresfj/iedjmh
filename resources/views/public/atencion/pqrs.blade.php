@extends('layouts.public.app')

@section('title', $title)

@section('content')
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
                <section class="public-alert public-alert--success">
                    {{ session('pqrs_success') }}
                    <a href="{{ route('atencion.pqrs.track') }}" class="public-alert__link mt-2 inline-block">Consultar estado de mi solicitud</a>
                </section>
            @endif

            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">info</span>
                        </span>

                        <div class="min-w-0 space-y-0.5">
                            <h3 class="public-heading text-lg font-extrabold text-ied-gray-900">Proceso de Radicacion</h3>
                            <p class="text-sm leading-relaxed text-ied-gray-600">Siga estos pasos para radicar su solicitud.</p>
                        </div>
                    </div>

                    <ol class="mt-4 space-y-3" aria-label="Pasos para radicar una solicitud PQRS">
                        <li class="flex items-start gap-3 text-sm text-ied-gray-700">
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-ied-primary text-xs font-bold text-white">1</span>
                            <p class="pt-0.5">Seleccione el tipo de solicitud (Peticion, Queja, Reclamo, Sugerencia o Felicitacion).</p>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-ied-gray-700">
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-ied-primary text-xs font-bold text-white">2</span>
                            <p class="pt-0.5">Diligencie sus datos o elija la opcion de radicado anonimo.</p>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-ied-gray-700">
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-ied-primary text-xs font-bold text-white">3</span>
                            <p class="pt-0.5">Describa los hechos de manera clara y adjunte evidencias si es necesario.</p>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-ied-gray-700">
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-ied-primary text-xs font-bold text-white">4</span>
                            <p class="pt-0.5">Conserve su codigo de radicado para seguimiento futuro.</p>
                        </li>
                    </ol>
                </article>

                <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">target</span>
                        </span>

                        <div class="min-w-0 space-y-0.5">
                            <h3 class="public-heading text-lg font-extrabold text-ied-gray-900">Consultar Estado</h3>
                            <p class="text-sm leading-relaxed text-ied-gray-600">Ingrese el codigo asignado al momento de su radicacion para conocer el avance de su solicitud.</p>
                        </div>
                    </div>

                    <form action="{{ route('atencion.pqrs.status') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <label class="block">
                            <span class="sr-only">Codigo de radicado</span>
                            <input
                                id="inline-tracking-code"
                                type="text"
                                name="tracking_code"
                                value="{{ old('tracking_code') }}"
                                required
                                maxlength="50"
                                placeholder="Ej: PQRS-2026-0001"
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                        </label>

                        <label class="block">
                            <span class="sr-only">Correo electronico</span>
                            <input
                                id="inline-applicant-email"
                                type="email"
                                name="applicant_email"
                                value="{{ old('applicant_email') }}"
                                required
                                maxlength="255"
                                placeholder="Correo registrado en la solicitud"
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                        </label>

                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                            Consultar Estado
                        </button>
                    </form>
                </article>
            </section>

            <section class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                        <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">edit_note</span>
                    </span>

                    <div class="min-w-0 space-y-0.5">
                        <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">Radicacion PQRS</h2>
                        <p class="text-sm leading-relaxed text-ied-gray-600">Completa este formulario para registrar tu solicitud. Recibiras un codigo de seguimiento.</p>
                    </div>
                </div>

                <form action="{{ route('atencion.pqrs.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4" data-pqrs-form data-file-dropzone-root>
                    @csrf

                    <div class="hidden" aria-hidden="true">
                        <label for="website">No diligenciar este campo</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <input type="hidden" name="is_anonymous" value="0">

                    {{-- Fila 1: Tipo, Correo, Modalidad --}}
                    <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Tipo de solicitud</span>
                            <select
                                name="type"
                                required
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Correo electronico</span>
                            <input
                                type="email"
                                name="applicant_email"
                                value="{{ old('applicant_email') }}"
                                required
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                            @error('applicant_email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Modalidad</span>
                            <span class="mt-2 inline-flex items-center gap-3">
                                <span class="public-ios-switch">
                                    <input
                                        type="checkbox"
                                        name="is_anonymous"
                                        value="1"
                                        role="switch"
                                        @checked(old('is_anonymous'))
                                        class="public-ios-switch__input"
                                        data-anonymous-toggle
                                    >
                                    <span class="public-ios-switch__track" aria-hidden="true"></span>
                                </span>
                                <span class="text-sm text-ied-gray-800">Anonimo</span>
                            </span>
                            @error('is_anonymous')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    {{-- Fila 2: Nombre y documento --}}
                    <div class="grid gap-4 md:grid-cols-2" data-anonymous-identity>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Nombre completo</span>
                            <input
                                type="text"
                                name="applicant_name"
                                value="{{ old('applicant_name') }}"
                                required
                                maxlength="255"
                                data-anonymous-name
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                            @error('applicant_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Documento</span>
                            <input
                                type="text"
                                name="applicant_document"
                                value="{{ old('applicant_document') }}"
                                maxlength="120"
                                data-anonymous-document
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                            @error('applicant_document')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    {{-- Fila 3: Direccion y telefono --}}
                    <div class="grid gap-4 md:grid-cols-2" data-anonymous-contact>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Direccion</span>
                            <input
                                type="text"
                                name="applicant_address"
                                value="{{ old('applicant_address') }}"
                                maxlength="255"
                                data-anonymous-address
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                            @error('applicant_address')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Telefono</span>
                            <input
                                type="text"
                                name="applicant_phone"
                                value="{{ old('applicant_phone') }}"
                                maxlength="80"
                                data-anonymous-phone
                                class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                            >
                            @error('applicant_phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    {{-- Fila 4: Mensaje --}}
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Mensaje</span>
                        <textarea
                            name="message"
                            rows="5"
                            required
                            maxlength="5000"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >{{ old('message') }}</textarea>
                        @error('message')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    {{-- Fila 5: Adjunto --}}
                    <label class="block">
                        <span class="public-pqrs-upload__title">Carga de archivos / Evidencias</span>

                        <div
                            class="public-pqrs-upload"
                            data-file-dropzone
                            data-file-extensions="pdf,docx"
                            data-file-max-bytes="2097152"
                            data-file-max-label="2MB"
                            data-file-formats-label="PDF, DOCX"
                            role="button"
                            tabindex="0"
                            aria-controls="pqrs-attachment-input"
                            aria-describedby="pqrs-attachment-help pqrs-attachment-client-error"
                        >
                            <input
                                id="pqrs-attachment-input"
                                type="file"
                                name="attachment"
                                accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                class="sr-only"
                                data-file-input
                            >

                            <span class="material-symbols-outlined public-pqrs-upload__icon" aria-hidden="true">cloud_upload</span>
                            <p class="public-pqrs-upload__lead">Haga clic para subir o arrastre sus archivos aquí</p>
                            <p id="pqrs-attachment-help" class="public-pqrs-upload__hint">Soporta PDF, DOCX (Máx. 2MB)</p>
                            <p class="public-pqrs-upload__selected hidden" data-file-selected aria-live="polite"></p>
                        </div>

                        <span id="pqrs-attachment-client-error" class="public-pqrs-upload__error hidden" data-file-error aria-live="polite"></span>
                        @error('attachment')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    {{-- Fila 6: Tratamiento de datos --}}
                    <label class="inline-flex items-start gap-2 text-sm text-ied-gray-700">
                        <input type="checkbox" name="consent_habeas_data" value="1" @checked(old('consent_habeas_data')) class="mt-1 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary/30">
                        <span>
                            Autorizo el tratamiento de datos personales conforme a la normativa vigente.
                            @error('consent_habeas_data')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </span>
                    </label>

                    <div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            Enviar solicitud
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">send</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </x-public.internal-page>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-pqrs-form]');

            if (!form) {
                return;
            }

            const anonymousToggle = form.querySelector('[data-anonymous-toggle]');
            const identityGroup = form.querySelector('[data-anonymous-identity]');
            const nameInput = form.querySelector('[data-anonymous-name]');
            const documentInput = form.querySelector('[data-anonymous-document]');
            const contactGroup = form.querySelector('[data-anonymous-contact]');
            const phoneInput = form.querySelector('[data-anonymous-phone]');
            const addressInput = form.querySelector('[data-anonymous-address]');

            if (!anonymousToggle || !identityGroup || !nameInput || !documentInput || !contactGroup || !phoneInput || !addressInput) {
                return;
            }

            const syncAnonymousState = () => {
                const isAnonymous = anonymousToggle.checked;

                identityGroup.classList.toggle('hidden', isAnonymous);
                contactGroup.classList.toggle('hidden', isAnonymous);
                nameInput.required = !isAnonymous;
                nameInput.disabled = isAnonymous;
                documentInput.disabled = isAnonymous;
                phoneInput.disabled = isAnonymous;
                addressInput.disabled = isAnonymous;

                if (isAnonymous) {
                    nameInput.value = '';
                    documentInput.value = '';
                    phoneInput.value = '';
                    addressInput.value = '';
                }
            };

            anonymousToggle.addEventListener('change', syncAnonymousState);
            syncAnonymousState();
        })();
    </script>
@endpush
