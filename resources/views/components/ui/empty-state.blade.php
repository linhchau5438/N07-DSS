{{--
    Đường dẫn: resources/views/components/ui/empty-state.blade.php
    Mô tả: Khung hiển thị khi không tìm thấy dữ liệu hoặc danh sách rỗng.
    Props nhận vào:
      - $icon (string, mặc định 'inbox'): Tên Material Symbol
      - $title (string, bắt buộc): Tiêu đề thông báo
      - $message (string, tùy chọn): Lời giải thích hoặc hướng dẫn
      - $actionText (string, tùy chọn): Chữ trên nút hành động
      - $actionUrl (string, tùy chọn): Link nút hành động
      - $actionIcon (string, tùy chọn)
--}}
@props([
    'icon' => 'inbox',
    'title' => 'Không tìm thấy dữ liệu',
    'message' => null,
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => null,
])

<div {{ $attributes->merge(['class' => 'py-16 text-center flex flex-col items-center justify-center w-full']) }}>
    <div class="w-14 h-14 rounded-2xl bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-3 shadow-xs">
        <span class="material-symbols-outlined text-[32px]">{{ $icon }}</span>
    </div>
    <h3 class="font-headline-sm text-base font-bold text-on-surface">{{ $title }}</h3>
    @if($message || !empty($slot))
        <p class="font-body-md text-xs text-on-surface-variant max-w-sm mt-1 mb-4 leading-relaxed">
            {{ $message ?? $slot }}
        </p>
    @endif
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}"
           class="h-9 px-4 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-xs font-semibold transition-colors inline-flex items-center gap-1.5 border border-surface-container-high/60 shadow-xs">
            @if($actionIcon)
                <span class="material-symbols-outlined text-[16px]">{{ $actionIcon }}</span>
            @endif
            <span>{{ $actionText }}</span>
        </a>
    @endif
</div>
