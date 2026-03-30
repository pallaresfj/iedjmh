<x-filament-widgets::widget class="agro-widget">
    @php($isKpiMosaicLayout = count($kpis) === 5)

    <div @class(['agro-kpi-grid', 'agro-kpi-grid--mosaic' => $isKpiMosaicLayout])>
        @foreach ($kpis as $kpi)
            <article @class([
                'agro-kpi-card',
                'agro-kpi-card--highlight' => $kpi['highlight'] ?? false,
                'agro-kpi-card--wide' => $isKpiMosaicLayout && $loop->index >= 3,
            ])>
                <div class="agro-kpi-card__top">
                    <span class="agro-kpi-card__icon">
                        <x-filament::icon :icon="$kpi['icon']" />
                    </span>

                    <span class="{{ $kpi['badge_class'] }}">
                        {{ $kpi['label'] }}
                    </span>
                </div>

                <p class="agro-kpi-card__value">{{ $kpi['value'] }}</p>
                <p class="agro-kpi-card__label">{{ $kpi['description'] }}</p>

                @if (filled($kpi['url']))
                    <a href="{{ $kpi['url'] }}" class="agro-kpi-card__link">
                        Ver modulo
                    </a>
                @endif
            </article>
        @endforeach
    </div>
</x-filament-widgets::widget>
