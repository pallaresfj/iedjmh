<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($pageTitle = trim($__env->yieldContent('title')))
        @php($pageLead = isset($lead) && is_string($lead) ? trim(strip_tags($lead)) : '')
        @php($metaDescription = trim($__env->yieldContent('meta_description')))
        @php($metaDescription = $metaDescription !== '' ? $metaDescription : ($pageLead !== '' ? $pageLead : config('institution.seo.default_description')))
        @php($canonicalUrl = trim($__env->yieldContent('canonical')))
        @php($canonicalUrl = $canonicalUrl !== '' ? $canonicalUrl : url()->current())
        @php($metaImage = trim($__env->yieldContent('meta_image')))
        @php($metaImage = $metaImage !== '' ? $metaImage : url(config('institution.seo.default_image')))
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="{{ $metaDescription }}" />
        <link rel="canonical" href="{{ $canonicalUrl }}" />

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ config('institution.name') }}" />
        <meta property="og:title" content="{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}" />
        <meta property="og:description" content="{{ $metaDescription }}" />
        <meta property="og:url" content="{{ $canonicalUrl }}" />
        <meta property="og:image" content="{{ $metaImage }}" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}" />
        <meta name="twitter:description" content="{{ $metaDescription }}" />
        <meta name="twitter:image" content="{{ $metaImage }}" />

        <title>{{ $pageTitle !== '' ? $pageTitle.' - '.config('app.name') : config('app.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

        @vite(['resources/css/public.css', 'resources/js/app.js'])
    </head>
    <body class="public-site antialiased">
        <div class="flex min-h-screen flex-col">
            <a href="#contenido-principal" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:text-ied-gray-900">
                Ir al contenido principal
            </a>

            <x-public.topbar />
            <x-public.header />

            <main id="contenido-principal" class="flex-1">
                @yield('content')
            </main>

            <x-public.footer />
        </div>

        @stack('scripts')
    </body>
</html>
