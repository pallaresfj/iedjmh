<x-filament-widgets::widget class="agro-widget">
    <section class="agro-surface">
        <header class="agro-surface__header">
            <h3 class="agro-surface__title">Ultimas Solicitudes PQRSF</h3>
        </header>

        <div class="agro-pqrs-list">
            @forelse ($items as $item)
                <article class="agro-pqrs-item {{ $item['stripe_class'] }}">
                    <div>
                        <p class="agro-pqrs-item__subject">{{ $item['subject'] }}</p>
                        <p class="agro-pqrs-item__meta">
                            {{ $item['submitted_at'] }} • {{ $item['applicant'] }}
                        </p>
                    </div>

                    <span class="{{ $item['status_badge_class'] }}">
                        {{ $item['status_label'] }}
                    </span>

                    @if (filled($item['record_url']))
                        <a href="{{ $item['record_url'] }}" class="agro-surface__action">
                            Revisar
                        </a>
                    @endif
                </article>
            @empty
                <p class="agro-empty-copy">No hay solicitudes PQRS registradas aun.</p>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
