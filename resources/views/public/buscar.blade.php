@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-ied-gray-900">Busqueda</h1>

        <form action="{{ route('buscar') }}" method="GET" class="mt-6">
            <div class="flex gap-3">
                <input
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    placeholder="Buscar noticias, documentos, tramites..."
                    minlength="3"
                    required
                    autofocus
                    class="flex-1 rounded-lg border border-ied-gray-200 bg-white px-4 py-2.5 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                >
                <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ied-primary-dark">
                    Buscar
                </button>
            </div>
        </form>

        @if (filled($query))
            <p class="mt-6 text-sm text-ied-gray-600">
                {{ $resultCount }} resultado{{ $resultCount !== 1 ? 's' : '' }} para "<strong>{{ $query }}</strong>"
            </p>

            @if ($results->isNotEmpty())
                <div class="mt-4 space-y-4">
                    @foreach ($results as $result)
                        <a href="{{ $result['url'] }}" class="block rounded-xl border border-ied-gray-100 bg-white p-4 transition hover:border-ied-primary/30 hover:shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-ied-primary/10 px-2.5 py-0.5 text-xs font-medium text-ied-primary-dark">{{ $result['type'] }}</span>
                                @if ($result['date'])
                                    <span class="text-xs text-ied-gray-500">{{ $result['date'] }}</span>
                                @endif
                            </div>
                            <h2 class="mt-2 font-semibold text-ied-gray-900">{{ $result['title'] }}</h2>
                            @if ($result['excerpt'])
                                <p class="mt-1 text-sm text-ied-gray-600">{{ $result['excerpt'] }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-8 rounded-xl border border-ied-gray-100 bg-ied-gray-50 p-6 text-center">
                    <p class="text-sm text-ied-gray-600">No se encontraron resultados para tu busqueda. Intenta con otros terminos.</p>
                </div>
            @endif
        @endif
    </div>
@endsection
