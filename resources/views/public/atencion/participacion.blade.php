@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="grid gap-4 md:grid-cols-2">
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">PQRS y consulta ciudadana</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">
                        Canal formal para peticiones y observaciones de la comunidad.
                    </p>
                    <a href="{{ route('atencion.pqrs') }}" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                        Radicar solicitud
                    </a>
                </article>

                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Rendicion y transparencia</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">
                        Consulta documentos oficiales y publicaciones de gestion institucional.
                    </p>
                    <a href="{{ route('transparencia.documentos') }}" class="mt-4 inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                        Ver documentos
                    </a>
                </article>
            </section>

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Canales de participacion</h2>
                <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                    <li>Mesas de dialogo con comunidad educativa.</li>
                    <li>Atencion a observaciones ciudadanas por PQRS.</li>
                    <li>Consulta de informacion publica institucional.</li>
                    <li>Canales de contacto para solicitudes formales.</li>
                </ul>
            </section>
        </div>
    </x-public.internal-page>
@endsection
