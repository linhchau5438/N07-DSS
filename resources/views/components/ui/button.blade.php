{{--
    Đường dẫn: resources/views/components/ui/button.blade.php
    Mô tả: Nút bấm chuẩn hóa dùng chung toàn bộ dự án.
    Biến thể:
      - primary (hoặc default, black): Nền đen bg-primary chữ + icon trắng, hover:bg-neutral-800.
      - secondary: Nền xám surface-container-low, viền border-surface-container-high/60, chữ + icon on-surface.
      - ghost: Trong suốt, hover nền xám nhạt, viền border-surface-container-high/60.
      - danger: Nền error/đỏ, chữ trắng.
      - filter: Nền xanh rừng #1A4D38, chữ trắng (dành riêng cho nút Bộ lọc).
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'h-9 px-3 text-xs',
        'lg' => 'h-12 px-6 text-sm',
        default => 'h-11 px-4 text-xs font-semibold',
    };

    $variantClasses = match($variant) {
        'primary', 'black' => 'bg-primary text-white hover:bg-neutral-800 shadow-2xs active:scale-[0.98]',
        'secondary' => 'bg-surface-container-low text-on-surface hover:bg-surface-container border border-surface-container-high/60 shadow-2xs active:scale-[0.98]',
        'ghost' => 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low border border-surface-container-high/60',
        'danger' => 'bg-error text-white hover:bg-red-700 shadow-2xs active:scale-[0.98]',
        'filter' => 'bg-[#1A4D38] text-white hover:bg-[#105941] shadow-2xs active:scale-[0.98]',
        default => 'bg-primary text-white hover:bg-neutral-800 shadow-2xs active:scale-[0.98]',
    };

    $classes = "rounded-xl inline-flex items-center justify-center gap-2 transition-all duration-200 select-none cursor-pointer whitespace-nowrap $sizeClasses $variantClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="material-symbols-outlined text-[18px] shrink-0 text-inherit">{{ $icon }}</span>
        @endif
        <span class="text-inherit">{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="material-symbols-outlined text-[18px] shrink-0 text-inherit">{{ $icon }}</span>
        @endif
        <span class="text-inherit">{{ $slot }}</span>
    </button>
@endif
