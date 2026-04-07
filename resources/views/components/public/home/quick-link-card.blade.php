@props([
    'title',
    'description' => null,
    'url' => '#',
    'icon' => 'link',
    'tone' => 'default',
])

<a
    href="{{ $url }}"
    @class([
        'group block rounded-2xl p-6 transition',
        'border border-slate-100 bg-white shadow-xl shadow-slate-200/40 hover:-translate-y-1 hover:shadow-2xl hover:shadow-slate-200/60' => $tone !== 'home',
        'public-home-quick-link border' => $tone === 'home',
    ])
>
    <span @class([
        'mb-4 inline-flex size-11 items-center justify-center rounded-xl transition',
        'bg-ied-primary/10 text-ied-primary group-hover:bg-ied-primary group-hover:text-white' => $tone !== 'home',
        'public-home-quick-link__icon' => $tone === 'home',
    ])>
        <x-public.icon :icon="$icon" class="!text-[24px]" />
    </span>

    <div>
        <p @class([
            'public-heading text-base font-bold tracking-[-0.01em] sm:text-[17px]',
            'text-slate-900' => $tone !== 'home',
            'public-home-quick-link__title' => $tone === 'home',
        ])>{{ $title }}</p>
        @if ($description)
            <p @class([
                'mt-2 text-xs leading-relaxed sm:text-[13px]',
                'text-slate-500' => $tone !== 'home',
                'public-home-quick-link__description' => $tone === 'home',
            ])>{{ $description }}</p>
        @endif
    </div>
</a>
