<x-filament-widgets::widget class="agro-widget">
    <section class="agro-moderation-alert">
        <header class="agro-moderation-alert__header">
            <div class="agro-moderation-alert__title-wrap">
                <span class="agro-moderation-alert__icon">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" />
                </span>

                <div>
                    <h3 class="agro-moderation-alert__title">Moderacion de noticias</h3>
                    <p class="agro-moderation-alert__copy">
                        Noticias en borrador creadas por colaboradores pendientes de revision.
                    </p>
                </div>
            </div>

            <span class="agro-moderation-alert__counter">
                {{ $moderation['count'] }} pendiente(s)
            </span>
        </header>

        @if ($moderation['count'] > 0)
            <div class="agro-moderation-alert__list">
                @foreach ($moderation['items'] as $item)
                    <article class="agro-moderation-item">
                        <div class="agro-moderation-item__body">
                            <p class="agro-moderation-item__title">{{ $item['title'] }}</p>
                            <p class="agro-moderation-item__meta">
                                {{ $item['author'] }} · {{ $item['created_at'] }}
                            </p>
                        </div>

                        @if (filled($item['edit_url']))
                            <a href="{{ $item['edit_url'] }}" class="agro-moderation-item__action">
                                Revisar
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="agro-moderation-alert__empty">
                No hay noticias pendientes de moderacion en este momento.
            </p>
        @endif

        @if (filled($moderation['index_url']))
            <footer class="agro-moderation-alert__footer">
                <a href="{{ $moderation['index_url'] }}">
                    Ir al modulo de noticias
                </a>
            </footer>
        @endif
    </section>
</x-filament-widgets::widget>

