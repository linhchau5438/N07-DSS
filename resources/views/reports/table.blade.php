{{--
    Đường dẫn: resources/views/reports/table.blade.php
    Mô tả: Giao diện Báo cáo dạng bảng dữ liệu chung (BC01, BC02, BC03, BC04, Bán chạy, OLAP) chuẩn Monochrome N07.
    Phụ thuộc: ReportController, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)
@section('page-subtitle', 'Lọc theo thời gian, danh mục hoặc sản phẩm để tập trung vào nội dung cần xem.')

@section('content')
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header (Card trắng rounded-2xl, p-6 md:p-8) --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header :title="$title"
                      chip="Báo cáo chi tiết"
                      :subtitle="$from && $to ? 'Kỳ dữ liệu: ' . \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') . ' — ' . \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') : 'Báo cáo trích xuất dữ liệu vận hành theo các chỉ số mới nhất.'">
        <x-slot:actions>
            <a href="{{ route('reports.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Tất cả báo cáo</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-white">print</span>
                <span class="text-white">In báo cáo</span>
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card                --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- TOOLBAR BỘ LỌC DỮ LIỆU BÁO CÁO DÙNG COMPONENT CHUẨN --}}
        <x-ui.toolbar method="GET">
            <div class="flex flex-wrap items-center gap-3 w-full">
                @if($showStockStatus ?? false)
                    <x-ui.filter-select name="stock_status" width="w-full sm:w-48" :autoSubmit="false">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['NORMAL'=>'Bình thường','REORDER'=>'Cần xem xét','MISSING_CONFIG'=>'Chưa cấu hình','INSUFFICIENT_DATA'=>'Thiếu dữ liệu','INVALID_DATA'=>'Dữ liệu bất thường'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('stock_status') === $key)>{{ $label }}</option>
                        @endforeach
                    </x-ui.filter-select>
                @endif

                @if(!$showOlapFilters)
                    <x-ui.filter-date name="from" value="{{ request('from', $from ?? now()->startOfMonth()->toDateString()) }}" width="w-full sm:w-36" />
                    <x-ui.filter-date name="to" value="{{ request('to', $to ?? now()->toDateString()) }}" width="w-full sm:w-36" />
                @else
                    <x-ui.filter-date name="from" value="{{ $from }}" width="w-full sm:w-36" />
                    <x-ui.filter-date name="to" value="{{ $to }}" width="w-full sm:w-36" />
                    <x-ui.filter-select name="dimension" width="w-full sm:w-48" :autoSubmit="false">
                        <option value="category" {{ $dimension === 'category' ? 'selected' : '' }}>Danh mục (Roll-up)</option>
                        <option value="product" {{ $dimension === 'product' ? 'selected' : '' }}>Sản phẩm (Drill-down)</option>
                        <option value="month" {{ $dimension === 'month' ? 'selected' : '' }}>Tháng</option>
                    </x-ui.filter-select>
                    <x-ui.filter-select name="type" width="w-full sm:w-40" :autoSubmit="false">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Nhập và xuất</option>
                        <option value="import" {{ $type === 'import' ? 'selected' : '' }}>Chỉ nhập</option>
                        <option value="export" {{ $type === 'export' ? 'selected' : '' }}>Chỉ xuất</option>
                    </x-ui.filter-select>
                @endif

                <x-ui.filter-select name="category_id" width="w-full sm:w-48" :autoSubmit="false">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </x-ui.filter-select>

                <x-ui.filter-select name="product_id" width="w-full sm:w-56" :autoSubmit="false">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} — {{ $product->name }}
                        </option>
                    @endforeach
                </x-ui.filter-select>

                <x-ui.button type="submit" variant="primary" icon="filter_alt">
                    Lọc báo cáo
                </x-ui.button>
            </div>
        </x-ui.toolbar>

        {{-- BÁO CÁO PHÂN TÍCH NHU CẦU DỰ BÁO (NẾU CÓ) --}}
        @if($isForecastReport ?? false)
            @php $maxForecastDemand = max(1, collect($forecastSeries)->max('quantity')); @endphp
            <div class="p-6 rounded-2xl bg-surface-container-low/50 border border-surface-container-high/60 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono">Phân tích nhu cầu xuất hàng</h3>
                        <p class="text-xs text-on-surface-variant">Lượng xuất thực tế từng ngày trong khoảng thời gian đã chọn.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $forecastSufficientData ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $forecastSufficientData ? 'Đủ dữ liệu đánh giá' : 'Dữ liệu còn hạn chế' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-white border border-surface-container-high/60">
                        <span class="text-[11px] text-on-surface-variant block mb-1">Xuất TB mỗi ngày (30 ngày)</span>
                        <strong class="text-base font-bold text-on-surface font-mono">{{ number_format($averageDailyDemand, 2, ',', '.') }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-surface-container-high/60">
                        <span class="text-[11px] text-on-surface-variant block mb-1">Phương pháp dự báo</span>
                        <strong class="text-xs font-bold text-on-surface">Trung bình 30 ngày · Hồi quy tuyến tính</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-surface-container-high/60">
                        <span class="text-[11px] text-on-surface-variant block mb-1">Đánh giá độ chính xác</span>
                        <strong class="text-xs font-bold text-on-surface">{{ $forecastSufficientData ? 'Sai lệch được kiểm tra trên 7 ngày gần nhất.' : 'Cần thêm dữ liệu xuất để đánh giá độ chính xác.' }}</strong>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="p-3 rounded-xl bg-white border border-surface-container-high/60 overflow-x-auto">
                    <div class="flex items-end gap-1 h-32 pt-2" role="img" aria-label="Lượng xuất hàng thực tế theo ngày">
                        @foreach($forecastSeries as $day)
                            <div class="flex-1 min-w-[12px] h-full flex flex-col justify-end items-center group relative cursor-pointer">
                                <div class="hidden group-hover:block absolute bottom-full mb-1 z-20 px-2 py-0.5 rounded bg-neutral-900 text-white text-[10px] font-mono whitespace-nowrap shadow-md pointer-events-none">
                                    {{ $day['date'] }}: {{ $day['quantity'] }} đơn vị
                                </div>
                                <div class="w-full rounded-t bg-secondary group-hover:bg-[#1A4D38] transition-colors"
                                     style="height: {{ max(4, $day['quantity'] / $maxForecastDemand * 92) }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- BẢNG DỮ LIỆU KẾT QUẢ BÁO CÁO (DÙNG x-ui.data-table) --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-on-surface">
                        {{ ($isForecastReport ?? false) ? 'So sánh phương pháp ước tính' : 'Dữ liệu chi tiết' }}
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-container-low text-on-surface-variant border border-surface-container-high/60">
                        {{ count($rows) }} dòng kết quả
                    </span>
                </div>
                @if($showOlapFilters)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                        Pivot · So sánh nhập và xuất
                    </span>
                @endif
            </div>

            <x-ui.data-table minWidth="min-w-[1200px]">
                <x-slot:header>
                    @foreach($headers as $index => $header)
                        <th class="py-3.5 {{ $index === 0 ? 'pl-6 pr-4' : ($index === count($headers) - 1 ? 'pr-6 pl-4' : 'px-5') }}">
                            {{ $header }}
                        </th>
                    @endforeach
                </x-slot:header>

                @forelse($rows as $row)
                    <tr class="h-[72px] hover:bg-surface-container-low/70 transition-colors">
                        @foreach($row as $cellIndex => $cell)
                            <td class="py-5 {{ $cellIndex === 0 ? 'pl-6 pr-4 font-mono font-semibold' : ($cellIndex === count($row) - 1 ? 'pr-6 pl-4' : 'px-5') }} text-[15px] leading-6 text-on-surface">
                                @if(in_array($cell, ['NORMAL', 'REORDER', 'MISSING_CONFIG', 'INSUFFICIENT_DATA', 'INVALID_DATA']))
                                    @php
                                        $mapStatus = [
                                            'NORMAL' => ['Bình thường', 'success'],
                                            'REORDER' => ['Cần xem xét', 'danger'],
                                            'MISSING_CONFIG' => ['Chưa cấu hình', 'warning'],
                                            'INSUFFICIENT_DATA' => ['Thiếu dữ liệu', 'neutral'],
                                            'INVALID_DATA' => ['Bất thường', 'error'],
                                        ];
                                        $stat = $mapStatus[$cell] ?? [$cell, 'neutral'];
                                    @endphp
                                    <x-ui.status-pill :variant="$stat[1]" :label="$stat[0]" />
                                @else
                                    <span class="tabular-nums font-sans">{{ $cell }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="p-0 border-0">
                            <x-ui.empty-state icon="table_rows"
                                              title="Không có dữ liệu trong kỳ đã chọn"
                                              message="Vui lòng thử thay đổi khoảng thời gian hoặc điều kiện lọc danh mục/sản phẩm." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>
        </div>

    </x-ui.section-card>

</div>
@endsection
