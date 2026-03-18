@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="academico">
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('academico.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Landing Academico
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            @foreach ($blocks as $block)
                <section class="space-y-3">
                    @if (! empty($block['title']))
                        <h2 class="public-heading text-xl font-semibold text-ied-gray-900">{{ $block['title'] }}</h2>
                    @endif
                    <div class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! nl2br(e($block['body'])) !!}
                    </div>
                </section>
            @endforeach

            @if ($pageKey === 'planes-area')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Planes disponibles</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($plans as $item)
                            <x-public.academico.document-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'proyectos-pedagogicos')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Proyectos pedagogicos</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($projects as $item)
                            <x-public.academico.project-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'calendario-academico')
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Agenda academica</h2>
                    <div class="divide-y divide-ied-gray-200">
                        @foreach ($calendarEvents as $item)
                            <x-public.academico.calendar-item :item="$item" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($pageKey === 'zona-academica')
                <section class="border-t border-ied-gray-200 pt-6">
                    <div class="public-surface p-5 sm:p-6">
                        <p class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                            Accede al portal academico institucional para consulta de informacion estudiantil, seguimiento
                            de desempeno y servicios para familias y estudiantes.
                        </p>
                        <a href="{{ route('zona-academica.index') }}" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                            Ir a Zona Academica
                        </a>
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
