@php($menuItems = collect(config('institution.navigation.primary', []))->reject(fn (array $item): bool => $item['route'] === 'zona-academica.index')->values()->all())

<header class="border-b border-slate-200 bg-white">
    <div class="public-container py-4">
        <div class="public-shell flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-ied-primary text-white">
                    <span class="material-symbols-outlined !text-[30px]" aria-hidden="true">agriculture</span>
                </span>
                <span class="min-w-0">
                    <span class="public-heading block truncate text-[20px] font-black uppercase leading-tight tracking-[-0.02em] text-ied-primary-dark">
                        {{ config('institution.display_name', 'IED JOSÉ MARÍA HERRERA') }}
                    </span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                        Institucion Educativa Departamental Agropecuaria
                    </span>
                </span>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('academico.zona-academica') }}" class="rounded-full border-2 border-ied-primary/20 px-4 py-2 text-xs font-bold text-ied-primary transition hover:bg-ied-primary/10">
                    SIEE
                </a>
                <a href="{{ route('zona-academica.index') }}" class="rounded-full border-2 border-ied-primary/20 px-4 py-2 text-xs font-bold text-ied-primary transition hover:bg-ied-primary/10">
                    Aula Virtual
                </a>
                <a href="{{ route('atencion.index') }}" class="rounded-full bg-ied-primary px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-ied-primary-dark">
                    Matriculas
                </a>
            </div>
        </div>
    </div>

    <div class="sticky top-0 z-40 hidden border-t border-slate-100 bg-white/95 backdrop-blur lg:block">
        <div class="public-container">
            <div class="public-shell">
                <x-public.nav :items="$menuItems" />
            </div>
        </div>
    </div>

    <div class="border-t border-slate-100 bg-white lg:hidden">
        <div class="public-container py-3">
            <div class="public-shell">
                <details class="group relative">
                    <summary class="inline-flex list-none items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                        <span>Menu</span>
                        <span class="material-symbols-outlined !text-[18px] transition group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="mt-3 space-y-3 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                        <x-public.nav :items="$menuItems" mobile />
                        <div class="grid gap-2 border-t border-slate-200 pt-3">
                            <a href="{{ route('atencion.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-700">
                                Matriculas
                            </a>
                            <a href="{{ route('zona-academica.index') }}" class="rounded-lg bg-ied-primary px-3 py-2 text-center text-sm font-semibold text-white">
                                Zona Academica
                            </a>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
