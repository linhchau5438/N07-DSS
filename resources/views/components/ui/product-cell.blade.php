{{--
    Đường dẫn: resources/views/components/ui/product-cell.blade.php
    Mô tả: Ô thông tin Sản phẩm & SKU chuẩn dùng chung cho các bảng danh sách hàng hóa.
    // SỬA: Tăng kích thước ô icon 44px, tên sản phẩm 15-16px font-semibold, SKU 12-13px.
--}}
@props([
    'name' => null,
    'title' => null,
    'sku' => null,
    'subtitle' => null,
    'image' => null,
    'url' => null,
    'meta' => null,
])

@php
    $displayName = $name ?? $title ?? '';
    $displaySku = $sku ?? $subtitle ?? null;
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3.5']) }}>
    {{-- Khung thumbnail 44x44 (w-11 h-11) bo góc rounded-xl --}}
    <div class="w-11 h-11 rounded-xl bg-surface-container-low border border-surface-container-high/60 overflow-hidden flex items-center justify-center shrink-0">
        @if($image)
            <img src="{{ $image }}" alt="{{ $displayName }}" loading="lazy" class="w-full h-full object-cover">
        @else
            <span class="material-symbols-outlined text-[22px] text-on-surface-variant">inventory_2</span>
        @endif
    </div>

    {{-- Tên sản phẩm & SKU font-mono --}}
    <div class="flex flex-col min-w-0">
        @if($url)
            <a href="{{ $url }}" title="{{ $displayName }}" class="font-semibold text-base text-on-surface hover:text-primary hover:underline transition-colors truncate max-w-xs md:max-w-md block">
                {{ $displayName }}
            </a>
        @else
            <span title="{{ $displayName }}" class="font-semibold text-base text-on-surface truncate max-w-xs md:max-w-md block">
                {{ $displayName }}
            </span>
        @endif

        <div class="flex items-center gap-2 text-[13px] text-on-surface-variant mt-0.5 font-mono">
            @if($displaySku)
                <span>{{ str_starts_with($displaySku, 'SKU:') ? $displaySku : 'SKU: ' . $displaySku }}</span>
            @endif
            @if($meta)
                <span class="text-on-surface-variant/80 font-sans">· {{ $meta }}</span>
            @endif
            @if(!empty($slot) && trim($slot))
                <span>{{ $slot }}</span>
            @endif
        </div>
    </div>
</div>
