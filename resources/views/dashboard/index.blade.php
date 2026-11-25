{{-- File: resources/views/dashboard/index.blade.php --}}
{{-- Mô tả: Giao diện Trang tổng quan (Dashboard) kho vận N07 chuẩn xác theo DESIGN.md & screen.png. Khung 1: Header chào, Khung 2: Card lớn gồm Hàng 1: 4 thẻ KPI, Hàng 2: Lưới 12 cột 8:4 cân bằng chiều cao với biểu đồ cột to bo góc hiện đại, Hàng 3: Khối widget phụ toàn chiều rộng. --}}
{{-- Phụ thuộc: DashboardController@index, layouts/app.blade.php, các biến: $metrics, $chart, $topSellers, $recentTransactions, $reorderCount, $pendingRecommendations, $lowStock. --}}

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- ========================================================================= --}}
    {{-- Khung 1: Top Header Bar chào mừng & tác vụ nhanh dùng x-ui.page-header    --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header :title="'Chào ' . auth()->user()->name"
                      chip="Kho Tổng Miền Bắc"
                      subtitle="Đây là những thông tin ưu tiên cần xử lý và theo dõi trong kho hàng hôm nay.">
        <x-slot:actions>
            {{-- Nối dữ liệu: route('reports.index') --}}
            <a href="{{ route('reports.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-lowest text-on-surface shadow-sm hover:bg-surface-container-low transition-all active:scale-[0.98] border border-surface-container-high">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">description</span>
                <span class="font-label-md text-label-md">Xuất báo cáo</span>
            </a>

            {{-- Nối dữ liệu: route('inventory.create', 'export') --}}
            <a href="{{ route('inventory.create', 'export') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-lowest text-on-surface shadow-sm hover:bg-surface-container-low transition-all active:scale-[0.98] border border-surface-container-high">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">upload_file</span>
                <span class="font-label-md text-label-md">− Tạo phiếu xuất</span>
            </a>

            {{-- Nối dữ liệu: route('inventory.create', 'import') --}}
            <a href="{{ route('inventory.create', 'import') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-primary text-on-primary shadow-sm hover:bg-neutral-800 transition-all active:scale-[0.98]">
                <span class="material-symbols-outlined text-[19px]">add_box</span>
                <span class="font-label-md text-label-md">+ Tạo phiếu nhập</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- Khung 2: Main Content Card lớn dùng x-ui.section-card                     --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-8">

        {{-- ===================================================================== --}}
        {{-- Hàng 1: Hệ thống 4 thẻ KPI thống kê chính (ĐƠN SẮC, GỌN min-h-[124px]) --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Thẻ 1: Tổng mặt hàng --}}
            <x-ui.kpi-card label="Tổng mặt hàng"
                           :value="number_format($metrics->product_count, 0, ',', '.')"
                           unit="mã"
                           icon="inventory_2"
                           tone="sage"
                           pill="+12.4%">
                <span class="truncate">{{ number_format($metrics->active_product_count, 0, ',', '.') }} nhóm hàng đang kinh doanh</span>
            </x-ui.kpi-card>

            {{-- Thẻ 2: Sắp hết / Hết hàng --}}
            <x-ui.kpi-card label="Sắp hết / Hết hàng"
                           :value="number_format($metrics->low_stock_count + $metrics->out_of_stock_count)"
                           unit="mã"
                           icon="warning"
                           tone="grey"
                           :pill="($metrics->low_stock_count + $metrics->out_of_stock_count) > 0 ? 'Cảnh báo' : 'Ổn định'"
                           :status="($metrics->low_stock_count + $metrics->out_of_stock_count) > 0 ? 'danger' : 'success'">
                <span class="truncate">{{ number_format($metrics->out_of_stock_count) }} hết sạch, {{ number_format($metrics->low_stock_count) }} chạm ngưỡng</span>
            </x-ui.kpi-card>

            {{-- Thẻ 3: Sản phẩm cần nhập --}}
            <x-ui.kpi-card label="Sản phẩm cần nhập"
                           :value="number_format($reorderCount)"
                           unit="đề xuất"
                           icon="notification_important"
                           tone="sage"
                           :pill="$reorderCount > 0 ? 'Cần duyệt' : 'Đầy đủ'"
                           :status="$reorderCount > 0 ? 'warning' : 'neutral'">
                <span class="truncate">{{ number_format($pendingRecommendations) }} phiếu chờ duyệt DSS</span>
            </x-ui.kpi-card>

            {{-- Thẻ 4: Tổng giá trị tồn kho (Thẻ điểm nhấn giá trị) --}}
            <x-ui.kpi-card label="Tổng giá trị tồn kho"
                           :value="number_format($metrics->inventory_value, 0, ',', '.')"
                           unit="đ"
                           icon="payments"
                           pill="+4.5%"
                           :highlight="true">
                <span class="truncate">Tổng giá vốn hàng đang lưu kho</span>
            </x-ui.kpi-card>
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 2: Lưới Workspace 12 cột (8 Cột Trái : 4 Cột Phải)               --}}
        {{-- SỬA: items-stretch và cấu trúc cân bằng giúp loại bỏ hoàn toàn khoảng   --}}
        {{-- trắng thừa ở cột trái so với cột phải                                  --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

            {{-- ================================================================= --}}
            {{-- CỘT TRÁI (Chiếm 8 cột - lg:col-span-7 xl:col-span-8)               --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6 w-full">

                {{-- SỬA: Biểu đồ cột pill Nhập - Xuất 7 ngày: vùng cao h-72, cột to w-6 sm:w-8, bo góc vuông vắn rounded-t-lg rounded-b-sm --}}
                <div class="bg-surface-container-low/50 p-6 rounded-2xl border border-surface-container-high/60 w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Biến động Nhập – Xuất kho</h2>
                                <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-md font-medium">7 ngày</span>
                            </div>
                            {{-- Nối dữ liệu: chuỗi ngày từ $chart --}}
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                Số lượng kiện hàng hoàn tất ghi nhận mỗi ngày ({{ $chart[0]['date'] ?? '' }} - {{ end($chart)['date'] ?? '' }})
                            </p>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm bg-[#1A4D38]"></span>
                                <span class="text-xs text-on-surface-variant font-medium">Nhập kho</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm bg-[#6B859E]"></span>
                                <span class="text-xs text-on-surface-variant font-medium">Xuất kho</span>
                            </div>
                            {{-- Nối dữ liệu: route('reports.bc01') --}}
                            <a href="{{ route('reports.bc01') }}"
                               class="h-8 px-3 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-surface-container text-xs font-semibold transition-colors flex items-center gap-1 border border-surface-container-high/80">
                                <span>Báo cáo chi tiết</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    {{-- Khung dựng biểu đồ: chiều cao h-72, khoảng cách gap-2 giữa các cột --}}
                    @php
                        // GIỮ NGUYÊN: logic tính đỉnh biểu đồ để chuẩn hóa % chiều cao
                        $maxMovement = max(1, collect($chart)->max(fn ($day) => max($day['import'], $day['export'])));
                    @endphp
                    <div class="relative w-full h-72 pt-8 flex items-end justify-between gap-2 sm:gap-6 px-4 bg-surface-container-lowest/60 rounded-xl border border-surface-container-high/40 pb-4"
                         role="img" aria-label="Biểu đồ cột nhập xuất 7 ngày">
                        {{-- Đường lưới phụ ngầm --}}
                        <div class="absolute inset-x-4 inset-y-8 flex flex-col justify-between pointer-events-none opacity-40">
                            <div class="w-full border-b border-dashed border-surface-container-high"></div>
                            <div class="w-full border-b border-dashed border-surface-container-high"></div>
                            <div class="w-full border-b border-dashed border-surface-container-high"></div>
                            <div class="w-full border-b border-surface-container-high"></div>
                        </div>

                        {{-- Render 7 nhóm cột động: cột to w-6 sm:w-8, bo góc rounded-t-lg rounded-b-sm, min-h-[8px] --}}
                        @foreach($chart as $day)
                            @php
                                $importHeight = max(6, round(($day['import'] / $maxMovement) * 92));
                                $exportHeight = max(6, round(($day['export'] / $maxMovement) * 92));
                            @endphp
                            <div class="relative flex-1 flex flex-col items-center gap-2 z-10 group">
                                <div class="w-full flex items-end justify-center gap-2 h-48">
                                    {{-- Cột Nhập (#1A4D38) --}}
                                    <div class="w-5 sm:w-7 rounded-t-lg rounded-b-sm bg-[#1A4D38] transition-all group-hover:brightness-110 shadow-sm min-h-[8px]"
                                         style="height: {{ $importHeight }}%;"
                                         title="Nhập: {{ number_format($day['import']) }} kiện"></div>
                                    {{-- Cột Xuất (#6B859E) --}}
                                    <div class="w-5 sm:w-7 rounded-t-lg rounded-b-sm bg-[#6B859E] transition-all group-hover:brightness-110 shadow-sm min-h-[8px]"
                                         style="height: {{ $exportHeight }}%;"
                                         title="Xuất: {{ number_format($day['export']) }} kiện"></div>
                                </div>
                                <span class="text-xs {{ $loop->last ? 'font-bold text-on-surface' : 'text-on-surface-variant font-medium' }}">
                                    {{ $loop->last ? 'Hôm nay' : $day['date'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- SỬA: Bảng Hoạt động điều chuyển gần nhất (có flex-1 flex flex-col justify-between tự giãn đều chiều cao với cột phải) --}}
                <div class="bg-surface-container-low/50 p-6 rounded-2xl border border-surface-container-high/60 w-full flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4">
                            <div>
                                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Hoạt động điều chuyển gần nhất</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">6 phiếu xuất nhập kho được lập gần đây trong hệ thống</p>
                            </div>
                            <a href="{{ route('inventory.index') }}"
                               class="font-label-sm text-label-sm text-on-surface-variant hover:text-on-surface flex items-center gap-1 font-semibold transition-colors">
                                <span>Toàn bộ nhật ký</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>

                        {{-- BẢNG HOẠT ĐỘNG GẦN NHẤT DÙNG x-ui.data-table THỐNG NHẤT --}}
                        <x-ui.data-table minWidth="min-w-[650px]">
                            <x-slot:header>
                                <th class="py-3 pl-6 pr-4 w-36">Mã phiếu</th>
                                <th class="py-3 px-4 text-center w-36">Phân loại</th>
                                <th class="py-3 px-4 w-32">Thời gian</th>
                                <th class="py-3 px-4">Người phụ trách</th>
                                <th class="py-3 px-4 text-center w-32">Trạng thái</th>
                                <th class="py-3 pl-4 pr-6 text-right w-20">Chi tiết</th>
                            </x-slot:header>

                            @forelse($recentTransactions as $transaction)
                                <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                                    <x-ui.cell-code :code="$transaction->transaction_code"
                                                    :href="route('inventory.show', $transaction->id)"
                                                    class="pl-6" />

                                    <td class="py-5 px-4 text-center whitespace-nowrap">
                                        <x-ui.status-pill :variant="$transaction->type === 'import' ? 'success' : 'info'"
                                                          :label="$transaction->type === 'import' ? 'Nhập kho' : 'Xuất kho'" />
                                    </td>

                                    <x-ui.cell-date :date="$transaction->transaction_date" class="px-4" />

                                    <td class="py-5 px-4 whitespace-nowrap font-medium text-[15px] text-on-surface">
                                        {{ $transaction->staff_name ?? 'Admin N07' }}
                                    </td>

                                    <td class="py-5 px-4 text-center whitespace-nowrap">
                                        <x-ui.status-pill :variant="$transaction->type === 'import' ? 'success' : 'info'"
                                                          :label="$transaction->type === 'import' ? 'Đã lưu kho' : 'Đang giao'" />
                                    </td>

                                    <td class="py-5 pl-4 pr-6 text-right whitespace-nowrap">
                                        <a href="{{ route('inventory.show', $transaction->id) }}"
                                           class="w-9 h-9 rounded-xl inline-flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors"
                                           title="Xem chi tiết phiếu">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-0">
                                        <x-ui.empty-state icon="receipt_long"
                                                          title="Chưa có giao dịch"
                                                          message="Chưa có hoạt động nhập xuất nào được ghi nhận gần đây." />
                                    </td>
                                </tr>
                            @endforelse

                            <x-slot:footer>
                                <div class="flex items-center justify-between text-xs text-on-surface-variant font-medium">
                                    <span>Hiển thị {{ count($recentTransactions) }} giao dịch mới nhất</span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Đồng bộ thời gian thực
                                    </span>
                                </div>
                            </x-slot:footer>
                        </x-ui.data-table>
                    </div>

                    {{-- Dòng tóm tắt trạng thái phiếu dưới chân bảng --}}
                    <div class="pt-3 border-t border-surface-container-high/60 flex items-center justify-between text-[11px] text-on-surface-variant">
                        <span>Hiển thị 6 giao dịch mới nhất</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đồng bộ tự động
                        </span>
                    </div>
                </div>

            </div>

            {{-- ================================================================= --}}
            {{-- CỘT PHẢI (Chiếm 4 cột - lg:col-span-5 xl:col-span-4)               --}}
            {{-- Chỉ giữ 3 widget cốt lõi để đạt chiều cao tương đương cột trái:     --}}
            {{-- 1. Ưu tiên hôm nay | 2. Dung tích kho hàng | 3. Hàng xuất nhiều     --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6 w-full">

                {{-- Widget 1: Ưu tiên hôm nay --}}
                <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 w-full">
                    <div class="flex items-center justify-between pb-3">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Ưu tiên hôm nay</h2>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">2 Việc cần làm</span>
                    </div>

                    <div class="space-y-3">
                        {{-- Box 1: Đề xuất nhập hàng (Amber pastel) --}}
                        <div class="p-4 rounded-xl bg-amber-50/90 border border-amber-200/80 transition-all hover:border-amber-300">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    {{-- Nối dữ liệu: $reorderCount --}}
                                    @if($reorderCount > 0)
                                        <h4 class="text-xs font-bold text-amber-900 leading-snug">{{ $reorderCount }} sản phẩm có đề xuất nhập hàng</h4>
                                        <p class="text-xs text-amber-800/80">{{ $pendingRecommendations }} đề xuất đang chờ Admin duyệt bổ sung</p>
                                    @else
                                        <h4 class="text-xs font-bold text-amber-900 leading-snug">Chưa có đề xuất nhập hàng mới</h4>
                                        <p class="text-xs text-amber-800/80">Kiểm tra sau khi phát sinh thêm giao dịch xuất kho</p>
                                    @endif
                                </div>
                                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">assignment_turned_in</span>
                                </span>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-amber-200/60 flex justify-between items-center">
                                <span class="text-[11px] text-amber-700 font-medium">Nhà cung cấp sẵn sàng</span>
                                <a class="text-xs font-bold text-amber-900 hover:underline flex items-center gap-0.5" href="{{ route('dss.index') }}">
                                    Xem đề xuất <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            </div>
                        </div>

                        {{-- Box 2: Tồn kho thấp (Rose pastel) --}}
                        <div class="p-4 rounded-xl bg-rose-50/90 border border-rose-200/80 transition-all hover:border-rose-300">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    {{-- Nối dữ liệu: $metrics->low_stock_count + $metrics->out_of_stock_count --}}
                                    <h4 class="text-xs font-bold text-rose-900 leading-snug">
                                        {{ number_format($metrics->low_stock_count + $metrics->out_of_stock_count) }} mặt hàng có tồn kho thấp
                                    </h4>
                                    <p class="text-xs text-rose-800/80">
                                        {{ number_format($metrics->out_of_stock_count) }} mặt hàng hết sạch, {{ number_format($metrics->low_stock_count) }} chạm ngưỡng an toàn
                                    </p>
                                </div>
                                <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">warning</span>
                                </span>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-rose-200/60 flex justify-between items-center">
                                <span class="text-[11px] text-rose-700 font-medium">Cảnh báo tự động</span>
                                <a class="text-xs font-bold text-rose-900 hover:underline flex items-center gap-0.5" href="{{ route('inventory.index', ['stock' => 'attention']) }}">
                                    Xem cảnh báo <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-on-surface-variant/80 mt-3 italic text-center">Gợi ý chỉ hỗ trợ quyết định; không tự động tạo phiếu nhập.</p>
                </div>

                {{-- Widget 2: Dung tích kho hàng (Progress Gauge) --}}
                <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 w-full">
                    <div class="flex items-center justify-between pb-2">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Dung tích kho hàng</h2>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Kho A - 5.000m²</span>
                    </div>

                    <div class="flex flex-col items-center pt-2">
                        {{-- Semi-circle gauge SVG chuẩn tỉ lệ --}}
                        <div class="relative w-48 h-28 flex items-end justify-center">
                            <svg class="w-48 h-24 overflow-visible" viewBox="0 0 100 50">
                                <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="12"></path>
                                <path d="M 10 50 A 40 40 0 0 1 82 25" fill="none" stroke="#1A4D38" stroke-dasharray="125.6" stroke-dashoffset="35" stroke-linecap="round" stroke-width="12"></path>
                                <path d="M 80 27 A 40 40 0 0 1 88 44" fill="none" stroke="#F59E0B" stroke-dasharray="125.6" stroke-dashoffset="108" stroke-linecap="round" stroke-width="12"></path>
                            </svg>
                            <div class="absolute bottom-0 inset-x-0 flex flex-col items-center text-center">
                                <span class="text-3xl font-extrabold text-on-surface tracking-tight leading-none">68%</span>
                                <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider mt-1">Đã sử dụng</span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            {{-- Nối dữ liệu: $metrics->total_stock --}}
                            <p class="text-xs text-on-surface font-medium">
                                {{ number_format($metrics->total_stock, 0, ',', '.') }} / 5.000 Pallet khả dụng
                            </p>
                        </div>

                        {{-- Thước đo phân loại --}}
                        <div class="grid grid-cols-3 gap-2 w-full mt-4 pt-3 border-t border-surface-container-high/70 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1A4D38]"></span>
                                    <span class="text-[11px] text-on-surface-variant">Đã xếp</span>
                                </div>
                                <span class="text-xs font-bold text-on-surface">54%</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span class="text-[11px] text-on-surface-variant">Phân loại</span>
                                </div>
                                <span class="text-xs font-bold text-on-surface">14%</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                    <span class="text-[11px] text-on-surface-variant">Còn trống</span>
                                </div>
                                <span class="text-xs font-bold text-on-surface">32%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Widget 3: Hàng xuất nhiều (Top sản phẩm bán chạy) --}}
                <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 w-full">
                    <div class="flex items-center justify-between pb-3">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Hàng xuất nhiều</h2>
                        {{-- Nối dữ liệu: route('reports.bc03') --}}
                        <a href="{{ route('reports.bc03') }}"
                           class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-surface-container-lowest border border-surface-container-high text-on-surface hover:bg-surface-container transition-colors">
                            Báo cáo
                        </a>
                    </div>

                    <div class="space-y-3">
                        {{-- Nối dữ liệu: $topSellers --}}
                        @forelse($topSellers as $index => $item)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-surface-container-lowest/80 border border-surface-container-high/40">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg {{ $index === 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-bold text-xs">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-on-surface leading-tight">{{ $item->name }}</div>
                                        <div class="text-[11px] text-on-surface-variant font-mono">{{ $item->product_code }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-on-surface">
                                        {{ number_format($item->sold_quantity, 0, ',', '.') }} {{ $item->unit }}
                                    </div>
                                    <div class="text-[10px] text-emerald-700 font-medium">Xuất bán chạy</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant text-center py-4">Chưa có số liệu xuất hàng 30 ngày qua.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- SỬA: Hàng 3: Khối Widget phụ full width phía dưới lưới 8:4             --}}
        {{-- Chia lưới 3 cột: Đội ngũ trực kho | Cảnh báo tồn thấp | Chỉ số kho     --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pt-2">

            {{-- Thẻ 1: Đội ngũ trực kho & Ca làm việc --}}
            <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3">
                        <div class="flex items-center gap-2">
                            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Đội ngũ trực kho</h2>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <a href="{{ route('users.index') }}"
                           class="text-xs font-semibold text-on-surface-variant hover:text-on-surface flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[16px]">add</span> Phân ca
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        {{-- Thành viên 1: User đang đăng nhập --}}
                        <div class="flex items-center justify-between p-2 rounded-xl bg-surface-container-lowest/80 border border-surface-container-high/40">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-on-surface truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-[10px] text-on-surface-variant truncate">Tổng quản trị kho</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Đang ca trực
                            </span>
                        </div>

                        {{-- Thành viên 2: Vũ Minh Tâm --}}
                        <div class="flex items-center justify-between p-2 rounded-xl bg-surface-container-lowest/80 border border-surface-container-high/40">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    VT
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-on-surface truncate">Vũ Minh Tâm</div>
                                    <div class="text-[10px] text-on-surface-variant truncate">Trưởng nhóm xuất hàng</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Đang ca trực
                            </span>
                        </div>

                        {{-- Thành viên 3: Trần Bảo Long --}}
                        <div class="flex items-center justify-between p-2 rounded-xl bg-surface-container-lowest/80 border border-surface-container-high/40">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    TL
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-on-surface truncate">Trần Bảo Long</div>
                                    <div class="text-[10px] text-on-surface-variant truncate">Kiểm định nhập kho</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                Nghỉ giải lao
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-surface-container-high/60 text-[11px] text-on-surface-variant flex justify-between items-center">
                    <span>Ca sáng (07:00 - 15:30)</span>
                    <span class="font-semibold text-emerald-800">3/4 nhân sự</span>
                </div>
            </div>

            {{-- Thẻ 2: Cảnh báo tồn kho thấp cần xử lý --}}
            <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3">
                        <div class="flex items-center gap-2">
                            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Hàng tồn thấp chú ý</h2>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">
                                {{ number_format($metrics->low_stock_count + $metrics->out_of_stock_count) }} mã
                            </span>
                        </div>
                        <a href="{{ route('inventory.index', ['stock' => 'attention']) }}"
                           class="text-xs font-semibold text-error hover:underline flex items-center gap-0.5">
                            Chi tiết <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        {{-- Nối dữ liệu: $lowStock --}}
                        @forelse($lowStock->take(3) as $item)
                            <div class="flex items-center justify-between p-2 rounded-xl bg-surface-container-lowest/80 border border-surface-container-high/40">
                                <div class="min-w-0 flex-1 pr-2">
                                    <div class="text-xs font-bold text-on-surface truncate">{{ $item->name }}</div>
                                    <div class="text-[10px] text-on-surface-variant font-mono">{{ $item->product_code }} · {{ $item->category_name }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs font-bold text-error">{{ number_format($item->on_hand) }} {{ $item->unit }}</div>
                                    <span class="text-[10px] font-semibold text-rose-700">
                                        {{ $item->on_hand <= 0 ? 'Hết hàng' : 'Tồn thấp' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant text-center py-4">Tất cả mặt hàng đang duy trì tồn kho an toàn.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-surface-container-high/60 text-[11px] text-on-surface-variant flex justify-between items-center">
                    <span>Ngưỡng ROP tự động</span>
                    <a href="{{ route('dss.index') }}" class="font-semibold text-emerald-800 hover:underline">Tạo đề xuất DSS →</a>
                </div>
            </div>

            {{-- Thẻ 3: Thông số & Hiệu suất kho vận --}}
            <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-surface-container-high/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Chỉ số vận hành</h2>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Tuần này</span>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div>
                            <div class="flex justify-between text-xs font-semibold pb-1">
                                <span class="text-on-surface-variant">Tỷ lệ đáp ứng đơn hàng</span>
                                <span class="text-on-surface font-bold">96.8%</span>
                            </div>
                            <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                                <div class="bg-[#1A4D38] h-2 rounded-full" style="width: 96.8%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold pb-1">
                                <span class="text-on-surface-variant">Thời gian xử lý phiếu trung bình</span>
                                <span class="text-on-surface font-bold">14 phút</span>
                            </div>
                            <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                                <div class="bg-[#6B859E] h-2 rounded-full" style="width: 78%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold pb-1">
                                <span class="text-on-surface-variant">Độ chính xác kiểm kê tồn</span>
                                <span class="text-on-surface font-bold">99.4%</span>
                            </div>
                            <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-600 h-2 rounded-full" style="width: 99.4%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-surface-container-high/60 text-[11px] text-on-surface-variant flex justify-between items-center">
                    <span>Đánh giá hiệu suất: Xuất sắc</span>
                    <a href="{{ route('reports.olap') }}" class="font-semibold text-emerald-800 hover:underline">Phân tích OLAP →</a>
                </div>
            </div>

        </div>

    </x-ui.section-card>
</div>
@endsection
