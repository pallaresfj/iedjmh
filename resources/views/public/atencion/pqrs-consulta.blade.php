@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Consulta el estado de tu solicitud</h2>
                <p class="mt-2 text-sm text-ied-gray-700">
                    Ingresa tu codigo de seguimiento y correo electronico para verificar el estado de tu PQRS.
                </p>

                <form action="{{ route('atencion.pqrs.status') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Codigo de seguimiento</span>
                        <input
                            type="text"
                            name="tracking_code"
                            value="{{ $trackingCode ?? old('tracking_code') }}"
                            required
                            maxlength="50"
                            placeholder="Ej: PQRS-2026-0001"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('tracking_code')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Correo electronico</span>
                        <input
                            type="email"
                            name="applicant_email"
                            value="{{ $applicantEmail ?? old('applicant_email') }}"
                            required
                            maxlength="255"
                            placeholder="tu@correo.com"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                        @error('applicant_email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            Consultar estado
                        </button>
                    </div>
                </form>
            </section>

            @isset($trackingCode)
                @if ($pqrs)
                    <section class="public-surface p-5 sm:p-6">
                        <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Estado de tu solicitud</h2>

                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-semibold text-ied-gray-700">Codigo</dt>
                                <dd class="text-ied-gray-900">{{ $pqrs->tracking_code }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ied-gray-700">Tipo</dt>
                                <dd class="text-ied-gray-900">
                                    @php
                                        $typeLabels = [
                                            'peticion' => 'Peticion',
                                            'queja' => 'Queja',
                                            'reclamo' => 'Reclamo',
                                            'sugerencia' => 'Sugerencia',
                                            'felicitacion' => 'Felicitacion',
                                            'tramite' => 'Tramite',
                                        ];
                                    @endphp
                                    {{ $typeLabels[$pqrs->type] ?? ucfirst($pqrs->type) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ied-gray-700">Estado</dt>
                                <dd>
                                    @php
                                        $statusPillClass = match ($pqrs->status) {
                                            'received' => 'public-status-pill--received',
                                            'in_progress' => 'public-status-pill--in-progress',
                                            'resolved' => 'public-status-pill--resolved',
                                            default => 'public-status-pill--closed',
                                        };
                                    @endphp
                                    <span class="public-status-pill {{ $statusPillClass }}">
                                        @switch($pqrs->status)
                                            @case('received') Recibido @break
                                            @case('in_progress') En tramite @break
                                            @case('resolved') Resuelto @break
                                            @case('closed') Cerrado @break
                                            @default {{ ucfirst($pqrs->status) }}
                                        @endswitch
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ied-gray-700">Modalidad</dt>
                                <dd class="text-ied-gray-900">{{ $pqrs->is_anonymous ? 'Anonima' : 'Identificada' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="font-semibold text-ied-gray-700">Resumen del mensaje</dt>
                                <dd class="text-ied-gray-900">{{ \Illuminate\Support\Str::limit($pqrs->message, 180) }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ied-gray-700">Fecha de radicacion</dt>
                                <dd class="text-ied-gray-900">{{ $pqrs->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                            </div>
                            @if ($pqrs->resolved_at)
                                <div>
                                    <dt class="font-semibold text-ied-gray-700">Fecha de resolucion</dt>
                                    <dd class="text-ied-gray-900">{{ $pqrs->resolved_at->translatedFormat('d M Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if ($messages->isNotEmpty())
                        <section class="public-surface p-5 sm:p-6">
                            <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Historial de mensajes</h2>
                            <div class="mt-4 space-y-4">
                                @foreach ($messages as $msg)
                                    <div class="rounded-lg border border-ied-gray-100 bg-ied-gray-50 p-4">
                                        <div class="flex items-center justify-between text-xs text-ied-gray-500">
                                            <span class="font-semibold text-ied-gray-700">{{ $msg['author'] }}</span>
                                            <time>{{ $msg['date'] }}</time>
                                        </div>
                                        @if (! empty($msg['subject']))
                                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">{{ $msg['subject'] }}</p>
                                        @endif

                                        @if (($msg['is_rich'] ?? false) === true)
                                            <div class="prose prose-sm mt-2 max-w-none text-ied-gray-800">{!! $msg['message'] !!}</div>
                                        @else
                                            <p class="mt-2 text-sm text-ied-gray-800">{{ $msg['message'] }}</p>
                                        @endif

                                        @if (! empty($msg['reference_url']))
                                            <div class="mt-3">
                                                <a href="{{ $msg['reference_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full border border-ied-primary/25 px-3 py-1 text-xs font-semibold text-ied-primary-dark hover:border-ied-primary hover:text-ied-primary">
                                                    Ver Respuesta
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @else
                    <section class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        No se encontro una solicitud con el codigo <strong>{{ $trackingCode }}</strong> y el correo proporcionado. Verifica los datos e intenta de nuevo.
                    </section>
                @endif
            @endisset
        </div>
    </x-public.internal-page>
@endsection
