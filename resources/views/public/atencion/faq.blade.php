@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="public-surface p-5 sm:p-6">
                <form action="{{ route('atencion.faq') }}" method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3" data-auto-filter-form data-auto-filter-target="#faqs-results">
                    <label class="xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Buscar</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Pregunta o palabra clave"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Categoria</span>
                        <select
                            name="category"
                            class="w-full rounded-lg border border-ied-gray-200 bg-white px-3 py-2 text-sm text-ied-gray-900 outline-none transition focus:border-ied-primary focus:ring-2 focus:ring-ied-primary/20"
                        >
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>
                                    {{ $category['name'] }} ({{ $category['count'] }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="md:col-span-2 xl:col-span-3 flex items-end gap-2">
                        <noscript>
                            <button type="submit" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                                Aplicar filtros
                            </button>
                        </noscript>
                        <a href="{{ route('atencion.faq') }}" data-auto-filter-clear class="inline-flex items-center rounded-full border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                            Limpiar
                        </a>
                    </div>
                </form>
            </section>

            <section id="faqs-results" class="space-y-3">
                @if ($items->count() === 0)
                    <div class="rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No se encontraron preguntas frecuentes con los filtros aplicados.
                    </div>
                @else
                    @foreach ($items as $item)
                        <x-public.atencion.faq-item :item="$item" />
                    @endforeach

                    <div class="pt-2">
                        {{ $items->links() }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
