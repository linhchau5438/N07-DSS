{{--
    Đường dẫn: resources/views/components/ui/toolbar.blade.php
    Mô tả: Khung công cụ Tìm kiếm & Bộ lọc duy nhất dùng chung cho toàn bộ dự án.
    Quy tắc:
      - Một hàng duy nhất trên desktop (flex-row items-center gap-3), cao h-11 mọi phần tử.
      - Thứ tự cố định: [search-input (flex-1)] -> [các filter-select inline] -> [nút Đặt lại ghost] -> [view-toggle] -> [filter-button].
      - Tuyệt đối không còn dải xám phụ bên dưới, không còn chữ "Tự động lọc khi chọn mục".
--}}
@props([
    'action' => null,
    'method' => 'GET',
    'resetUrl' => null,
    'hasFilter' => false,
])

<form @if($action) action="{{ $action }}" @endif method="{{ $method }}" class="p-0 w-full">
    <div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row items-stretch lg:items-center gap-3 w-full']) }}>
        {{-- Slot chứa ô tìm kiếm, các filter-select, view-toggle, filter-button --}}
        {{ $slot }}

        {{-- Nút Đặt lại bộ lọc (Ghost button chỉ hiện khi đang có bộ lọc hoạt động) --}}
        @if($hasFilter && $resetUrl)
            <a href="{{ $resetUrl }}"
               class="h-11 px-3.5 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low text-xs font-semibold inline-flex items-center gap-1.5 transition-colors border border-surface-container-high/60 shrink-0 whitespace-nowrap select-none"
               title="Đặt lại toàn bộ bộ lọc">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span>Đặt lại</span>
            </a>
        @endif
    </div>
</form>
