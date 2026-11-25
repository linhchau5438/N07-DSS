{{--
    Đường dẫn: resources/views/components/ui/status-pill.blade.php
    Mô tả: Huy hiệu (Pill/Badge) trạng thái chuẩn hóa, chữ 12px, chống gãy dòng (whitespace-nowrap).
--}}
@props([
    'variant' => 'neutral',
    'dot' => true,
    'icon' => null,
    'label' => null,
])

@php
    switch ($variant) {
        case 'success':
        case 'mint':
        case 'NORMAL':
        case 'fulfilled':
            $pillClass = "bg-secondary-container text-on-secondary-container border border-secondary-container";
            $dotColor = "bg-secondary";
            break;
        case 'warning':
        case 'amber':
        case 'REORDER':
        case 'pending':
            $pillClass = "bg-amber-100 text-amber-900 border border-amber-300/60";
            $dotColor = "bg-amber-600";
            break;
        case 'danger':
        case 'rose':
        case 'error':
        case 'rejected':
        case 'cancelled':
        case 'INVALID_DATA':
            $pillClass = "bg-error-container text-on-error-container border border-error-container";
            $dotColor = "bg-error";
            break;
        case 'info':
        case 'sky':
        case 'ordered':
        case 'approved':
            $pillClass = "bg-sky-100 text-sky-900 border border-sky-200";
            $dotColor = "bg-sky-600";
            break;
        case 'missing_config':
            $pillClass = "bg-amber-50 text-amber-800 border border-amber-200";
            $dotColor = "bg-amber-500";
            break;
        case 'violet':
            $pillClass = "bg-purple-100 text-purple-900 border border-purple-200";
            $dotColor = "bg-purple-600";
            break;
        case 'neutral':
        case 'slate':
        case 'INSUFFICIENT_DATA':
        default:
            $pillClass = "bg-surface-container-high text-on-surface-variant border border-surface-container-highest";
            $dotColor = "bg-slate-400";
            break;
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 h-7 px-3 rounded-full text-xs font-semibold whitespace-nowrap select-none $pillClass"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dotColor }}"></span>
    @elseif($icon)
        <span class="material-symbols-outlined text-[14px] shrink-0">{{ $icon }}</span>
    @endif
    <span>{{ $label ?? $slot }}</span>
</span>
