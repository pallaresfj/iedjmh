@props([
    'items' => [],
    'mobile' => false,
])

@if ($mobile)
    <nav aria-label="Navegacion principal" class="public-nav public-nav--mobile grid gap-2">
        @foreach ($items as $item)
            @php($children = collect($item['children'] ?? []))
            @php($isActive = request()->routeIs($item['route']) || $children->contains(fn (array $child): bool => request()->routeIs($child['route'])))

            @if ($children->isNotEmpty())
                <details @if($isActive) open @endif class="public-nav__mobile-group group rounded-xl border p-2">
                    <summary class="public-nav__mobile-summary flex list-none items-center justify-between rounded-lg px-2 py-2 text-sm font-semibold">
                        <span>{{ $item['label'] }}</span>
                        <span class="material-symbols-outlined !text-[18px] transition group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="mt-1 grid gap-1 px-1 pb-1">
                        @foreach ($children as $child)
                            @php($childActive = request()->routeIs($child['route']))
                            <a
                                href="{{ route($child['route']) }}"
                                @if ($childActive) aria-current="page" @endif
                                @class([
                                    'public-nav__mobile-link public-nav__mobile-link--child flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition',
                                    'public-nav__mobile-link--active' => $childActive,
                                ])
                            >
                                <x-public.icon :icon="$child['icon'] ?? 'chevron_right'" class="!text-[18px]" />
                                <span>{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @else
                <a
                    href="{{ route($item['route']) }}"
                    @if ($isActive) aria-current="page" @endif
                    @class([
                        'public-nav__mobile-link rounded-lg px-3 py-2 text-sm font-medium transition',
                        'public-nav__mobile-link--active' => $isActive,
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
@else
    <nav aria-label="Navegacion principal" class="public-nav public-nav--desktop flex w-full items-center justify-center gap-7 text-[13px]">
        @foreach ($items as $item)
            @php($children = collect($item['children'] ?? []))
            @php($isActive = request()->routeIs($item['route']) || $children->contains(fn (array $child): bool => request()->routeIs($child['route'])))

            @if ($children->isNotEmpty())
                <div class="public-nav__group group relative">
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        @class([
                            'public-nav__trigger inline-flex items-center gap-1 border-b-2 py-4 font-semibold tracking-[-0.01em] transition',
                            'public-nav__trigger--active' => $isActive,
                        ])
                    >
                        <span>{{ $item['label'] }}</span>
                        <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">expand_more</span>
                    </a>

                    <ul class="public-nav__dropdown pointer-events-none invisible absolute left-0 top-full z-50 min-w-[250px] overflow-hidden rounded-b-2xl border opacity-0 shadow-xl transition-all duration-200 group-hover:pointer-events-auto group-hover:visible group-hover:opacity-100 group-focus-within:pointer-events-auto group-focus-within:visible group-focus-within:opacity-100">
                        @foreach ($children as $child)
                            @php($childActive = request()->routeIs($child['route']))
                            <li>
                                <a
                                    href="{{ route($child['route']) }}"
                                    @if ($childActive) aria-current="page" @endif
                                    @class([
                                        'public-nav__dropdown-link flex items-center gap-3 px-4 py-3 text-[14px] transition',
                                        'public-nav__dropdown-link--active' => $childActive,
                                    ])
                                >
                                    <x-public.icon :icon="$child['icon'] ?? 'chevron_right'" class="!text-[19px]" />
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <a
                    href="{{ route($item['route']) }}"
                    @if ($isActive) aria-current="page" @endif
                    @class([
                        'public-nav__link border-b-2 py-4 font-semibold tracking-[-0.01em] transition',
                        'public-nav__link--active' => $isActive,
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
@endif
