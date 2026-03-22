@props([
    'categories' => collect(),
    'activeCategory' => '',
    'activeSection' => null,
])

@php(
    $resolvedActiveSection = $activeSection
        ?? (request()->routeIs('transparencia.contratacion.*')
            ? 'contratacion'
            : (request()->routeIs('transparencia.index') ? 'landing' : 'documentos'))
)

<div class="public-surface p-4 sm:p-5">
    <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Transparencia</p>
    <ul class="mt-3 space-y-1 text-sm">
        <li>
            @php($isTransparencyLanding = $resolvedActiveSection === 'landing')
            <a
                href="{{ route('transparencia.index') }}"
                @if ($isTransparencyLanding) aria-current="page" @endif
                @class([
                    'block rounded-md px-3 py-2 transition',
                    'bg-ied-primary text-white' => $isTransparencyLanding,
                    'text-ied-gray-700 hover:bg-ied-gray-100 hover:text-ied-primary-dark' => ! $isTransparencyLanding,
                ])
            >
                Landing Transparencia
            </a>
        </li>
        <li>
            @php($isTransparencyDocuments = $resolvedActiveSection === 'documentos' && $activeCategory === '')
            <a
                href="{{ route('transparencia.documentos') }}"
                @if ($isTransparencyDocuments) aria-current="page" @endif
                @class([
                    'block rounded-md px-3 py-2 transition',
                    'bg-ied-primary text-white' => $isTransparencyDocuments,
                    'text-ied-gray-700 hover:bg-ied-gray-100 hover:text-ied-primary-dark' => ! $isTransparencyDocuments,
                ])
            >
                Documentos Publicos
            </a>
        </li>
        <li>
            @php($isContractingSection = $resolvedActiveSection === 'contratacion')
            <a
                href="{{ route('transparencia.contratacion.index') }}"
                @if ($isContractingSection) aria-current="page" @endif
                @class([
                    'block rounded-md px-3 py-2 transition',
                    'bg-ied-primary text-white' => $isContractingSection,
                    'text-ied-gray-700 hover:bg-ied-gray-100 hover:text-ied-primary-dark' => ! $isContractingSection,
                ])
            >
                Contratación
            </a>
        </li>
    </ul>
</div>

@if ($categories->isNotEmpty() && $resolvedActiveSection === 'documentos')
    <div class="public-surface p-4 sm:p-5">
        <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Categorias</p>
        <ul class="mt-3 space-y-1 text-sm">
            @foreach ($categories as $category)
                <li>
                    @php($isActiveCategory = $activeCategory === $category['slug'])
                    <a
                        href="{{ route('transparencia.documentos', ['category' => $category['slug']]) }}"
                        @if ($isActiveCategory) aria-current="page" @endif
                        @class([
                            'flex items-center justify-between rounded-md px-3 py-2 transition',
                            'bg-ied-primary text-white' => $isActiveCategory,
                            'text-ied-gray-700 hover:bg-ied-gray-100 hover:text-ied-primary-dark' => ! $isActiveCategory,
                        ])
                    >
                        <span>{{ $category['name'] }}</span>
                        <span class="text-xs font-semibold">{{ $category['count'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
