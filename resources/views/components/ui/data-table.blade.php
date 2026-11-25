{{--
    Đường dẫn: resources/views/components/ui/data-table.blade.php
    Mô tả: Khung bảng chuẩn dùng chung cho tất cả trang danh sách dữ liệu trong hệ thống.
    Quy tắc:
      - Nằm trực tiếp trong section-card, bọc bởi 1 khung rounded-2xl viền border-surface-container-high/60.
      - Chứa div overflow-x-auto với thanh cuộn ngang custom-scrollbar.
      - Header: nền surface-container-low, cao h-12, chữ 12px uppercase tracking-wider font-semibold.
      - Body: text-[15px] leading-6 divide-y divide-surface-container-high/50 text-on-surface.
      - Chiều cao hàng: min h-[72px] (py-5), hover:bg-surface-container-low/70.
      - Khung chân gắn liền cho phân trang / footer.
--}}
@props([
    'minWidth' => 'min-w-[1050px]',
])

<div class="border border-surface-container-high/60 rounded-2xl overflow-hidden bg-surface-container-lowest shadow-sm">
    <div class="overflow-x-auto custom-scrollbar w-full">
        <table class="w-full text-left border-collapse {{ $minWidth }}">
            <thead>
                <tr class="bg-surface-container-low border-b border-surface-container-high/70 text-on-surface-variant text-xs font-semibold uppercase tracking-wider select-none h-12 sticky top-0 z-10 whitespace-nowrap">
                    @if(isset($header))
                        {{ $header }}
                    @elseif(isset($head))
                        {{ $head }}
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-high/50 text-[15px] leading-6 text-on-surface">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($empty) && !empty(trim($empty)))
        <div class="border-t border-surface-container-high/50">
            {{ $empty }}
        </div>
    @endif

    @if(isset($pagination) && !empty(trim($pagination)))
        <div class="border-t border-surface-container-high/50 px-6 py-4 bg-surface-container-lowest">
            {{ $pagination }}
        </div>
    @elseif(isset($footer) && !empty(trim($footer)))
        <div class="border-t border-surface-container-high/50 px-6 py-4 bg-surface-container-lowest">
            {{ $footer }}
        </div>
    @endif
</div>
