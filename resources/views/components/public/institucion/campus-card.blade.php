@props([
    'campus',
])

<article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
    <div class="flex items-start gap-4">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
            <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">location_on</span>
        </span>

        <div class="min-w-0 space-y-0.5">
            <h3 class="public-heading text-lg font-extrabold text-ied-gray-900">{{ $campus['name'] }}</h3>

            @if (! empty($campus['description']))
                <p class="text-sm leading-relaxed text-ied-gray-600">{{ $campus['description'] }}</p>
            @endif
        </div>
    </div>

    <dl class="mt-4 space-y-2 text-sm">
        @if (! empty($campus['address']))
            <div class="flex items-start gap-2">
                <span class="material-symbols-outlined mt-0.5 text-base text-ied-gray-400" aria-hidden="true">home</span>
                <div>
                    <dt class="sr-only">Direccion</dt>
                    <dd class="text-ied-gray-700">{{ $campus['address'] }}</dd>
                </div>
            </div>
        @endif

        @if (! empty($campus['phone']))
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">call</span>
                <div>
                    <dt class="sr-only">Telefono</dt>
                    <dd class="text-ied-gray-700">{{ $campus['phone'] }}</dd>
                </div>
            </div>
        @endif

        @if (! empty($campus['email']))
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">mail</span>
                <div>
                    <dt class="sr-only">Correo</dt>
                    <dd>
                        <a href="mailto:{{ $campus['email'] }}" class="text-ied-primary-dark transition hover:text-ied-primary">{{ $campus['email'] }}</a>
                    </dd>
                </div>
            </div>
        @endif
    </dl>

    @if (! empty($campus['map_url']))
        <a href="{{ $campus['map_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary">
            Ver ubicacion
            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.19L10.22 5.53a.75.75 0 011.06-1.06l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 11-1.06-1.06l3.72-3.72H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
        </a>
    @endif
</article>
