<x-filament-widgets::widget class="agro-widget">
    <section class="agro-surface">
        <header class="agro-surface__header">
            <h3 class="agro-surface__title">Gestion de Noticias Recientes</h3>

            <div class="agro-surface__actions">
                @if (filled($createUrl))
                    <a href="{{ $createUrl }}" class="agro-round-add" aria-label="Crear noticia">
                        <x-filament::icon icon="heroicon-o-plus" />
                    </a>
                @endif

                @if (filled($indexUrl))
                    <a href="{{ $indexUrl }}" class="agro-surface__action">
                        Ver todas
                    </a>
                @endif
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="agro-table">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Fecha</th>
                        <th>Autor</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <div class="agro-table-title">
                                    <span class="agro-table-thumb">
                                        <x-filament::icon icon="heroicon-o-newspaper" />
                                    </span>

                                    <div>
                                        <p class="agro-table-title__text">{{ $item['title'] }}</p>
                                        <p class="{{ $item['status_class'] }}">{{ $item['status_label'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['author'] }}</td>
                            <td class="text-right">
                                @if (filled($item['edit_url']))
                                    <a href="{{ $item['edit_url'] }}" class="agro-icon-link" aria-label="Editar noticia">
                                        <x-filament::icon icon="heroicon-o-pencil-square" />
                                    </a>
                                @else
                                    <span class="agro-icon-link agro-icon-link--disabled" aria-hidden="true">
                                        <x-filament::icon icon="heroicon-o-pencil-square" />
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="agro-empty-row">
                                Aun no hay noticias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-filament-widgets::widget>
