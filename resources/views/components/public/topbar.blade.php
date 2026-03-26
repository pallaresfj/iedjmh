@props([
    'homeThemeable' => false,
])

@php($contact = \App\Support\PublicSettings::contact())
@php($email = $contact['email'])
@php($phone = $contact['phone'])
@php($govLabel = config('institution.govbar.label', 'GOV.CO'))
@php($location = $contact['location'])

<div @class([
    'public-topbar',
    'public-topbar--home' => $homeThemeable,
])>
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
                <button
                    type="button"
                    class="public-home-theme-toggle public-home-theme-toggle--icon"
                    data-public-theme-toggle
                    aria-label="Cambiar tema del sitio"
                    aria-pressed="false"
                >
                    <span class="material-symbols-outlined !text-[18px]" data-public-theme-toggle-icon aria-hidden="true">dark_mode</span>
                </button>
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
