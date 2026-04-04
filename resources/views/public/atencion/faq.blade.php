@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />

            <x-public.filter-panel :action="route('atencion.faq')" target="#faqs-results">
                <label>
                    <span class="public-filter-label">Buscar</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Pregunta o palabra clave" class="public-filter-input">
                </label>

                <label>
                    <span class="public-filter-label">Categoria</span>
                    <select name="category" class="public-filter-input">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>
                                {{ $category['name'] }} ({{ $category['count'] }})
                            </option>
                        @endforeach
                    </select>
                </label>
            </x-public.filter-panel>
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

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
                        {{ $items->links('vendor.pagination.public') }}
                    </div>
                @endif
            </section>
        </div>
    </x-public.internal-page>
@endsection
