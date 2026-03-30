<x-filament-widgets::widget class="agro-widget">
    <section class="agro-contract-card">
        <div class="agro-contract-card__content">
            <div class="agro-contract-card__head">
                <div class="agro-contract-card__head-main">
                    <span class="agro-contract-card__icon">
                        <x-filament::icon icon="heroicon-o-academic-cap" />
                    </span>
                    <h3>Egresados</h3>
                </div>
            </div>

            <p class="agro-contract-card__subtitle">Estado de registros</p>

            <div class="agro-contract-card__progress-copy">
                <span>Verificacion</span>
                <span>{{ $status['verification_progress'] }}%</span>
            </div>

            <div class="agro-contract-card__progress">
                <span style="width: {{ $status['verification_progress'] }}%"></span>
            </div>

            <div class="agro-contract-card__stats">
                <article>
                    <p class="agro-contract-card__label">Activos</p>
                    <p class="agro-contract-card__value">{{ $status['active'] }}</p>
                </article>

                <article>
                    <p class="agro-contract-card__label">Precargados</p>
                    <p class="agro-contract-card__value">{{ $status['preloaded'] }}</p>
                </article>

                <article>
                    <p class="agro-contract-card__label">Bloqueados</p>
                    <p class="agro-contract-card__value">{{ $status['blocked'] }}</p>
                </article>
            </div>
        </div>

        <footer class="agro-contract-card__footer">
            <span>Total registrados: {{ $status['total'] }}</span>

            @if (filled($status['index_url']))
                <a href="{{ $status['index_url'] }}">Ver egresados</a>
            @endif
        </footer>
    </section>
</x-filament-widgets::widget>
