{{--
    Đường dẫn: resources/views/components/ui/view-toggle.blade.php
    Mô tả: Nút chuyển đổi chế độ xem Bảng (table) hoặc Lưới (grid) chuẩn hóa.
--}}
@props([
    'current' => 'table',
])

<div {{ $attributes->merge(['class' => 'flex items-center h-11 p-1 rounded-xl bg-surface-container-low border border-surface-container-high/60 shrink-0 select-none']) }}>
    <button type="button" id="btnViewTable" onclick="switchViewMode('table')"
            class="h-9 px-3 rounded-lg flex items-center gap-1.5 transition-all text-xs font-semibold cursor-pointer {{ $current === 'table' ? 'bg-white text-on-surface shadow-2xs font-bold' : 'text-on-surface-variant hover:text-on-surface' }}"
            title="Chế độ xem Bảng">
        <span class="material-symbols-outlined text-[19px]">table_rows</span>
        <span class="hidden sm:inline">Bảng</span>
    </button>
    <button type="button" id="btnViewGrid" onclick="switchViewMode('grid')"
            class="h-9 px-3 rounded-lg flex items-center gap-1.5 transition-all text-xs font-semibold cursor-pointer {{ $current === 'grid' ? 'bg-white text-on-surface shadow-2xs font-bold' : 'text-on-surface-variant hover:text-on-surface' }}"
            title="Chế độ xem Lưới">
        <span class="material-symbols-outlined text-[19px]">grid_view</span>
        <span class="hidden sm:inline">Lưới</span>
    </button>
</div>
