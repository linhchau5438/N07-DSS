{{--
    Đường dẫn: resources/views/reports/index.blade.php
    Mô tả: Trung tâm Báo cáo & Phân tích theo Monochrome Design System, đồng bộ với /dashboard và /products.
    Phụ thuộc: ReportController@index, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Báo cáo & Phân tích')
@section('page-title', 'Báo cáo & Phân tích')
@section('page-subtitle', 'Khai thác số liệu vận hành, tồn kho và chất lượng dự báo.')

@section('content')
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header (Card trắng rounded-2xl, p-6 md:p-8) --}}
    {{-- SỬA: Tiêu đề cố định cỡ text-headline-lg (24px, bold) đồng bộ mọi trang   --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Báo cáo & Phân tích"
                      chip="Trung tâm số liệu vận hành"
                      subtitle="Theo dõi biến động dòng hàng nhập - xuất - tồn, kiểm soát ngưỡng cảnh báo an toàn và đánh giá mô hình dự báo.">
        <x-slot:actions>
            {{-- Nút in nhanh danh mục báo cáo --}}
            <button type="button" onclick="window.print()"
                    class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>In trang</span>
            </button>

            {{-- Nối route: reports.olap --}}
            <a href="{{ route('reports.olap') }}"
               class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-white">grid_view</span>
                <span class="text-white">Phân tích đa chiều (OLAP)</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card                --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- Thanh lọc thời gian và thông tin đồng bộ --}}
        <div class="flex items-center justify-between border-b border-surface-container-high/60 pb-4 flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-on-surface flex items-center gap-1.5 uppercase tracking-wider font-mono">
                    <span class="material-symbols-outlined text-[16px] text-secondary">date_range</span>
                    <span>Kỳ báo cáo</span>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary text-white">
                    Tháng này ({{ now()->format('m/Y') }})
                </span>
            </div>

            <div class="text-xs text-on-surface-variant flex items-center gap-2 font-mono">
                <span class="material-symbols-outlined text-[16px] text-secondary">verified</span>
                <span>Dữ liệu tính đến {{ now()->format('H:i - d/m/Y') }}</span>
            </div>
        </div>

        {{-- Dải KPI tóm tắt các nhóm chỉ số cốt lõi (GỌN 140PX ĐƠN SẮC) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.kpi-card label="Danh mục biểu mẫu"
                           value="07"
                           unit="mẫu"
                           icon="description"
                           tone="sage">
                <span class="truncate">Đáp ứng chuẩn kho vận N07</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Báo cáo trọng điểm"
                           value="BC01"
                           unit="ROP"
                           icon="notification_important"
                           tone="grey"
                           pill="Cảnh báo"
                           status="warning">
                <span class="truncate">Cảnh báo thiếu hụt tự động</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Mô hình dự báo"
                           value="MA + LR"
                           icon="model_training"
                           tone="sage">
                <span class="truncate">Kiểm thử sai số 7 ngày</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Khối phân tích (OLAP)"
                           value="3 Chiều"
                           icon="view_in_ar"
                           tone="grey"
                           pill="Phân tích"
                           status="success">
                <span class="truncate">Roll-up, Drill-down, Slice, Dice</span>
            </x-ui.kpi-card>
        </div>

        {{-- ===================================================================== --}}
        {{-- LƯỚI CARD CÁC BIỂU MẪU BÁO CÁO (3 CỘT x 2 HÀNG + OLAP)                 --}}
        {{-- ===================================================================== --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Danh sách biểu mẫu báo cáo vận hành</span>
                </h3>
                <span class="text-xs text-on-surface-variant font-medium">Chọn biểu mẫu để lọc chi tiết</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- 1. BC01: Tồn kho & Cảnh báo ROP (NỔI BẬT NỀN XANH ĐẬM #1A4D38) --}}
                <x-ui.report-card code="BC01"
                                  title="Tồn kho và cảnh báo ROP"
                                  description="Theo dõi lượng tồn, hàng đang về, IP, nhu cầu/ngày, SS, ROP và trạng thái cảnh báo an toàn của từng sản phẩm."
                                  icon="warning"
                                  :route="route('reports.bc01')"
                                  :highlight="true"
                                  badge="Khẩn cấp"
                                  metric="Kiểm soát ROP tức thời" />

                {{-- 2. BC02: Nhập - xuất - tồn --}}
                <x-ui.report-card code="BC02"
                                  title="Nhập – xuất – tồn"
                                  description="Chi tiết số lượng tồn đầu kỳ, tổng nhập, tổng xuất và tồn kho cuối kỳ theo từng mặt hàng và khoảng thời gian."
                                  icon="swap_horiz"
                                  :route="route('reports.bc02')"
                                  badge="Định kỳ"
                                  metric="Đối chiếu chứng từ" />

                {{-- 3. BC03: Hàng cần nhập --}}
                <x-ui.report-card code="BC03"
                                  title="Hàng cần nhập (DSS)"
                                  description="Theo dõi danh sách các mặt hàng được hệ thống DSS đề xuất, số lượng Admin đã phê duyệt và tiến độ nhận hàng."
                                  icon="add_shopping_cart"
                                  :route="route('reports.bc03')"
                                  badge="Bổ sung"
                                  metric="Tiến độ đơn hàng" />

                {{-- 4. BC04: Sản phẩm bán chạy --}}
                <x-ui.report-card code="BC04"
                                  title="Sản phẩm bán chạy"
                                  description="Xếp hạng các mặt hàng theo số lượng và tổng doanh thu/giá trị xuất kho trong kỳ để tối ưu vị trí lưu kho."
                                  icon="trending_up"
                                  :route="route('reports.best-selling')"
                                  badge="Hiệu suất"
                                  metric="Tốc độ luân chuyển" />

                {{-- 5. BC05: Hàng tồn lâu / Chậm luân chuyển --}}
                <x-ui.report-card code="BC05"
                                  title="Hàng tồn lâu, chậm luân chuyển"
                                  description="Phát hiện các mặt hàng còn tồn đọng trong kho nhưng không phát sinh hoặc rất ít giao dịch xuất trong kỳ."
                                  icon="history"
                                  :route="route('reports.bc04')"
                                  badge="Rủi ro tồn"
                                  metric="Cảnh báo ứ đọng" />

                {{-- 6. BC06: Đánh giá dự báo nhu cầu (BC05 Controller) --}}
                <x-ui.report-card code="BC06"
                                  title="Đánh giá dự báo nhu cầu"
                                  description="So sánh độ chính xác giữa Moving Average và Linear Regression trên cùng 7 ngày kiểm thử (MAE, RMSE)."
                                  icon="ssid_chart"
                                  :route="route('reports.bc05')"
                                  badge="Thuật toán"
                                  metric="Độ lệch MAE & RMSE" />

                {{-- 7. OLAP: Phân tích theo nhiều chiều --}}
                <x-ui.report-card code="OLAP"
                                  title="Phân tích đa chiều (Kho dữ liệu)"
                                  description="Khai thác dữ liệu kho theo danh mục (Roll-up), sản phẩm (Drill-down), thời gian và phân loại giao dịch (Slice & Dice)."
                                  icon="analytics"
                                  :route="route('reports.olap')"
                                  badge="Đa chiều"
                                  metric="Roll-up & Drill-down" />
            </div>
        </div>

    </x-ui.section-card>

</div>
@endsection
