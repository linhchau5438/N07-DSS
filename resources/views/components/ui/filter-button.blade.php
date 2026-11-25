{{--
    Đường dẫn: resources/views/components/ui/filter-button.blade.php
    Mô tả: Nút bấm "Bộ lọc" chuẩn hóa màu xanh rừng #1A4D38 chữ trắng, tích hợp badge số lượng bộ lọc đang hoạt động.
--}}
@props([
    'count' => 0,
    'target' => 'filter-panel',
    'label' => 'Bộ lọc',
])

<button type="button"
        onclick="toggleFilterPanel('{{ $target }}')"
        {{ $attributes->merge(['class' => 'h-11 px-4 rounded-xl bg-[#1A4D38] text-white hover:bg-[#105941] transition-all duration-200 text-xs font-semibold inline-flex items-center gap-2 shadow-2xs shrink-0 select-none cursor-pointer active:scale-[0.98]']) }}>
    <span class="material-symbols-outlined text-[18px]">tune</span>
    <span>{{ $label }}</span>
    @if((int) $count > 0)
        <span class="w-5 h-5 rounded-full bg-white text-[#1A4D38] text-[11px] font-bold flex items-center justify-center shrink-0">
            {{ $count }}
        </span>
    @endif
</button>
