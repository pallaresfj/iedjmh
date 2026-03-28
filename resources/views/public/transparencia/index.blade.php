@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="transparencia" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Consulta rapida</p>
                <a href="{{ route('transparencia.documentos') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    Ir a listado de documentos
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-8">
            <section class="space-y-4">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Categorias documentales</h2>

                @if ($categories->isEmpty())
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        Aun no hay categorias publicadas con documentos disponibles.
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($categories as $category)
                            <x-public.transparencia.category-card :category="$category" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Publicaciones recientes</h2>
                    <a href="{{ route('transparencia.documentos') }}" class="text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                        Ver todos
                    </a>
                </div>

                @if ($recentDocuments->isEmpty())
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No hay documentos publicados en este momento.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($recentDocuments as $document)
                            <x-public.transparencia.document-card :document="$document" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
