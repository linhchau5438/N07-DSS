{{--
    Đường dẫn: resources/views/components/ui/tab-switcher.blade.php
    Mô tả: Thanh chuyển Tab dạng pill chuẩn Design System (Dashboard / Tồn kho / Danh mục).
--}}
<div {{ $attributes->merge(['class' => 'inline-flex items-center p-1 rounded-xl bg-surface-container-low border border-surface-container-high/60 gap-1']) }}>
    {{ $slot }}
</div>
