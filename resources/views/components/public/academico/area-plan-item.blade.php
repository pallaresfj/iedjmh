@props([
    'item',
])

<article class="rounded-2xl border border-ied-gray-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between md:gap-6">
        <div class="min-w-0 flex items-start gap-3 sm:gap-4 md:flex-1">
            <div
                class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl border text-ied-primary-dark sm:size-14 sm:rounded-2xl"
                style="background-color: rgba(var(--color-ied-primary-light-rgb), 0.34); border-color: rgba(var(--color-ied-primary-rgb), 0.22);"
            >
                <span class="material-symbols-outlined !text-[23px] sm:!text-[25px]" aria-hidden="true">{{ $item['icon'] }}</span>
            </div>

            <div class="min-w-0">
                <h3 class="public-heading text-lg font-extrabold leading-tight tracking-[-0.03em] text-ied-gray-900 sm:text-[2rem]">
                    {{ $item['area_name'] }}
                </h3>
                <p class="mt-1 break-words text-xs leading-relaxed text-ied-gray-700 [overflow-wrap:anywhere] sm:text-sm">
                    <span class="font-semibold text-ied-primary-dark">Docentes Responsables:</span>
                    {{ $item['responsible_teachers'] }}.
                </p>
            </div>
        </div>

        <div class="md:ml-auto md:shrink-0 md:self-center">
            <a
                href="{{ $item['plan_url'] }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-10 shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg border border-ied-primary/30 bg-ied-primary/10 px-4 text-sm font-semibold text-ied-primary-dark transition hover:border-ied-primary-dark hover:bg-ied-primary-dark hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ied-primary sm:h-11 sm:rounded-xl sm:text-sm"
            >
                Consultar Plan
                <span class="material-symbols-outlined !text-[18px] sm:!text-[19px]" aria-hidden="true">open_in_new</span>
            </a>
        </div>
    </div>
</article>
