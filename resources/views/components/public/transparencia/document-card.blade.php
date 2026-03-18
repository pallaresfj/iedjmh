@props([
    'document',
])

<article class="public-surface p-5 sm:p-6">
    <div class="flex flex-wrap items-center gap-2 text-xs">
        @if (! empty($document['published_at']))
            <span class="rounded-full bg-ied-gray-100 px-2.5 py-1 font-medium text-ied-gray-700">
                Publicado: {{ $document['published_at'] }}
            </span>
        @endif
        @if (! empty($document['updated_at']))
            <span class="rounded-full bg-ied-gray-100 px-2.5 py-1 font-medium text-ied-gray-700">
                Actualizado: {{ $document['updated_at'] }}
            </span>
        @endif
    </div>

    <h3 class="public-heading mt-3 text-lg font-semibold text-ied-gray-900">{{ $document['title'] }}</h3>

    @if (! empty($document['summary']))
        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $document['summary'] }}</p>
    @endif

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ied-gray-600">
        @if (! empty($document['number']))
            <span><span class="font-semibold text-ied-gray-900">Numero:</span> {{ $document['number'] }}</span>
        @endif
        @if (! empty($document['document_date']))
            <span><span class="font-semibold text-ied-gray-900">Fecha documento:</span> {{ $document['document_date'] }}</span>
        @endif
    </div>

    @if (collect($document['categories'])->isNotEmpty())
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($document['categories'] as $category)
                <li class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-1 text-xs font-medium text-ied-primary-dark">
                    {{ $category['name'] }}
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-5 flex flex-wrap gap-2">
        <a href="{{ $document['detail_url'] }}" class="inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
            Ver detalle
        </a>

        @if (! empty($document['file_url']))
            <a href="{{ $document['file_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                Abrir documento
            </a>
        @endif
    </div>
</article>
