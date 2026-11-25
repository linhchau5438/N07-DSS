{{--
    Đường dẫn: resources/views/components/ui/filter-select.blade.php
    Mô tả: Dropdown chọn bộ lọc & chọn dữ liệu chuẩn dùng chung cho toàn bộ dự án.
    Quy tắc:
      - Chiều cao h-11, bo góc rounded-xl, nền trắng bg-white kèm viền border-surface-container-high.
      - Chống lỗi 2 mũi tên: class [background-image:none] !bg-none appearance-none triệt để.
      - Chỉ hiển thị duy nhất 1 icon expand_more tự vẽ ở absolute right-3 pointer-events-none.
      - Padding phải đủ !pr-10 chống đè chữ, chữ 14px, focus ring xanh rừng #1A4D38.
--}}
@props([
    'name',
    'id' => null,
    'autoSubmit' => true,
    'width' => 'w-full sm:w-52',
    'required' => false,
])

<div {{ $attributes->merge(['class' => "relative shrink-0 $width"]) }}>
    <select name="{{ $name }}"
            @if($id) id="{{ $id }}" @endif
            @if($required) required @endif
            @if($autoSubmit) onchange="this.form ? this.form.submit() : null" @endif
            class="filter-select w-full h-11 !pl-3.5 !pr-10 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] cursor-pointer appearance-none [background-image:none] transition-all shadow-2xs">
        {{ $slot }}
    </select>
    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">
        expand_more
    </span>
</div>
