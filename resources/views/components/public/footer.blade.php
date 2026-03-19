@php($primaryNav = config('institution.navigation.primary', []))
@php($footerNav = config('institution.navigation.footer', []))
@php($email = config('institution.email'))
@php($phone = config('institution.phone'))
@php($allies = config('institution.allies', []))

<footer class="mt-14 border-t border-white/5 bg-[#102117] text-slate-200">
    <div class="public-container pt-14 pb-8">
        <div class="public-shell grid gap-10 border-b border-white/10 pb-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-ied-primary-light !text-[28px]" aria-hidden="true">agriculture</span>
                    <p class="public-heading text-lg font-bold text-white">{{ config('institution.short_name') }}</p>
                </div>

                <p class="text-sm leading-relaxed text-slate-300">
                    {{ config('institution.name') }}. Formando lideres para el agro y la vida.
                </p>

                <ul class="space-y-2 text-sm text-slate-300">
                    <li>{{ config('institution.address') }}</li>
                    @if ($phone)
                        <li>{{ $phone }}</li>
                    @endif
                    @if ($email)
                        <li>{{ $email }}</li>
                    @endif
                </ul>
            </div>

            <div class="space-y-5">
                <p class="public-heading text-xs font-bold uppercase tracking-[0.2em] text-white/70">Explorar</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($primaryNav as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="text-slate-300 transition hover:text-white">
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
                            <a href="{{ route($item['route']) }}" class="text-slate-300 transition hover:text-white">
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
                            class="inline-flex h-9 items-center justify-center rounded-md bg-white/10 px-3 text-[11px] font-semibold text-slate-200 transition hover:bg-white/15 hover:text-white"
                        >
                            {{ $ally['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="public-shell flex flex-col gap-3 pt-6 text-[11px] text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ config('institution.name') }}. Sitio institucional oficial.</p>
            <a href="{{ url('/admin') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-slate-400 transition hover:text-white">
                <span class="material-symbols-outlined !text-[14px]" aria-hidden="true">shield_person</span>
                Acceso administrativo
            </a>
        </div>
    </div>
</footer>
