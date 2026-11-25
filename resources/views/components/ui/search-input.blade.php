{{--
    Đường dẫn: resources/views/components/ui/search-input.blade.php
    Mô tả: Ô tìm kiếm dùng chung cho toàn bộ dự án (Top bar, Sản phẩm, Tồn kho, Lịch sử, Danh mục, Nhân sự, Báo cáo).
    Quy tắc:
      - Chiều cao chuẩn h-11, bo góc rounded-xl, nền trắng bg-white kèm viền border-surface-container-high.
      - Icon kính lúp absolute left-3.5 pointer-events-none text-[20px].
      - Padding !pl-11 !pr-14 chống đè icon và chữ.
      - Phím tắt ⌘K nhỏ bên phải, placeholder 14-15px, focus ring xanh rừng #1A4D38.
--}}
@props([
    'id' => 'search-input',
    'name' => 'search',
    'placeholder' => 'Tìm kiếm sản phẩm, số phiếu, mã SKU...',
    'value' => '',
    'shortcut' => '⌘K',
    'autoSubmit' => false,
])

<div {{ $attributes->merge(['class' => 'relative flex-1 min-w-[220px]']) }}>
    {{-- Icon kính lúp pointer-events-none --}}
    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none">
        search
    </span>

    {{-- Ô input chuẩn hóa UI 100% --}}
    <input type="text"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           @if($autoSubmit) data-auto-submit="true" @endif
           class="w-full h-11 !pl-11 !pr-14 rounded-xl bg-white text-on-surface placeholder:text-on-surface-variant/70 text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] transition-all shadow-2xs" />

    {{-- Phím tắt hiển thị bên phải --}}
    @if($shortcut)
        <kbd class="absolute right-3.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded text-[10px] font-mono bg-surface-container-high/80 text-on-surface-variant pointer-events-none select-none">
            {{ $shortcut }}
        </kbd>
    @endif
</div>
