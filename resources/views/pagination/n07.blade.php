@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Phân trang">
        <p class="pagination-summary">
            Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            trong {{ $paginator->total() }} kết quả
        </p>

        <ul class="pagination-list">
            @if ($paginator->onFirstPage())
                <li class="is-disabled"><span aria-hidden="true">‹</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="is-disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="is-current" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">›</a></li>
            @else
                <li class="is-disabled"><span aria-hidden="true">›</span></li>
            @endif
        </ul>
    </nav>
@endif
