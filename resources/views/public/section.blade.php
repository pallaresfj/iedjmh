@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$description" :section-key="$sectionKey">
        <div class="space-y-4">
            <p class="text-base leading-relaxed text-ied-gray-700">
                Esta seccion ya cuenta con la estructura visual institucional y queda lista para integrar contenidos
                reales desde el CMS (paginas, noticias, documentos, eventos y modulos relacionados).
            </p>
            <p class="text-base leading-relaxed text-ied-gray-700">
                En la siguiente fase se conectaran componentes de datos, bloques de contenido y rutas internas
                especificas para esta seccion.
            </p>
        </div>
    </x-public.internal-page>
@endsection
