@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="academico" :replace-header-with-banner="true">
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Accesos</p>
                <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                    <li>
                        <a href="{{ route('academico.calendario-academico') }}" class="text-ied-primary-dark hover:text-ied-primary">
                            Calendario Academico
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('institucion.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                            Institucion
                        </a>
                    </li>
                </ul>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <p class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                Esta seccion integra informacion curricular y recursos de consulta academica. El contenido editorial
                se administra desde CMS y los listados se actualizan con modulos dinamicos.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($cards as $card)
                    <x-public.academico.page-card
                        :title="$card['title']"
                        :summary="$card['summary']"
                        :route="$card['route']"
                    />
                @endforeach
            </div>
        </div>
    </x-public.internal-page>
@endsection
