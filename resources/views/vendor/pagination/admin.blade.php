@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Pagination">
        <p class="admin-pagination__info">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>
        <ul class="admin-pagination__list">
            @if ($paginator->onFirstPage())
                <li><span class="admin-pagination__btn is-disabled" aria-disabled="true">Prev</span></li>
            @else
                <li><a class="admin-pagination__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="admin-pagination__btn is-disabled">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="admin-pagination__btn is-current" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="admin-pagination__btn" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="admin-pagination__btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a></li>
            @else
                <li><span class="admin-pagination__btn is-disabled" aria-disabled="true">Next</span></li>
            @endif
        </ul>
    </nav>
@endif
