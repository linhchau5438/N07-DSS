@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)
@section('page-subtitle', 'Lọc theo thời gian, danh mục hoặc sản phẩm để tập trung vào nội dung cần xem.')

@section('content')
    <div class="page-heading">
        <div><h2>Kết quả báo cáo</h2><p>
            @if($from && $to) Kỳ dữ liệu: {{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}
            @else Báo cáo theo các đề xuất mới nhất
            @endif
        </p></div>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Tất cả báo cáo</a>
    </div>

    <section class="content">
        <form method="GET" class="filters">@if($showStockStatus ?? false)<label>Trạng thái<select name="stock_status"><option value="">Tất cả</option>@foreach(['NORMAL'=>'Bình thường','REORDER'=>'Cần xem xét','MISSING_CONFIG'=>'Chưa cấu hình','INSUFFICIENT_DATA'=>'Thiếu dữ liệu','INVALID_DATA'=>'Dữ liệu bất thường'] as $key=>$label)<option value="{{ $key }}" @selected(request('stock_status')===$key)>{{ $label }}</option>@endforeach</select></label>@endif
            @if(!$showOlapFilters)
                <label class="form-field"><span class="form-label">Từ ngày</span>
                    <input type="date" name="from" value="{{ request('from', $from ?? now()->startOfMonth()->toDateString()) }}">
                </label>
                <label class="form-field"><span class="form-label">Đến ngày</span>
                    <input type="date" name="to" value="{{ request('to', $to ?? now()->toDateString()) }}">
                </label>
            @else
                <label class="form-field"><span class="form-label">Từ ngày</span><input type="date" name="from" value="{{ $from }}"></label>
                <label class="form-field"><span class="form-label">Đến ngày</span><input type="date" name="to" value="{{ $to }}"></label>
                <label class="form-field"><span class="form-label">Chiều phân tích</span>
                    <select name="dimension">
                        <option value="category" {{ $dimension === 'category' ? 'selected' : '' }}>Danh mục (Roll-up)</option>
                        <option value="product" {{ $dimension === 'product' ? 'selected' : '' }}>Sản phẩm (Drill-down)</option>
                        <option value="month" {{ $dimension === 'month' ? 'selected' : '' }}>Tháng</option>
                    </select>
                </label>
                <label class="form-field"><span class="form-label">Slice · Loại giao dịch</span>
                    <select name="type">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Nhập và xuất</option>
                        <option value="import" {{ $type === 'import' ? 'selected' : '' }}>Chỉ nhập</option>
                        <option value="export" {{ $type === 'export' ? 'selected' : '' }}>Chỉ xuất</option>
                    </select>
                </label>
            @endif
            <label class="form-field"><span class="form-label">Dice · Danh mục</span>
                <select name="category_id">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-field"><span class="form-label">Sản phẩm</span>
                <select name="product_id">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn">Lọc báo cáo</button>
        </form>

        @if($isForecastReport ?? false)
            @php
                $maxForecastDemand = max(1, collect($forecastSeries)->max('quantity'));
            @endphp
            <section class="forecast-panel">
                @isset($statusCounts)<p>@foreach($statusCounts as $status=>$count) {{ $status }}: {{ $count }} · @endforeach</p>@endisset
<div class="table-caption">
                    <div><h3>Phân tích nhu cầu xuất hàng</h3><p>Lượng xuất thực tế từng ngày trong khoảng thời gian đã chọn.</p></div>
                    <span class="badge {{ $forecastSufficientData ? 'badge-success' : 'badge-warning' }}">
                        {{ $forecastSufficientData ? 'Đủ dữ liệu đánh giá' : 'Dữ liệu còn hạn chế' }}
                    </span>
                </div>
                <div class="inventory-summary">
                    <div class="inventory-summary-item">
                        <span>Lượng xuất trung bình mỗi ngày (30 ngày gần nhất)</span>
                        <strong>{{ number_format($averageDailyDemand, 2, ',', '.') }}</strong>
                    </div>
                    <div class="inventory-summary-item">
                        <span>Phương pháp dự báo</span>
                        <strong>Trung bình 30 ngày · Hồi quy tuyến tính</strong>
                    </div>
                    <div class="inventory-summary-item">
                        <span>Ghi chú</span>
                        <strong>{{ $forecastSufficientData ? 'Sai lệch được kiểm tra trên 7 ngày gần nhất.' : 'Cần thêm dữ liệu xuất để đánh giá độ chính xác.' }}</strong>
                    </div>
                </div>
                <div class="forecast-chart-scroll">
                    <div class="chart" role="img" aria-label="Lượng xuất hàng thực tế theo ngày">
                        @foreach($forecastSeries as $day)
                            <div class="chart-day">
                                <div class="chart-bars">
                                    <div class="chart-bar chart-bar-export" title="{{ $day['date'] }}: {{ $day['quantity'] }} đơn vị"
                                         style="height: {{ max(2, $day['quantity'] / $maxForecastDemand * 90) }}%"></div>
                                </div>
                                <span class="chart-label">{{ $day['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="chart-legend"><span><i class="legend-dot" style="background:#6d8fe7"></i>Lượng xuất thực tế</span></div>
            </section>
        @endif

        @isset($statusCounts)<p>@foreach($statusCounts as $status=>$count) {{ $status }}: {{ $count }} · @endforeach</p>@endisset
<div class="table-caption">
            <div><h3>{{ ($isForecastReport ?? false) ? 'So sánh phương pháp ước tính' : 'Dữ liệu báo cáo' }}</h3><p>{{ count($rows) }} dòng kết quả</p></div>
            @if($showOlapFilters)<span class="badge badge-info">Pivot · so sánh nhập và xuất</span>@endif
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                @empty
                    <tr><td class="empty-state" colspan="{{ count($headers) }}">Không có dữ liệu trong kỳ đã chọn.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
