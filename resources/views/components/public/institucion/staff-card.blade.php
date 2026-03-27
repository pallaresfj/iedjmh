@props([
    'member',
])

<article class="public-surface rounded-2xl border border-ied-gray-200 p-3 sm:p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
            <div class="relative size-12 shrink-0 overflow-hidden rounded-full border border-ied-gray-200 bg-ied-gray-100 sm:size-14">
                @if (! empty($member['photo_url']))
                    <img src="{{ $member['photo_url'] }}" alt="Foto de {{ $member['full_name'] }}" class="size-full object-cover" loading="lazy" />
                @else
                    <span class="flex size-full items-center justify-center text-[11px] font-bold uppercase tracking-wide text-ied-primary-dark">
                        {{ $member['initials'] }}
                    </span>
                @endif
            </div>

            <div class="min-w-0 space-y-0.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="public-heading truncate text-lg font-extrabold text-ied-gray-900 sm:text-xl">{{ $member['full_name'] }}</h3>
                    @if (! empty($member['department_label']))
                        <span class="inline-flex items-center rounded-full bg-ied-primary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-ied-primary-dark">
                            {{ $member['department_label'] }}
                        </span>
                    @endif
                </div>

                <p class="inline-flex flex-wrap items-center gap-1.5 text-xs font-medium text-ied-gray-600">
                    <span class="font-semibold text-ied-primary-dark">{{ $member['position_title'] }}</span>
                    @if (! empty($member['campus_name']))
                        <span aria-hidden="true">•</span>
                        <span class="inline-flex items-center gap-1 whitespace-nowrap">
                            <svg class="h-3 w-3 shrink-0 text-ied-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2.5a5.5 5.5 0 0 0-5.5 5.5c0 3.468 3.74 7.68 5.06 9.075a.6.6 0 0 0 .88 0C11.76 15.68 15.5 11.468 15.5 8A5.5 5.5 0 0 0 10 2.5Zm0 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                            </svg>
                            {{ $member['campus_name'] }}
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <div class="sm:pl-4">
            @if (! empty($member['contact_url']))
                <a href="{{ $member['contact_url'] }}" class="inline-flex w-full items-center justify-center rounded-xl border border-ied-primary/35 px-6 py-2.5 text-base font-semibold text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary sm:w-auto">
                    Contactar
                </a>
            @else
                <span class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl border border-ied-gray-200 px-6 py-2.5 text-sm font-semibold text-ied-gray-500 sm:w-auto">
                    Sin contacto
                </span>
            @endif
        </div>
    </div>
</article>
