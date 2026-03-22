@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Canales rapidos</p>
                <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                    <li>
                        <a href="{{ route('atencion.pqrs') }}" class="text-ied-primary-dark hover:text-ied-primary">Radicar PQRS</a>
                    </li>
                    <li>
                        <a href="{{ route('atencion.tramites') }}" class="text-ied-primary-dark hover:text-ied-primary">Consultar tramites</a>
                    </li>
                </ul>
            </div>
        </x-slot:sidebar>

        <div class="space-y-8">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="grid gap-4 md:grid-cols-2">
                <a href="{{ route('atencion.contactenos') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Contactenos</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Canales de contacto institucional y horarios de atencion.</p>
                </a>
                <a href="{{ route('atencion.pqrs') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">PQRS</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Formulario para peticiones, quejas, reclamos y sugerencias con radicado.</p>
                </a>
                <a href="{{ route('atencion.tramites') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Tramites y servicios</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Consulta requisitos, canales y tiempos de respuesta.</p>
                </a>
                <a href="{{ route('atencion.faq') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Preguntas frecuentes</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Respuestas rapidas para orientacion ciudadana.</p>
                </a>
                <a href="{{ route('atencion.mapa-sitio') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Mapa del sitio</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Estructura completa de navegacion del portal.</p>
                </a>
                <a href="{{ route('atencion.participacion') }}" class="public-surface block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Participacion</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">Mecanismos para la participacion ciudadana.</p>
                </a>
            </section>

            @if ($procedureCategories->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Categorias de tramites</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($procedureCategories as $category)
                            <a href="{{ route('atencion.tramites', ['category' => $category['slug']]) }}" class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-3 py-1 text-xs font-medium text-ied-primary-dark">
                                {{ $category['name'] }} ({{ $category['count'] }})
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($featuredFaqs->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <div class="flex items-end justify-between gap-3">
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Preguntas destacadas</h2>
                        <a href="{{ route('atencion.faq') }}" class="text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">Ver todas</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($featuredFaqs as $item)
                            <x-public.atencion.faq-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
