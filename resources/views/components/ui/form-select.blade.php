{{--
    Đường dẫn: resources/views/components/ui/form-select.blade.php
    Mô tả: Component Select dùng chung trong các form thêm/sửa, đồng bộ với filter-select.
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'required' => false,
    'error' => null,
])

<div class="space-y-1.5 w-full">
    @if($label)
        <label @if($id) for="{{ $id }}" @endif class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
            {{ $label }} @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif
    <div class="relative w-full">
        <select name="{{ $name }}"
                @if($id) id="{{ $id }}" @endif
                @if($required) required @endif
                {{ $attributes->merge(['class' => 'form-select w-full h-11 !pl-3.5 !pr-10 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] cursor-pointer appearance-none [background-image:none] transition-all shadow-2xs']) }}>
            {{ $slot }}
        </select>
        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">
            expand_more
        </span>
    </div>
    @if($error)
        <p class="text-xs text-error font-medium">{{ $error }}</p>
    @endif
</div>
