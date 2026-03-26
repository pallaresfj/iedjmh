@extends('layouts.public.app')

@section('title', $event['title'])

@section('content')
    <x-public.internal-page :title="$event['title']" :lead="$event['summary']" :banner="$banner" section-key="academico">
        <x-slot:sidebar>
            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('home') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Inicio
                </a>
                <a href="{{ route('academico.calendario-academico') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Calendario academico
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Informacion del evento</h2>

                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Inicia</dt>
                        <dd>{{ $event['starts_at'] ?? 'No definida' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Finaliza</dt>
                        <dd>{{ $event['ends_at'] ?? 'No definida' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Jornada</dt>
                        <dd>{{ $event['is_all_day'] ? 'Todo el dia' : 'Horario especifico' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Lugar</dt>
                        <dd>{{ $event['location'] ?? 'No definido' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Publicado</dt>
                        <dd>{{ $event['published_at'] ?? 'No registrado' }}</dd>
                    </div>
                </dl>

                @if (collect($event['categories'])->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($event['categories'] as $category)
                            <li class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-1 text-xs font-medium text-ied-primary-dark">
                                {{ $category['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($event['description']))
                    <div class="mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! nl2br(e($event['description'])) !!}
                    </div>
                @endif

                @if (! empty($event['registration_url']))
                    <a href="{{ $event['registration_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                        Inscripcion o mas informacion
                    </a>
                @endif
            </section>

            @if ($relatedEvents->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Otros eventos</h2>
                    <div class="space-y-6">
                        @foreach ($relatedEvents as $item)
                            <x-public.home.event-item
                                :day="$item['day']"
                                :month="$item['month']"
                                :title="$item['title']"
                                :time="$item['time'] ?? null"
                                :location="$item['location'] ?? null"
                                :meta="$item['meta'] ?? null"
                                :url="$item['url']"
                                :highlight-date="$loop->first"
                                tone="home"
                            />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
