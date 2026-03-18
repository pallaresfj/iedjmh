@props([
    'item',
])

<article class="public-surface p-5 sm:p-6">
    <div class="flex flex-wrap gap-2 text-xs">
        @if (! empty($item['category']))
            <span class="rounded-full bg-ied-primary/10 px-2.5 py-1 font-medium text-ied-primary-dark">{{ $item['category'] }}</span>
        @endif
        @if ($item['is_online'])
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 font-medium text-emerald-800">En linea</span>
        @endif
    </div>

    <h3 class="public-heading mt-3 text-lg font-semibold text-ied-gray-900">{{ $item['name'] }}</h3>

    @if (! empty($item['summary']))
        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
    @endif

    @if (! empty($item['requirements']))
        <div class="mt-3 rounded-lg bg-ied-gray-100 p-3 text-sm text-ied-gray-700">
            <p class="font-semibold text-ied-gray-900">Requisitos</p>
            <p class="mt-1">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item['requirements']), 180) }}</p>
        </div>
    @endif

    <dl class="mt-4 grid gap-2 text-xs text-ied-gray-600 sm:grid-cols-2">
        <div>
            <dt class="font-semibold text-ied-gray-900">Publicado</dt>
            <dd>{{ $item['published_at'] ?? 'No disponible' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-ied-gray-900">Actualizado</dt>
            <dd>{{ $item['updated_at'] ?? 'No disponible' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-ied-gray-900">Tiempo de respuesta</dt>
            <dd>{{ $item['response_time'] ?? 'No definido' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-ied-gray-900">Costo</dt>
            <dd>{{ $item['cost'] ?? 'No definido' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-ied-gray-900">Canal</dt>
            <dd>{{ $item['channel'] ?? 'No definido' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-ied-gray-900">Contacto</dt>
            <dd>{{ $item['contact_email'] ?? $item['contact_phone'] ?? 'No definido' }}</dd>
        </div>
    </dl>

    @if (! empty($item['application_url']))
        <a href="{{ $item['application_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
            Iniciar tramite
        </a>
    @endif
</article>
