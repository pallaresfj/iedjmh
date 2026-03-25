@php($primaryNav = config('institution.navigation.primary', []))
@php($footerNav = config('institution.navigation.footer', []))
@php($allies = \App\Support\PublicSettings::allies())
@php($institutionName = \App\Support\PublicSettings::get('institution_name', config('institution.name')))
@php($logoUrl = \App\Support\PublicSettings::mediaUrl(\App\Support\PublicSettings::get('logo_path')))

<footer class="public-footer mt-14 border-t border-white/10 text-white/90">
    <div class="public-container pt-14 pb-8">
        <div class="public-shell grid gap-10 border-b border-white/10 pb-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    @if (filled($logoUrl))
                        <span class="public-footer__logo-wrap grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-white/20 bg-white">
                            <img src="{{ $logoUrl }}" alt="Logo institucional footer" class="public-footer__logo size-full object-contain p-1.5" />
                        </span>
                    @else
                        <span class="public-footer__icon-fallback material-symbols-outlined text-ied-primary-light !text-[28px]" aria-hidden="true">agriculture</span>
                    @endif
                    <p class="public-heading text-lg font-bold text-white">{{ $institutionName }}</p>
                </div>
            </div>

            <div class="space-y-5">
                <p class="public-heading text-xs font-bold uppercase tracking-[0.2em] text-white/70">Explorar</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($primaryNav as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="text-white/75 transition hover:text-white">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="space-y-5">
                <p class="public-heading text-xs font-bold uppercase tracking-[0.2em] text-white/70">Atencion</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($footerNav as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="text-white/75 transition hover:text-white">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="space-y-5">
                <p class="public-heading text-xs font-bold uppercase tracking-[0.2em] text-white/70">Aliados</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($allies as $ally)
                        <a
                            href="{{ $ally['url'] ?? '#' }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-white/10 px-3 text-[11px] font-semibold text-white/85 transition hover:bg-white/15 hover:text-white"
                        >
                            {{ $ally['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="public-shell flex flex-col gap-3 pt-6 text-[11px] text-white/55 sm:flex-row sm:items-center sm:justify-between">
            <p>
                &copy; {{ now()->year }} {{ $institutionName }} - Desarrollado por
                <a href="https://asyservicios.com" target="_blank" rel="noopener noreferrer" class="text-white/75 transition hover:text-white">
                    AS&amp;Servicios.com
                </a>
            </p>
            <a href="{{ url('/admin') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-white/65 transition hover:text-white">
                <span class="material-symbols-outlined !text-[14px]" aria-hidden="true">shield_person</span>
                Acceso administrativo
            </a>
        </div>
    </div>
</footer>
