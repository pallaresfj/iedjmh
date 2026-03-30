@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <section class="matricula-section matricula-section--requirements">
        <div class="public-container">
            <div class="mx-auto max-w-5xl">
                <header class="matricula-header">
                    <h1 class="matricula-title">Requisitos de Matricula</h1>
                    <p class="matricula-subtitle">Documentacion requerida para el ingreso</p>
                </header>

                <div class="matricula-requirements-grid">
                    @foreach ($requirements as $requirement)
                        <article @class([
                            'matricula-requirement-card',
                            'matricula-requirement-card--emphasized' => $requirement['emphasized'],
                        ])>
                            <span class="matricula-requirement-card__icon">
                                <span class="material-symbols-outlined" aria-hidden="true">{{ $requirement['icon'] }}</span>
                            </span>
                            <h2 class="matricula-requirement-card__title">{{ $requirement['title'] }}</h2>
                            <p class="matricula-requirement-card__description">{{ $requirement['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="matricula-section matricula-section--form">
        <div class="public-container">
            <div class="mx-auto max-w-4xl">
                <header class="matricula-header">
                    <h2 class="matricula-title">Formulario de Inscripcion</h2>
                    <p class="matricula-subtitle">Completa el formulario digital para iniciar su proceso. Un asesor se pondra en contacto para agendar la entrevista presencial.</p>
                </header>

                @if (session('matricula_success'))
                    <section class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        {{ session('matricula_success') }}
                    </section>
                @endif

                @if (! $hasCampuses)
                    <section class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Actualmente no hay sedes disponibles para recibir solicitudes. Por favor intente mas tarde.
                    </section>
                @endif

                <article class="matricula-form-card">
                    <form action="{{ route('matricula.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" data-matricula-form>
                        @csrf

                        <div class="grid grid-cols-12 gap-4">
                            <label class="matricula-field col-span-12 md:col-span-8">
                                <span class="matricula-field__label">Nombre completo del estudiante</span>
                                <input
                                    type="text"
                                    name="student_name"
                                    value="{{ old('student_name') }}"
                                    maxlength="255"
                                    required
                                    @disabled(! $hasCampuses)
                                    placeholder="Ej. Juan Perez"
                                    class="matricula-field__control"
                                />
                                @error('student_name')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            </label>

                            <label class="matricula-field col-span-12 md:col-span-4">
                                <span class="matricula-field__label">Documento de identidad</span>
                                <input
                                    type="text"
                                    name="document_number"
                                    value="{{ old('document_number') }}"
                                    maxlength="120"
                                    required
                                    @disabled(! $hasCampuses)
                                    placeholder="Numero de documento"
                                    class="matricula-field__control"
                                />
                                @error('document_number')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <label class="matricula-field">
                                <span class="matricula-field__label">Grado al que aspira</span>
                                <select name="grade" required @disabled(! $hasCampuses) class="matricula-field__control">
                                    <option value="">Seleccione un grado</option>
                                    @foreach ($gradeOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('grade') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('grade')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            </label>

                            <label class="matricula-field">
                                <span class="matricula-field__label">Sede de interes</span>
                                <select name="campus_id" required @disabled(! $hasCampuses) class="matricula-field__control">
                                    <option value="">Seleccione una sede</option>
                                    @foreach ($campuses as $campus)
                                        <option value="{{ $campus['id'] }}" @selected((string) old('campus_id') === (string) $campus['id'])>{{ $campus['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('campus_id')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            </label>

                            <label class="matricula-field">
                                <span class="matricula-field__label">Telefono de contacto</span>
                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    maxlength="80"
                                    required
                                    @disabled(! $hasCampuses)
                                    placeholder="Ej. 3001234567"
                                    class="matricula-field__control"
                                />
                                @error('phone')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <label class="matricula-field">
                            <span class="matricula-field__label">Carga de documentos (opcional)</span>

                            <div
                                class="matricula-dropzone"
                                data-matricula-dropzone
                                role="button"
                                tabindex="0"
                                aria-controls="matricula-attachments-input"
                                aria-describedby="matricula-attachments-help"
                            >
                                <input
                                    id="matricula-attachments-input"
                                    type="file"
                                    name="attachments[]"
                                    multiple
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @disabled(! $hasCampuses)
                                    class="sr-only"
                                    data-matricula-file-input
                                />
                                <span class="material-symbols-outlined matricula-dropzone__icon" aria-hidden="true">cloud_upload</span>
                                <p class="matricula-dropzone__lead">Arrastra tus archivos aqui o selecciona desde tu equipo</p>
                                <p id="matricula-attachments-help" class="matricula-dropzone__hint">Hasta 5 archivos (PDF/JPG/JPEG/PNG/WEBP), maximo 1MB por archivo.</p>
                                <p class="matricula-dropzone__selected hidden" data-matricula-file-selected aria-live="polite"></p>
                            </div>

                            <span class="matricula-field__error hidden" data-matricula-file-error aria-live="polite"></span>
                            @error('attachments')<span class="matricula-field__error">{{ $message }}</span>@enderror
                            @foreach ($errors->get('attachments.*') as $messages)
                                @foreach ($messages as $message)
                                    <span class="matricula-field__error">{{ $message }}</span>
                                @endforeach
                            @endforeach
                        </label>

                        <button
                            type="submit"
                            @disabled(! $hasCampuses)
                            class="matricula-submit-btn"
                        >
                            Enviar Solicitud de Matricula
                        </button>

                        <p class="matricula-form-footnote">
                            Al enviar este formulario, usted acepta nuestra Politica de Tratamiento de Datos Personales conforme a la Ley 1581 de 2012.
                        </p>
                    </form>
                </article>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-matricula-form]');

            if (!form) {
                return;
            }

            const dropzone = form.querySelector('[data-matricula-dropzone]');
            const fileInput = form.querySelector('[data-matricula-file-input]');
            const fileError = form.querySelector('[data-matricula-file-error]');
            const fileSelected = form.querySelector('[data-matricula-file-selected]');
            const maxFiles = 5;
            const maxFileSize = 1024 * 1024;
            const allowedMimeTypes = new Set(['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/png', 'image/webp']);
            const allowedExtensions = new Set(['pdf', 'jpg', 'jpeg', 'png', 'webp']);
            let isPickerOpening = false;

            if (!dropzone || !fileInput || !fileError || !fileSelected) {
                return;
            }

            const setError = (message) => {
                fileError.textContent = message;
                fileError.classList.remove('hidden');
                dropzone.classList.add('has-error');
            };

            const clearError = () => {
                fileError.textContent = '';
                fileError.classList.add('hidden');
                dropzone.classList.remove('has-error');
            };

            const updateSelected = (files) => {
                if (!files || files.length === 0) {
                    fileSelected.textContent = '';
                    fileSelected.classList.add('hidden');
                    dropzone.classList.remove('has-file');
                    return;
                }

                const names = Array.from(files).map((file) => file.name).join(', ');
                fileSelected.textContent = names;
                fileSelected.classList.remove('hidden');
                dropzone.classList.add('has-file');
            };

            const validateFiles = (files) => {
                if (files.length > maxFiles) {
                    setError('Puedes adjuntar maximo 5 archivos.');
                    return false;
                }

                const hasOversize = Array.from(files).some((file) => file.size > maxFileSize);
                if (hasOversize) {
                    setError('Cada archivo debe pesar maximo 1MB.');
                    return false;
                }

                const hasInvalidType = Array.from(files).some((file) => {
                    const name = String(file.name || '').toLowerCase();
                    const extension = name.includes('.') ? name.split('.').pop() : '';
                    const mime = String(file.type || '').toLowerCase();

                    const validByMime = mime !== '' && allowedMimeTypes.has(mime);
                    const validByExtension = extension !== '' && allowedExtensions.has(extension);

                    return !validByMime && !validByExtension;
                });

                if (hasInvalidType) {
                    setError('Solo se permiten archivos PDF, JPG, JPEG, PNG o WEBP.');
                    return false;
                }

                clearError();
                return true;
            };

            const assignFiles = (files) => {
                const dataTransfer = new DataTransfer();
                Array.from(files).forEach((file) => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                updateSelected(fileInput.files);
            };

            const openPicker = (event = null) => {
                if (fileInput.disabled || isPickerOpening) {
                    return;
                }

                if (event?.target === fileInput) {
                    return;
                }

                if (event?.target instanceof Element && event.target.closest('input[type="file"]')) {
                    return;
                }

                isPickerOpening = true;
                fileInput.click();
                window.setTimeout(() => {
                    isPickerOpening = false;
                }, 0);
            };

            fileInput.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            fileInput.addEventListener('change', (event) => {
                event.stopPropagation();

                if (!validateFiles(fileInput.files)) {
                    fileInput.value = '';
                    updateSelected(null);
                    return;
                }

                updateSelected(fileInput.files);
            });

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });
            });

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', (event) => {
                const files = event.dataTransfer?.files;

                if (!files || files.length === 0) {
                    return;
                }

                if (!validateFiles(files)) {
                    return;
                }

                assignFiles(files);
            });

            dropzone.addEventListener('click', (event) => {
                event.preventDefault();
                openPicker(event);
            });

            dropzone.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();

                openPicker();
            });
        });
    </script>
@endpush
