@php($email = config('institution.email'))
@php($phone = config('institution.phone'))
@php($govLabel = config('institution.govbar.label', 'GOV.CO'))
@php($location = \App\Support\PublicSettings::get('location', collect([config('institution.city'), config('institution.department')])->filter()->join(', ')))

<div class="bg-slate-900 text-white">
    <div class="public-container py-1.5">
        <div class="public-shell flex flex-col gap-2 text-[11px] sm:flex-row sm:items-center sm:justify-between">
            <p class="inline-flex items-center gap-2 font-semibold uppercase tracking-[0.18em] text-white/95">
                <span class="material-symbols-outlined text-sm !text-[14px]" aria-hidden="true">flag</span>
                {{ $govLabel }}
            </p>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-white/85">
                @if (filled($location))
                    <span>{{ $location }}</span>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="transition hover:text-white focus-visible:text-white">
                        {{ $email }}
                    </a>
                @endif
                @if ($phone)
                    <span>{{ $phone }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
