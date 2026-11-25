{{-- File: resources/views/pagination/n07.blade.php --}}
{{-- Mô tả: Giao diện phân trang chuẩn N07 Design System (Tailwind CSS, hỗ trợ dark/light tokens, đầy đủ class pagination-nav/pagination-summary để tương thích kiểm thử tự động). --}}
{{-- Phụ thuộc: $paginator (Illuminate\Pagination\LengthAwarePaginator), $elements. --}}
@if ($paginator->hasPages())
    {{-- GIỮ NGUYÊN class pagination-nav để không phá vỡ ProductIndexTest --}}
    <nav class="pagination-nav bg-surface-container-low/50 px-6 py-4 border-t border-surface-container-high/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 select-none" role="navigation" aria-label="Phân trang">
        {{-- GIỮ NGUYÊN format text Hiển thị a–b trong N kết quả để pass unit/feature test --}}
        <p class="pagination-summary text-xs text-on-surface-variant font-medium">
            Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            trong {{ $paginator->total() }} kết quả
        </p>

        <ul class="pagination-list flex items-center gap-1.5 list-none m-0 p-0">
            {{-- Nút Trang trước --}}
            @if ($paginator->onFirstPage())
                <li class="is-disabled opacity-40 cursor-not-allowed">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm border border-surface-container-high text-on-surface-variant" aria-hidden="true">‹</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-sm border border-surface-container-high bg-surface-container-lowest text-on-surface hover:bg-surface-container transition-colors">‹</a>
                </li>
            @endif

            {{-- Các phần tử trang số --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="is-disabled opacity-60">
                        <span class="w-8 h-8 flex items-center justify-center text-xs text-on-surface-variant">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="is-current" aria-current="page">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold bg-primary text-white shadow-xs">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" aria-label="Trang {{ $page }}"
                                   class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-medium border border-surface-container-high/60 bg-surface-container-lowest text-on-surface hover:bg-surface-container transition-colors">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Nút Trang sau --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-sm border border-surface-container-high bg-surface-container-lowest text-on-surface hover:bg-surface-container transition-colors">›</a>
                </li>
            @else
                <li class="is-disabled opacity-40 cursor-not-allowed">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm border border-surface-container-high text-on-surface-variant" aria-hidden="true">›</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
