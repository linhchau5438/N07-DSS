{{--
    Đường dẫn: resources/views/components/ui/flash-alert.blade.php
    Mô tả: Thông báo Flash (Thành công / Lỗi / Cảnh báo) màu pastel nhẹ nhàng.
    Props nhận vào:
      - $type (string, mặc định 'success'): 'success' | 'error' | 'warning' | 'info'
      - $message (string, tùy chọn): Nội dung thông báo
--}}
@props([
    'type' => 'success',
    'message' => null,
])

@php
    switch ($type) {
        case 'error':
        case 'danger':
            $alertClass = "bg-rose-50 border border-rose-200 text-rose-800";
            $icon = "error";
            $iconColor = "text-rose-600";
            break;
        case 'warning':
            $alertClass = "bg-amber-50 border border-amber-200 text-amber-800";
            $icon = "warning";
            $iconColor = "text-amber-600";
            break;
        case 'info':
            $alertClass = "bg-sky-50 border border-sky-200 text-sky-800";
            $icon = "info";
            $iconColor = "text-sky-600";
            break;
        case 'success':
        default:
            $alertClass = "bg-emerald-50 border border-emerald-200 text-emerald-800";
            $icon = "check_circle";
            $iconColor = "text-emerald-600";
            break;
    }
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-xl text-sm flex items-center gap-2.5 shadow-xs $alertClass"]) }} role="alert">
    <span class="material-symbols-outlined text-[20px] shrink-0 {{ $iconColor }}">{{ $icon }}</span>
    <div class="flex-1 leading-normal">
        {{ $message ?? $slot }}
    </div>
</div>
