{{--
    Đường dẫn: resources/views/components/ui/filter-date.blade.php
    Mô tả: Ô chọn ngày (Date input) chuẩn hóa trong Toolbar bộ lọc, cao h-11, bo góc rounded-xl, đồng bộ với search-input và filter-select.
--}}
@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'width' => 'w-full sm:w-44',
    'autoSubmit' => false,
    'required' => false,
])

<div {{ $attributes->merge(['class' => "relative shrink-0 $width"]) }}>
    <input type="date"
           name="{{ $name }}"
           @if($id) id="{{ $id }}" @endif
           value="{{ $value }}"
           @if($placeholder) placeholder="{{ $placeholder }}" @endif
           @if($required) required @endif
           @if($autoSubmit) onchange="this.form ? this.form.submit() : null" @endif
           class="w-full h-11 px-3.5 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] transition-all shadow-2xs cursor-pointer" />
</div>
