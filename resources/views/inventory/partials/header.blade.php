{{--
    File: resources/views/inventory/partials/header.blade.php
    Mô tả: Header Khung 1 dùng chung cho cả trang Tồn kho hiện tại và Lịch sử nhập xuất.
--}}
@props([
    'productsCount' => 0,
])

@php
    $isAttention = request('stock') === 'attention';
@endphp

<x-ui.page-header :title="$isAttention ? 'Cảnh báo tồn kho' : 'Tồn kho & Lịch sử nhập xuất'"
                  :chip="$isAttention ? 'Cần xử lý khẩn cấp (' . $productsCount . ' mặt hàng)' : 'Kho Tổng Miền Bắc (N07-HN)'"
                  :subtitle="$isAttention ? 'Danh sách các mặt hàng chạm hoặc thấp hơn ngưỡng an toàn và điểm đặt lại (ROP). Bổ sung ngay để đảm bảo vận hành.' : 'Xem nhanh lượng tồn thực tế, theo dõi định mức an toàn, đề xuất bổ sung DSS và nhật ký giao dịch kho gần nhất.'">
    <x-slot:actions>
        {{-- Nối route: reports.bc01 --}}
        <x-ui.button variant="secondary" :href="route('reports.bc01')" icon="assessment">
            Báo cáo tồn kho
        </x-ui.button>

        {{-- Nối route: inventory.create (export) --}}
        <x-ui.button variant="secondary" :href="route('inventory.create', 'export')" icon="unarchive">
            − Tạo phiếu xuất
        </x-ui.button>

        {{-- Nối route: inventory.create (import) --}}
        <x-ui.button variant="primary" :href="route('inventory.create', 'import')" icon="archive">
            ＋ Tạo phiếu nhập
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
