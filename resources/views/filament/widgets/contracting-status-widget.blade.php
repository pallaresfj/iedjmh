<x-filament-widgets::widget class="agro-widget">
    <section class="agro-contract-card">
        <div class="agro-contract-card__content">
            <div class="agro-contract-card__head">
                <div class="agro-contract-card__head-main">
                    <span class="agro-contract-card__icon">
                        <x-filament::icon icon="heroicon-o-document-check" />
                    </span>
                    <h3>Estado de Contratacion</h3>
                </div>

                @if (filled($status['create_url']))
                    <a href="{{ $status['create_url'] }}" class="agro-round-add" aria-label="Crear proceso contractual">
                        <x-filament::icon icon="heroicon-o-plus" />
                    </a>
                @endif
            </div>

            <p class="agro-contract-card__subtitle">Vigencia {{ $status['year'] }}</p>

            <div class="agro-contract-card__progress-copy">
                <span>Avance anual</span>
                <span>{{ $status['progress'] }}%</span>
            </div>

            <div class="agro-contract-card__progress">
                <span style="width: {{ $status['progress'] }}%"></span>
            </div>

            <div class="agro-contract-card__stats">
                <article>
                    <p class="agro-contract-card__label">En curso</p>
                    <p class="agro-contract-card__value">{{ $status['in_progress'] }}</p>
                </article>

                <article>
                    <p class="agro-contract-card__label">Adjudicados</p>
                    <p class="agro-contract-card__value">{{ $status['awarded'] }}</p>
                </article>

                <article>
                    <p class="agro-contract-card__label">Finalizados</p>
                    <p class="agro-contract-card__value">{{ $status['finalized'] }}</p>
                </article>
            </div>
        </div>

        <footer class="agro-contract-card__footer">
            <span>Total vigencia: {{ $status['total'] }}</span>

            @if (filled($status['index_url']))
                <a href="{{ $status['index_url'] }}">Ver procesos</a>
            @endif
        </footer>
    </section>
</x-filament-widgets::widget>
