{{--
    Đường dẫn: resources/views/components/ui/page-header.blade.php
    Mô tả: Component Khung 1 Header chuẩn của mọi trang (Card trắng rounded-2xl, p-6 md:p-8).
    Props nhận vào:
      - $title (string, bắt buộc): Tiêu đề trang (luôn cố định cỡ text-headline-lg / 24px, bold)
      - $chip (string, tùy chọn): Nhãn chip kho hoặc trạng thái bên cạnh tiêu đề
      - $subtitle (string, tùy chọn): Dòng mô tả phụ dưới tiêu đề (text-body-md text-on-surface-variant)
    Slot nhận vào:
      - $actions hoặc $slot: Cụm nút hành động bên phải
--}}
@props([
    'title',
    'chip' => null,
    'subtitle' => null,
])

{{-- Khung 1: Card trắng rounded-2xl p-6 md:p-8 chuẩn Design System --}}
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest rounded-2xl p-6 md:p-8 border border-surface-container-high/60 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6']) }}>
    <div class="space-y-1.5">
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Tiêu đề LUÔN là một cỡ duy nhất: text-headline-lg (24px/32px, bold, tracking-tight) màu on-surface --}}
            <h1 class="font-headline-lg text-headline-lg text-[24px] font-bold text-on-surface tracking-tight leading-tight">{{ $title }}</h1>
            @if($chip)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-xs font-semibold whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                    {{ $chip }}
                </span>
            @endif
        </div>
        @if($subtitle)
            <p class="font-body-md text-sm text-on-surface-variant max-w-2xl leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            {{ $actions }}
        </div>
    @elseif(!empty($slot) && trim($slot))
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            {{ $slot }}
        </div>
    @endif
</div>
