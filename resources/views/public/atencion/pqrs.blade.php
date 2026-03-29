@extends('layouts.public.app')

@section('title', $title)

@section('content')
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
                <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('pqrs_success') }}
                    <a href="{{ route('atencion.pqrs.track') }}" class="mt-2 inline-block font-semibold text-emerald-700 underline hover:text-emerald-900">Consultar estado de mi solicitud</a>
                </section>
            @endif

            <section class="public-surface p-5 sm:p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <section class="public-surface public-pqrs-sidebar-process p-5 sm:p-6">
                        <h3 class="public-pqrs-sidebar-process__title">
                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                            Proceso de Radicacion
                        </h3>
                        <ol class="public-pqrs-sidebar-process__steps" aria-label="Pasos para radicar una solicitud PQRS">
                            <li class="public-pqrs-sidebar-process__step">
                                <span class="public-pqrs-sidebar-process__badge">1</span>
                                <p>Seleccione el tipo de solicitud (Peticion, Queja, Reclamo, Sugerencia o Felicitacion).</p>
                            </li>
                            <li class="public-pqrs-sidebar-process__step">
                                <span class="public-pqrs-sidebar-process__badge">2</span>
                                <p>Diligencie sus datos o elija la opcion de radicado anonimo.</p>
                            </li>
                            <li class="public-pqrs-sidebar-process__step">
                                <span class="public-pqrs-sidebar-process__badge">3</span>
                                <p>Describa los hechos de manera clara y adjunte evidencias si es necesario.</p>
                            </li>
                            <li class="public-pqrs-sidebar-process__step">
                                <span class="public-pqrs-sidebar-process__badge">4</span>
                                <p>Conserve su codigo de radicado para seguimiento futuro.</p>
                            </li>
                        </ol>
                    </section>

                    <section class="public-surface public-pqrs-sidebar-track p-5 sm:p-6">
                        <h3 class="public-pqrs-sidebar-track__title">
                            <span class="material-symbols-outlined" aria-hidden="true">target</span>
                            Consultar Estado
                        </h3>
                        <p class="public-pqrs-sidebar-track__lead">
                            Ingrese el codigo asignado al momento de su radicacion para conocer el avance de su solicitud.
                        </p>

                        <form action="{{ route('atencion.pqrs.status') }}" method="POST" class="public-pqrs-sidebar-track__form">
                            @csrf
                            <label class="sr-only" for="inline-tracking-code">Codigo de radicado</label>
                            <input
                                id="inline-tracking-code"
                                type="text"
                                name="tracking_code"
                                value="{{ old('tracking_code') }}"
                                required
                                maxlength="50"
                                placeholder="Ej: PQRS-2026-0001"
                                class="public-pqrs-sidebar-track__input"
                            >

                            <label class="sr-only" for="inline-applicant-email">Correo electronico</label>
                            <input
                                id="inline-applicant-email"
                                type="email"
                                name="applicant_email"
                                value="{{ old('applicant_email') }}"
                                required
                                maxlength="255"
                                placeholder="Correo registrado en la solicitud"
                                class="public-pqrs-sidebar-track__input"
                            >

                            <button type="submit" class="public-pqrs-sidebar-track__button">
                                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                                Consultar Estado
                            </button>
                        </form>
                    </section>
                </div>

                <h2 class="public-heading mt-6 text-lg font-semibold text-ied-gray-900">Radicacion PQRS</h2>
                <p class="mt-2 text-sm text-ied-gray-700">
                    Completa este formulario para registrar tu solicitud. Recibiras un codigo de seguimiento.
                </p>

                <form action="{{ route('atencion.pqrs.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-12" data-pqrs-form>
                    @csrf

                    <div class="hidden" aria-hidden="true">
                        <label for="website">No diligenciar este campo</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <input type="hidden" name="is_anonymous" value="0">

                    {{-- Fila 1: Tipo, Correo, Modalidad --}}
                    <label class="md:col-span-4">
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

                    <label class="md:col-span-6">
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

                    <label class="md:col-span-2">
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

                    {{-- Fila 2: Nombre y documento --}}
                    <div class="md:col-span-12 grid gap-4 md:grid-cols-2" data-anonymous-identity>
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
                    <div class="md:col-span-12 grid gap-4 md:grid-cols-2" data-anonymous-contact>
                        <label>
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

                        <label>
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
                    <label class="md:col-span-12">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Mensaje</span>
                        <textarea
                            name="message"
                            rows="6"
                            required
                            maxlength="5000"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >{{ old('message') }}</textarea>
                        @error('message')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    {{-- Fila 5: Adjunto --}}
                    <label class="md:col-span-12">
                        <span class="public-pqrs-upload__title">Carga de archivos / Evidencias</span>

                        <div
                            class="public-pqrs-upload"
                            data-pqrs-dropzone
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
                                data-pqrs-file-input
                            >

                            <span class="material-symbols-outlined public-pqrs-upload__icon" aria-hidden="true">cloud_upload</span>
                            <p class="public-pqrs-upload__lead">Haga clic para subir o arrastre sus archivos aquí</p>
                            <p id="pqrs-attachment-help" class="public-pqrs-upload__hint">Soporta PDF, DOCX (Máx. 2MB)</p>
                            <p class="public-pqrs-upload__selected hidden" data-pqrs-file-selected aria-live="polite"></p>
                        </div>

                        <span id="pqrs-attachment-client-error" class="public-pqrs-upload__error hidden" data-pqrs-file-error aria-live="polite"></span>
                        @error('attachment')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    {{-- Fila 6: Tratamiento de datos --}}
                    <label class="md:col-span-12 inline-flex items-start gap-2 text-sm text-ied-gray-700">
                        <input type="checkbox" name="consent_habeas_data" value="1" @checked(old('consent_habeas_data')) class="mt-1 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary/30">
                        <span>
                            Autorizo el tratamiento de datos personales conforme a la normativa vigente.
                            @error('consent_habeas_data')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </span>
                    </label>

                    <div class="md:col-span-12">
                        <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            Enviar solicitud
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
            const dropzone = form.querySelector('[data-pqrs-dropzone]');
            const fileInput = form.querySelector('[data-pqrs-file-input]');
            const fileError = form.querySelector('[data-pqrs-file-error]');
            const fileSelected = form.querySelector('[data-pqrs-file-selected]');

            const initAttachmentDropzone = () => {
                if (!dropzone || !fileInput || !fileError || !fileSelected) {
                    return;
                }

                const maxSizeBytes = 2 * 1024 * 1024;
                const allowedExtensions = new Set(['pdf', 'docx']);
                let dragDepth = 0;

                const bytesToMb = (bytes) => (bytes / (1024 * 1024)).toFixed(2);

                const resetFileSelection = () => {
                    fileInput.value = '';
                    fileSelected.textContent = '';
                    fileSelected.classList.add('hidden');
                    dropzone.classList.remove('has-file');
                };

                const showFileError = (message) => {
                    fileError.textContent = message;
                    fileError.classList.remove('hidden');
                    dropzone.classList.add('has-error');
                };

                const clearFileError = () => {
                    fileError.textContent = '';
                    fileError.classList.add('hidden');
                    dropzone.classList.remove('has-error');
                };

                const validateFile = (file) => {
                    const extension = file.name.includes('.')
                        ? file.name.split('.').pop().toLowerCase()
                        : '';

                    if (!allowedExtensions.has(extension)) {
                        showFileError('Formato no permitido. Solo se aceptan archivos PDF o DOCX.');

                        return false;
                    }

                    if (file.size > maxSizeBytes) {
                        showFileError('El archivo supera el tamaño máximo permitido (2MB).');

                        return false;
                    }

                    return true;
                };

                const syncSelectedFile = (file) => {
                    fileSelected.textContent = `Archivo seleccionado: ${file.name} (${bytesToMb(file.size)} MB)`;
                    fileSelected.classList.remove('hidden');
                    dropzone.classList.add('has-file');
                };

                const assignFileToInput = (file) => {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    fileInput.files = transfer.files;
                };

                const processFile = (file) => {
                    clearFileError();

                    if (!validateFile(file)) {
                        resetFileSelection();

                        return;
                    }

                    assignFileToInput(file);
                    syncSelectedFile(file);
                };

                const preventDefaults = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                };

                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
                    dropzone.addEventListener(eventName, preventDefaults);
                });

                dropzone.addEventListener('dragenter', () => {
                    dragDepth += 1;
                    dropzone.classList.add('is-dragover');
                });

                dropzone.addEventListener('dragover', () => {
                    dropzone.classList.add('is-dragover');
                });

                dropzone.addEventListener('dragleave', () => {
                    dragDepth = Math.max(0, dragDepth - 1);

                    if (dragDepth === 0) {
                        dropzone.classList.remove('is-dragover');
                    }
                });

                dropzone.addEventListener('drop', (event) => {
                    dragDepth = 0;
                    dropzone.classList.remove('is-dragover');
                    const droppedFiles = Array.from(event.dataTransfer?.files ?? []);

                    if (droppedFiles.length !== 1) {
                        showFileError('Solo se permite adjuntar un archivo.');
                        resetFileSelection();

                        return;
                    }

                    processFile(droppedFiles[0]);
                });

                dropzone.addEventListener('click', () => {
                    fileInput.click();
                });

                dropzone.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        fileInput.click();
                    }
                });

                dropzone.addEventListener('focus', () => {
                    dropzone.classList.add('is-focused');
                });

                dropzone.addEventListener('blur', () => {
                    dropzone.classList.remove('is-focused');
                });

                fileInput.addEventListener('change', () => {
                    clearFileError();
                    const selectedFile = fileInput.files?.[0];

                    if (!selectedFile) {
                        resetFileSelection();

                        return;
                    }

                    if (!validateFile(selectedFile)) {
                        resetFileSelection();

                        return;
                    }

                    syncSelectedFile(selectedFile);
                });
            };

            initAttachmentDropzone();

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
