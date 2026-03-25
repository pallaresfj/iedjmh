@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="public-pagination">
        <ul class="public-pagination__list">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <svg class="public-pagination__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            @else
                <li>
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        data-public-pagination-link
                        class="public-pagination__item"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <svg class="public-pagination__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="public-pagination__item public-pagination__item--disabled" aria-disabled="true">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <li>
                                <span class="public-pagination__item public-pagination__item--active" aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a
                                    href="{{ $url }}"
                                    data-public-pagination-link
                                    class="public-pagination__item"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                >
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        data-public-pagination-link
                        class="public-pagination__item"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <svg class="public-pagination__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
            @else
                <li>
                    <span class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <svg class="public-pagination__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
