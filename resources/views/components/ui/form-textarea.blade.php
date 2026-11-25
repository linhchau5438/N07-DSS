{{--
    Đường dẫn: resources/views/components/ui/form-textarea.blade.php
    Mô tả: Ô nhập văn bản nhiều dòng dùng chung trong form.
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'rows' => 3,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
            {{ $label }} @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif

    <textarea id="{{ $inputId }}"
              name="{{ $name }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              @if($required) required @endif
              {{ $attributes->merge(['class' => 'w-full p-3.5 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] transition-all shadow-2xs']) }}>{{ old($name, $value) }}</textarea>

    @if($error || $errors->has($name))
        <p class="text-xs text-error font-medium mt-1">{{ $error ?? $errors->first($name) }}</p>
    @endif
</div>
