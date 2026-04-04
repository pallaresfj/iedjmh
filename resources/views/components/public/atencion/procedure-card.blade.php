@props([
    'item',
])

<article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
    <div class="flex gap-4 sm:gap-5">
        <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30 sm:inline-flex">
            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">assignment</span>
        </span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if (! empty($item['category']))
                    <span class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-0.5 font-semibold text-ied-primary-dark">{{ $item['category'] }}</span>
                @endif
                @if ($item['is_online'])
                    <span class="rounded-full border border-ied-primary/30 bg-ied-primary/10 px-2.5 py-0.5 font-semibold text-ied-primary-dark">En linea</span>
                @endif
            </div>

            <h3 class="public-heading mt-2 text-base font-semibold text-ied-gray-900 sm:text-lg">{{ $item['name'] }}</h3>

            @if (! empty($item['summary']))
                <p class="mt-1.5 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
            @endif

            @if (! empty($item['requirements']))
                <div class="mt-3 rounded-lg bg-ied-gray-100 p-3 text-sm text-ied-gray-700">
                    <p class="font-semibold text-ied-gray-900">Requisitos</p>
                    <p class="mt-1">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item['requirements']), 180) }}</p>
                </div>
            @endif

            <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-ied-gray-600">
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Publicado:</dt>
                    <dd>{{ $item['published_at'] ?? 'No disponible' }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Actualizado:</dt>
                    <dd>{{ $item['updated_at'] ?? 'No disponible' }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Respuesta:</dt>
                    <dd>{{ $item['response_time'] ?? 'No definido' }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Costo:</dt>
                    <dd>{{ $item['cost'] ?? 'No definido' }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Canal:</dt>
                    <dd>{{ $item['channel'] ?? 'No definido' }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold text-ied-gray-900">Contacto:</dt>
                    <dd>{{ $item['contact_email'] ?? $item['contact_phone'] ?? 'No definido' }}</dd>
                </div>
            </dl>

            @if (! empty($item['application_url']))
                <a href="{{ $item['application_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                    <span class="material-symbols-outlined !text-[15px]" aria-hidden="true">open_in_new</span>
                    Iniciar tramite
                </a>
            @endif
        </div>
    </div>
</article>
