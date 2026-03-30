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

            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">inbox</span>
                        </span>

                        <div class="min-w-0 space-y-0.5">
                            <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">PQRS y consulta ciudadana</h2>
                            <p class="text-sm leading-relaxed text-ied-gray-600">Canal formal para peticiones y observaciones de la comunidad.</p>
                        </div>
                    </div>

                    <a href="{{ route('atencion.pqrs') }}" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
                        Radicar solicitud
                        <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </article>

                <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">visibility</span>
                        </span>

                        <div class="min-w-0 space-y-0.5">
                            <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">Rendicion y transparencia</h2>
                            <p class="text-sm leading-relaxed text-ied-gray-600">Consulta documentos oficiales y publicaciones de gestion institucional.</p>
                        </div>
                    </div>

                    <a href="{{ route('transparencia.documentos') }}" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
                        Ver documentos
                        <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </article>
            </section>

            <section class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                        <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">forum</span>
                    </span>

                    <div class="min-w-0 space-y-0.5">
                        <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">Canales de participacion</h2>
                        <p class="text-sm leading-relaxed text-ied-gray-600">Mecanismos disponibles para la participacion ciudadana.</p>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-sm text-ied-gray-700">
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">chevron_right</span>
                        Mesas de dialogo con comunidad educativa.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">chevron_right</span>
                        Atencion a observaciones ciudadanas por PQRS.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">chevron_right</span>
                        Consulta de informacion publica institucional.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">chevron_right</span>
                        Canales de contacto para solicitudes formales.
                    </li>
                </ul>
            </section>
        </div>
    </x-public.internal-page>
@endsection
