@php
    $institutionName = $this->getInstitutionName();
    $logoUrl = $this->getInstitutionLogoUrl();
    $heroBackgroundUrl = $this->getHeroBackgroundUrl();
@endphp

<x-filament-panels::page.simple :heading="null" :subheading="null" class="agro-admin-login-page">
    <section class="agro-admin-login" style="--agro-login-bg-image: url('{{ $heroBackgroundUrl }}');">
        <div class="agro-admin-login__overlay" aria-hidden="true"></div>

        <header class="agro-admin-login__header">
            <p class="agro-admin-login__brand">{{ $institutionName }}</p>
        </header>

        <div class="agro-admin-login__content">
            <div class="agro-admin-login__card">
                <div class="agro-admin-login__logo-wrap">
                    @if (filled($logoUrl))
                        <img src="{{ $logoUrl }}" alt="Logo institucional" class="agro-admin-login__logo" />
                    @else
                        <x-filament::icon icon="heroicon-o-academic-cap" class="agro-admin-login__logo-fallback" />
                    @endif
                </div>

                <h1 class="agro-admin-login__title">Acceso al Portal</h1>
                <p class="agro-admin-login__subtitle">Ingresa con tu correo institucional</p>

                <div class="agro-admin-login__form">
                    {{ $this->content }}
                </div>
            </div>
        </div>

        <footer class="agro-admin-login__footer">
            <p>
                &copy; {{ now()->year }} {{ $institutionName }} - Desarrollado por
                <a href="https://asyservicios.com" target="_blank" rel="noopener noreferrer">
                    AS&amp;Servicios.com
                </a>
            </p>
        </footer>
    </section>
</x-filament-panels::page.simple>
