{{--
    Đường dẫn: resources/views/components/ui/filter-bar.blade.php
    Mô tả: Thanh công cụ tìm kiếm và bộ lọc đa chiều dùng chung cho mọi bảng.
    Props nhận vào:
      - $action (string, tùy chọn): Action của form GET
      - $method (string, mặc định 'GET')
      - $resetUrl (string, tùy chọn): Đường dẫn xóa lọc để quay về trạng thái mặc định
      - $hasFilter (bool, mặc định false): Đang có bộ lọc hoạt động hay không
--}}
@props([
    'action' => null,
    'method' => 'GET',
    'resetUrl' => null,
    'hasFilter' => false,
])

<form @if($action) action="{{ $action }}" @endif method="{{ $method }}" class="m-0 p-0 w-full">
    <div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row items-stretch lg:items-center gap-3 w-full']) }}>
        {{-- Slot chứa ô tìm kiếm và các dropdown lọc --}}
        {{ $slot }}

        {{-- Nút Đặt lại bộ lọc (Ghost button không bị gãy dòng) --}}
        @if($hasFilter && $resetUrl)
            <a href="{{ $resetUrl }}"
               class="h-11 px-4 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low text-xs font-semibold inline-flex items-center gap-1.5 transition-colors border border-surface-container-high/60 shrink-0 whitespace-nowrap"
               title="Đặt lại toàn bộ bộ lọc">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span class="whitespace-nowrap">Đặt lại</span>
            </a>
        @endif
    </div>
</form>
