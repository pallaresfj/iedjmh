<x-filament-widgets::widget class="agro-widget">
    <section class="agro-surface agro-events-widget">
        <header class="agro-surface__header">
            <h3 class="agro-surface__title">Proximos Eventos</h3>

            @if (filled($createUrl))
                <a href="{{ $createUrl }}" class="agro-round-add" aria-label="Crear evento">
                    <x-filament::icon icon="heroicon-o-plus" />
                </a>
            @endif
        </header>

        <div class="agro-events-list">
            @forelse ($events as $event)
                <article class="agro-event-item">
                    <div class="agro-event-item__date">
                        <span>{{ $event['month'] }}</span>
                        <strong>{{ $event['day'] }}</strong>
                    </div>

                    <div class="agro-event-item__body">
                        <p class="agro-event-item__title">{{ $event['title'] }}</p>
                        <p class="agro-event-item__meta">{{ $event['details'] }}</p>
                    </div>
                </article>
            @empty
                <p class="agro-empty-copy">No hay eventos futuros publicados.</p>
            @endforelse
        </div>

        @if (filled($indexUrl))
            <a href="{{ $indexUrl }}" class="agro-outline-btn">
                Ver calendario completo
            </a>
        @endif
    </section>
</x-filament-widgets::widget>
