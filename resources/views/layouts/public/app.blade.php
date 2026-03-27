<!DOCTYPE html>
@php($isHomeRoute = request()->routeIs('home'))
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-home-page="{{ $isHomeRoute ? '1' : '0' }}"
    data-public-theme="light"
    data-home-theme="light"
>
    <head>
        @php($pageTitle = trim($__env->yieldContent('title')))
        @php($pageLead = isset($lead) && is_string($lead) ? trim(strip_tags($lead)) : '')
        @php($metaDescription = trim($__env->yieldContent('meta_description')))
        @php($metaDescription = $metaDescription !== '' ? $metaDescription : ($pageLead !== '' ? $pageLead : config('institution.seo.default_description')))
        @php($canonicalUrl = trim($__env->yieldContent('canonical')))
        @php($canonicalUrl = $canonicalUrl !== '' ? $canonicalUrl : url()->current())
        @php($metaImage = trim($__env->yieldContent('meta_image')))
        @php($metaImage = $metaImage !== '' ? $metaImage : url(config('institution.seo.default_image')))
        @php($institutionName = \App\Support\PublicSettings::get('institution_name', config('institution.name')))
        @php($settingsLogoPath = \App\Support\PublicSettings::get('logo_path'))
        @php($settingsLogoUrl = \App\Support\PublicSettings::mediaUrl($settingsLogoPath))
        @php($faviconIsSvg = is_string($settingsLogoPath) && \Illuminate\Support\Str::endsWith(strtolower($settingsLogoPath), '.svg'))
        @php($themeColors = \App\Support\PublicSettings::themeColors())
        @php($mapContact = \App\Support\PublicSettings::contact())
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="{{ $metaDescription }}" />
        <link rel="canonical" href="{{ $canonicalUrl }}" />

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ $institutionName }}" />
        <meta property="og:title" content="{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}" />
        <meta property="og:description" content="{{ $metaDescription }}" />
        <meta property="og:url" content="{{ $canonicalUrl }}" />
        <meta property="og:image" content="{{ $metaImage }}" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}" />
        <meta name="twitter:description" content="{{ $metaDescription }}" />
        <meta name="twitter:image" content="{{ $metaImage }}" />

        <title>{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}</title>

        @if ($settingsLogoUrl)
            <link rel="icon" href="{{ $settingsLogoUrl }}" @if ($faviconIsSvg) type="image/svg+xml" @endif sizes="any">
            <link rel="apple-touch-icon" href="{{ $faviconIsSvg ? '/apple-touch-icon.png' : $settingsLogoUrl }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

        <style>
            :root {
@foreach ($themeColors as $cssVar => $value)
                {{ $cssVar }}: {{ $value }};
@endforeach
            }
        </style>

        <script>
            (() => {
                const storageKey = 'ied_public_theme';
                const legacyStorageKey = 'ied_public_home_theme';
                let theme = 'light';

                try {
                    const storedTheme = localStorage.getItem(storageKey);
                    const legacyTheme = localStorage.getItem(legacyStorageKey);

                    if (storedTheme === 'light' || storedTheme === 'dark') {
                        theme = storedTheme;
                    } else if (legacyTheme === 'light' || legacyTheme === 'dark') {
                        theme = legacyTheme;
                    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        theme = 'dark';
                    }
                } catch (error) {
                    theme = 'light';
                }

                document.documentElement.setAttribute('data-public-theme', theme);
                document.documentElement.setAttribute('data-home-theme', theme);
            })();
        </script>

        @vite(['resources/css/public.css', 'resources/js/app.js'])
    </head>
    <body @class([
        'public-site antialiased',
        'public-site--home' => $isHomeRoute,
    ])>
        <div class="flex min-h-screen flex-col">
            <a href="#contenido-principal" class="public-skip-link sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:px-3 focus:py-2 focus:text-sm">
                Ir al contenido principal
            </a>

            <x-public.topbar :home-themeable="$isHomeRoute" />
            <x-public.header :home-themeable="$isHomeRoute" />
            <x-public.location-map-modal
                :latitude="$mapContact['latitude']"
                :longitude="$mapContact['longitude']"
                :location-label="$mapContact['location']"
            />

            <main id="contenido-principal" class="flex-1">
                @yield('content')
            </main>

            <x-public.footer :home-themeable="$isHomeRoute" />
        </div>

        @stack('scripts')
    </body>
</html>
