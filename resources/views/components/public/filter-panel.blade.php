@props([
    'action',
    'target',
    'title' => 'Filtros de busqueda',
])

<div class="public-surface public-filter-panel p-5 sm:p-6">
    <p class="public-heading text-xs font-semibold uppercase tracking-widest text-ied-primary-dark">{{ $title }}</p>

    <form
        action="{{ $action }}"
        method="GET"
        class="mt-6 space-y-6"
        data-auto-filter-form
        data-auto-filter-target="{{ $target }}"
    >
        {{ $slot }}

        <div class="pt-4">
            <noscript>
                <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-ied-primary/25 bg-ied-primary/5 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary/40 hover:bg-ied-primary/10">
                    Aplicar
                </button>
            </noscript>
            <a href="{{ $action }}" data-auto-filter-clear class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-ied-primary/25 bg-ied-primary/5 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary/40 hover:bg-ied-primary/10">
                <span class="material-symbols-outlined !text-[15px]" aria-hidden="true">refresh</span>
                Limpiar Filtros
            </a>
        </div>
    </form>
</div>
