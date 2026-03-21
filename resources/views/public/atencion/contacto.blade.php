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

            <section class="grid gap-4 sm:grid-cols-2">
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Direccion</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">{{ $contact['address'] ?: 'No disponible' }}</p>
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Telefono</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">{{ $contact['phone'] ?: 'No disponible' }}</p>
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Correo</h2>
                    @if ($contact['email'])
                        <a href="mailto:{{ $contact['email'] }}" class="mt-2 inline-block text-sm text-ied-primary-dark hover:text-ied-primary">{{ $contact['email'] }}</a>
                    @else
                        <p class="mt-2 text-sm text-ied-gray-700">No disponible</p>
                    @endif
                </article>
                <article class="public-surface p-5">
                    <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Ubicacion</h2>
                    <p class="mt-2 text-sm text-ied-gray-700">{{ $contact['city'] }}, {{ $contact['department'] }}</p>
                </article>
            </section>

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-lg font-semibold text-ied-gray-900">Radica tu solicitud</h2>
                <p class="mt-2 text-sm text-ied-gray-700">
                    Para peticiones formales y trazabilidad institucional, utiliza el canal PQRS con radicado.
                </p>
                <a href="{{ route('atencion.pqrs') }}" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                    Ir a PQRS
                </a>
            </section>
        </div>
    </x-public.internal-page>
@endsection
