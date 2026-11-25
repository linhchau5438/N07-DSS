{{--
    Đường dẫn: resources/views/components/ui/section-card.blade.php
    Mô tả: Card lớn trắng (Khung 2) rounded-2xl chuẩn Design System.
    Props nhận vào:
      - $padding (string, mặc định 'p-6 md:p-8'): Khoảng cách đệm
      - $spacing (string, mặc định 'space-y-6'): Khoảng cách giữa các khối con
--}}
@props([
    'padding' => 'p-6 md:p-8',
    'spacing' => 'space-y-6',
])

<div {{ $attributes->merge(['class' => "bg-surface-container-lowest rounded-2xl $padding border border-surface-container-high/60 shadow-sm $spacing"]) }}>
    {{ $slot }}
</div>
