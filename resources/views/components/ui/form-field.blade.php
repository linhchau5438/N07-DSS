{{--
    Đường dẫn: resources/views/components/ui/form-field.blade.php
    Mô tả: Khung trường nhập liệu chuẩn hóa (Label + Input + Error validation).
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'disabled' => false,
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

    <div class="relative w-full">
        @if($type === 'textarea')
            <textarea id="{{ $inputId }}"
                      name="{{ $name }}"
                      placeholder="{{ $placeholder }}"
                      @if($required) required @endif
                      @if($disabled) disabled @endif
                      rows="3"
                      {{ $attributes->merge(['class' => 'w-full p-3.5 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] transition-all shadow-2xs']) }}>{{ old($name, $value) }}</textarea>
        @else
            <input type="{{ $type }}"
                   id="{{ $inputId }}"
                   name="{{ $name }}"
                   value="{{ old($name, $value) }}"
                   placeholder="{{ $placeholder }}"
                   @if($required) required @endif
                   @if($disabled) disabled @endif
                   {{ $attributes->merge(['class' => 'w-full h-11 px-3.5 rounded-xl bg-white text-on-surface text-[14px] font-medium border border-surface-container-high focus:outline-none focus:ring-1 focus:ring-[#1A4D38] focus:border-[#1A4D38] transition-all shadow-2xs']) }} />
        @endif
    </div>

    @if($error || $errors->has($name))
        <p class="text-xs text-error font-medium mt-1">{{ $error ?? $errors->first($name) }}</p>
    @endif
</div>
