@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Canales</p>
                <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                    <li><a href="{{ route('atencion.contactenos') }}" class="text-ied-primary-dark hover:text-ied-primary">Contáctenos</a></li>
                    <li><a href="{{ route('atencion.tramites') }}" class="text-ied-primary-dark hover:text-ied-primary">Tramites</a></li>
                </ul>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                <p>
                    <strong>¿Ya radicaste una solicitud?</strong>
                    <a href="{{ route('atencion.pqrs.track') }}" class="font-semibold text-ied-primary-dark underline hover:text-ied-primary">Consulta el estado aqui</a>
                </p>
            </section>

            @if (session('pqrs_success'))
                <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('pqrs_success') }}
                    <a href="{{ route('atencion.pqrs.track') }}" class="mt-2 inline-block font-semibold text-emerald-700 underline hover:text-emerald-900">Consultar estado de mi solicitud</a>
                </section>
            @endif

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Radicacion PQRS</h2>
                <p class="mt-2 text-sm text-ied-gray-700">
                    Completa este formulario para registrar tu solicitud. Recibiras un codigo de seguimiento.
                </p>

                <form action="{{ route('atencion.pqrs.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf

                    <div class="hidden" aria-hidden="true">
                        <label for="website">No diligenciar este campo</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <label>
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

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Asunto</span>
                        <input
                            type="text"
                            name="subject"
                            value="{{ old('subject') }}"
                            required
                            maxlength="255"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('subject')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Adjunto</span>
                        <input
                            type="file"
                            name="attachment"
                            accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-ied-primary/10 file:px-3 file:py-1.5 file:font-semibold file:text-ied-primary-dark focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        <span class="mt-1 block text-xs text-ied-gray-500">PDF o DOCX, maximo 2 MB.</span>
                        @error('attachment')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Nombre completo</span>
                        <input
                            type="text"
                            name="applicant_name"
                            value="{{ old('applicant_name') }}"
                            required
                            maxlength="255"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Correo electronico</span>
                        <input
                            type="email"
                            name="applicant_email"
                            value="{{ old('applicant_email') }}"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Telefono</span>
                        <input
                            type="text"
                            name="applicant_phone"
                            value="{{ old('applicant_phone') }}"
                            maxlength="80"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Documento</span>
                        <input
                            type="text"
                            name="applicant_document"
                            value="{{ old('applicant_document') }}"
                            maxlength="120"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_document')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Municipio</span>
                        <input
                            type="text"
                            name="municipality"
                            value="{{ old('municipality') }}"
                            maxlength="120"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('municipality')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="md:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Direccion</span>
                        <input
                            type="text"
                            name="applicant_address"
                            value="{{ old('applicant_address') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_address')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="md:col-span-2">
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

                    <label class="md:col-span-2 inline-flex items-start gap-2 text-sm text-ied-gray-700">
                        <input type="checkbox" name="consent_habeas_data" value="1" @checked(old('consent_habeas_data')) class="mt-1 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary/30">
                        <span>
                            Autorizo el tratamiento de datos personales conforme a la normativa vigente.
                            @error('consent_habeas_data')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </span>
                    </label>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            Enviar solicitud
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </x-public.internal-page>
@endsection
