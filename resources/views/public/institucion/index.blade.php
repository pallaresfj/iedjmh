@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="institucion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.institucion.sidebar :pages="$institutionPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Enlaces clave</p>
                <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                    <li>
                        <a href="{{ route('transparencia.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                            Transparencia
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('atencion.index') }}" class="text-ied-primary-dark hover:text-ied-primary">
                            Atención al Ciudadano
                        </a>
                    </li>
                </ul>
            </div>
        </x-slot:sidebar>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($cards as $card)
                <x-public.institucion.page-card
                    :title="$card['title']"
                    :summary="$card['summary']"
                    :route="$card['route']"
                    :icon="$card['icon']"
                />
            @endforeach
        </div>
    </x-public.internal-page>
@endsection
