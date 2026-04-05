@extends('layouts.public.app')

@section('title', $document['title'])

@section('content')
    <x-public.internal-page :title="$document['title']" :lead="$document['summary']" section-key="transparencia" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('transparencia.documentos') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Listado de documentos
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Metadatos de publicacion</h2>

                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Publicado</dt>
                        <dd>{{ $document['published_at'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Actualizado</dt>
                        <dd>{{ $document['updated_at'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Fecha del documento</dt>
                        <dd>{{ $document['document_date'] ?? 'No registrada' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Numero</dt>
                        <dd>{{ $document['number'] ?? 'No registrado' }}</dd>
                    </div>
                </dl>

                @if (collect($document['categories'])->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($document['categories'] as $category)
                            <li class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-1 text-xs font-medium text-ied-primary-dark">
                                {{ $category['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($document['description']))
                    <div class="mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        @php
                            $description = (string) $document['description'];
                            $containsHtml = $description !== strip_tags($description);
                        @endphp

                        @if ($containsHtml)
                            {!! $description !!}
                        @else
                            {!! nl2br(e($description)) !!}
                        @endif
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    @if (! empty($document['file_url']))
                        <a href="{{ $document['file_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                            Abrir documento
                        </a>
                    @endif
                    <a href="{{ route('transparencia.documentos') }}" class="inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                        Regresar al listado
                    </a>
                </div>
            </section>

            @if ($related->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Documentos relacionados</h2>
                    <div class="space-y-4">
                        @foreach ($related as $item)
                            <x-public.transparencia.document-card :document="$item" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
