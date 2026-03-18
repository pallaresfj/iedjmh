@props([
    'items' => [],
    'mobile' => false,
])

@if ($mobile)
    <nav aria-label="Navegacion principal" class="grid gap-2">
        @foreach ($items as $item)
            @php($children = collect($item['children'] ?? []))
            @php($isActive = request()->routeIs($item['route']) || $children->contains(fn (array $child): bool => request()->routeIs($child['route'])))

            @if ($children->isNotEmpty())
                <details @if($isActive) open @endif class="group rounded-xl border border-slate-200 bg-white p-2">
                    <summary class="flex list-none items-center justify-between rounded-lg px-2 py-2 text-sm font-semibold text-slate-700">
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
                                    'flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition',
                                    'bg-ied-primary text-white' => $childActive,
                                    'text-slate-600 hover:bg-ied-primary/10 hover:text-ied-primary-dark' => ! $childActive,
                                ])
                            >
                                <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">{{ $child['icon'] ?? 'chevron_right' }}</span>
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
                        'rounded-lg px-3 py-2 text-sm font-medium transition',
                        'bg-ied-primary text-white' => $isActive,
                        'text-slate-700 hover:bg-slate-100 hover:text-ied-primary-dark' => ! $isActive,
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
@else
    <nav aria-label="Navegacion principal" class="flex w-full items-center justify-center gap-7 text-[13px]">
        @foreach ($items as $item)
            @php($children = collect($item['children'] ?? []))
            @php($isActive = request()->routeIs($item['route']) || $children->contains(fn (array $child): bool => request()->routeIs($child['route'])))

            @if ($children->isNotEmpty())
                <div class="group relative">
                    <a
                        href="{{ route($item['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        @class([
                            'inline-flex items-center gap-1 border-b-2 py-4 font-semibold tracking-[-0.01em] transition',
                            'border-ied-primary text-ied-primary-dark' => $isActive,
                            'border-transparent text-slate-700 group-hover:border-ied-primary group-hover:text-ied-primary-dark' => ! $isActive,
                        ])
                    >
                        <span>{{ $item['label'] }}</span>
                        <span class="material-symbols-outlined !text-[18px]" aria-hidden="true">expand_more</span>
                    </a>

                    <ul class="pointer-events-none invisible absolute left-0 top-full z-50 min-w-[250px] overflow-hidden rounded-b-2xl border border-slate-100 bg-white opacity-0 shadow-xl transition-all duration-200 group-hover:pointer-events-auto group-hover:visible group-hover:opacity-100 group-focus-within:pointer-events-auto group-focus-within:visible group-focus-within:opacity-100">
                        @foreach ($children as $child)
                            @php($childActive = request()->routeIs($child['route']))
                            <li>
                                <a
                                    href="{{ route($child['route']) }}"
                                    @if ($childActive) aria-current="page" @endif
                                    @class([
                                        'flex items-center gap-3 px-4 py-3 text-[14px] transition',
                                        'bg-ied-primary/10 text-ied-primary-dark' => $childActive,
                                        'text-slate-600 hover:bg-ied-primary/10 hover:text-ied-primary-dark' => ! $childActive,
                                    ])
                                >
                                    <span class="material-symbols-outlined !text-[19px]" aria-hidden="true">{{ $child['icon'] ?? 'chevron_right' }}</span>
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
                        'border-b-2 py-4 font-semibold tracking-[-0.01em] transition',
                        'border-ied-primary text-ied-primary-dark' => $isActive,
                        'border-transparent text-slate-700 hover:border-ied-primary/60 hover:text-ied-primary-dark' => ! $isActive,
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
@endif
