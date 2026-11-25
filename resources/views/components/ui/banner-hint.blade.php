{{--
    Đường dẫn: resources/views/components/ui/banner-hint.blade.php
    Mô tả: Dải thông báo gợi ý màu hổ phách (hoặc mint) có nút hành động/liên kết.
    Props nhận vào:
      - $title (string, tùy chọn): Tiêu đề in đậm của gợi ý
      - $message (string, tùy chọn): Nội dung giải thích
      - $actionText (string, tùy chọn): Chữ hiển thị nút hành động
      - $actionUrl (string, tùy chọn): Đường dẫn đích của nút hành động
      - $icon (string, mặc định 'tips_and_updates')
      - $variant (string, mặc định 'amber')
--}}
@props([
    'title' => null,
    'message' => null,
    'actionText' => null,
    'actionUrl' => null,
    'icon' => 'tips_and_updates',
    'variant' => 'amber',
])

@php
    if ($variant === 'mint' || $variant === 'success') {
        $bannerClass = "bg-secondary-container/40 border-secondary-container text-on-secondary-container";
        $iconClass = "text-secondary";
        $linkClass = "text-secondary hover:text-emerald-950 decoration-secondary";
    } else {
        $bannerClass = "bg-amber-50 border-amber-200/70 text-amber-900";
        $iconClass = "text-amber-700";
        $linkClass = "text-amber-900 hover:text-amber-950 decoration-amber-400";
    }
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 rounded-xl border $bannerClass text-xs"]) }}>
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-[22px] shrink-0 {{ $iconClass }}">{{ $icon }}</span>
        <div class="leading-relaxed">
            @if($title)
                <strong class="font-bold mr-1">{{ $title }}:</strong>
            @endif
            <span>{{ $message ?? $slot }}</span>
        </div>
    </div>

    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}"
           class="inline-flex items-center gap-1 font-bold underline underline-offset-4 shrink-0 {{ $linkClass }} transition-colors">
            <span>{{ $actionText }}</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </a>
    @endif
</div>
