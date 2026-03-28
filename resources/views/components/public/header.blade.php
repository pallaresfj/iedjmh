@props([
    'homeThemeable' => false,
])

@php($menuItems = config('institution.navigation.primary', []))
@php($institutionName = \App\Support\PublicSettings::get('institution_name', config('institution.display_name', config('institution.name', 'IED JOSÉ MARÍA HERRERA'))))
@php($logoUrl = \App\Support\PublicSettings::mediaUrl(\App\Support\PublicSettings::get('logo_path')))
@php($institutionDane = \App\Support\PublicSettings::get('dane', ''))
@php($institutionNit = \App\Support\PublicSettings::get('nit', ''))
@php($sieeUrl = \App\Support\PublicSettings::get('siee'))
@php($aulaVirtualUrl = \App\Support\PublicSettings::get('aula_virtual'))

<header @class([
    'public-header border-b border-ied-gray-200 bg-white',
    'public-header--home' => $homeThemeable,
])>
    <div class="public-container py-4">
        <div class="public-shell public-header__main-row flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <a href="{{ route('home') }}" class="public-header__brand-link flex min-w-0 items-center gap-3">
                @if (filled($logoUrl))
                    <span class="public-header__logo-wrap grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-ied-primary/20 bg-white">
                        <img src="{{ $logoUrl }}" alt="Logo institucional" class="size-full object-contain p-1.5" />
                    </span>
                @else
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-ied-primary text-white">
                        <span class="material-symbols-outlined !text-[30px]" aria-hidden="true">agriculture</span>
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="public-header__title public-heading block truncate text-[20px] font-black uppercase leading-tight tracking-[-0.02em] text-ied-primary-dark">
                        {{ $institutionName }}
                    </span>
                    <span class="public-header__meta block text-[10px] font-semibold uppercase tracking-[0.12em] text-ied-gray-600">
                        DANE: {{ $institutionDane }} - NIT: {{ $institutionNit }}
                    </span>
                </span>
            </a>

            <div class="public-header__actions hidden items-center gap-2 md:flex">
                @if (filled($sieeUrl))
                    <a href="{{ $sieeUrl }}" target="_blank" rel="noopener noreferrer" class="public-header__action public-header__action--secondary rounded-full border-2 border-ied-primary/20 px-4 py-2 text-xs font-bold text-ied-primary transition hover:bg-ied-primary/10">
                        SIEE
                    </a>
                @endif
                @if (filled($aulaVirtualUrl))
                    <a href="{{ $aulaVirtualUrl }}" target="_blank" rel="noopener noreferrer" class="public-header__action public-header__action--secondary rounded-full border-2 border-ied-primary/20 px-4 py-2 text-xs font-bold text-ied-primary transition hover:bg-ied-primary/10">
                        Aula Virtual
                    </a>
                @endif
                <a href="{{ route('atencion.index') }}" class="public-header__action public-header__action--primary rounded-full bg-ied-primary px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-ied-primary-dark">
                    Matriculas
                </a>
                <a href="{{ route('buscar') }}" class="public-header__action grid size-9 place-items-center rounded-full border-2 border-ied-primary/20 text-ied-primary transition hover:bg-ied-primary/10" aria-label="Buscar en el sitio">
                    <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">search</span>
                </a>
            </div>
        </div>
    </div>

    <div class="public-header__desktop-nav sticky top-0 z-40 hidden backdrop-blur lg:block">
        <div class="public-container">
            <div class="public-shell">
                <x-public.nav :items="$menuItems" />
            </div>
        </div>
    </div>

    <div class="public-header__mobile-nav lg:hidden">
        <div class="public-container py-3">
            <div class="public-shell">
                <details class="group relative">
                    <summary class="public-header__mobile-summary inline-flex list-none items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold">
                        <span>Menu</span>
                        <span class="material-symbols-outlined !text-[18px] transition group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="public-header__mobile-panel mt-3 space-y-3 rounded-xl p-3 shadow-lg">
                        <x-public.nav :items="$menuItems" mobile />
                        <div class="public-header__mobile-actions grid gap-2 border-t border-ied-gray-200 pt-3">
                            <a href="{{ route('atencion.index') }}" class="public-header__mobile-action public-header__mobile-action--primary rounded-lg border border-ied-gray-200 px-3 py-2 text-center text-sm font-semibold text-ied-gray-700">
                                Matriculas
                            </a>
                            @if (filled($sieeUrl))
                                <a href="{{ $sieeUrl }}" target="_blank" rel="noopener noreferrer" class="public-header__mobile-action public-header__mobile-action--secondary rounded-lg border border-ied-gray-200 px-3 py-2 text-center text-sm font-semibold text-ied-gray-700">
                                    SIEE
                                </a>
                            @endif
                            @if (filled($aulaVirtualUrl))
                                <a href="{{ $aulaVirtualUrl }}" target="_blank" rel="noopener noreferrer" class="public-header__mobile-action public-header__mobile-action--secondary rounded-lg bg-ied-primary px-3 py-2 text-center text-sm font-semibold text-white">
                                    Aula Virtual
                                </a>
                            @endif
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
