@props([
    'document',
])

<article class="public-surface overflow-hidden">
    <div class="flex gap-4 p-5 sm:gap-5 sm:p-6">
        <div
            class="hidden size-12 shrink-0 items-center justify-center rounded-xl border sm:inline-flex"
            style="background-color: rgba(var(--color-ied-primary-light-rgb), 0.34); border-color: rgba(var(--color-ied-primary-rgb), 0.22);"
        >
            <span class="material-symbols-outlined !text-[22px] text-ied-primary-dark" aria-hidden="true">description</span>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                @if (collect($document['categories'])->isNotEmpty())
                    @foreach ($document['categories'] as $category)
                        <span class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-0.5 text-[11px] font-semibold text-ied-primary-dark">
                            {{ $category['name'] }}
                        </span>
                    @endforeach
                @endif
            </div>

            <h3 class="public-heading mt-2 text-base font-semibold text-ied-gray-900 sm:text-lg">{{ $document['title'] }}</h3>

            @if (! empty($document['summary']))
                <p class="mt-1.5 text-sm leading-relaxed text-ied-gray-700">{{ $document['summary'] }}</p>
            @endif

            <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-ied-gray-600">
                @if (! empty($document['number']))
                    <div class="flex gap-1">
                        <dt class="font-semibold text-ied-gray-900">Numero:</dt>
                        <dd>{{ $document['number'] }}</dd>
                    </div>
                @endif
                @if (! empty($document['document_date']))
                    <div class="flex gap-1">
                        <dt class="font-semibold text-ied-gray-900">Fecha:</dt>
                        <dd>{{ $document['document_date'] }}</dd>
                    </div>
                @endif
                @if (! empty($document['published_at']))
                    <div class="flex gap-1">
                        <dt class="font-semibold text-ied-gray-900">Publicado:</dt>
                        <dd>{{ $document['published_at'] }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                @if (! empty($document['file_url']))
                    <a href="{{ $document['file_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                        <span class="material-symbols-outlined !text-[15px]" aria-hidden="true">open_in_new</span>
                        Abrir documento
                    </a>
                @endif

                <a href="{{ $document['detail_url'] }}" class="inline-flex items-center gap-1.5 rounded-lg border border-ied-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-gray-700 transition hover:border-ied-gray-400 hover:text-ied-gray-900">
                    Ver detalle
                </a>
            </div>
        </div>
    </div>
</article>
