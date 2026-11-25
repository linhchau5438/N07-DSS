@extends('layouts.app')

@section('title', $title)

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('reports.index') }}">Báo cáo</a>
    <span class="sep">/</span>
    <span class="current">{{ $title }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>{{ $title }}</h2>
        <p>
            @if($from && $to)
                Khoảng thời gian phân tích: <strong>{{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }}</strong> đến <strong>{{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}</strong>
            @else
                Dữ liệu phân tích theo các đề xuất và giao dịch mới nhất trong kho
            @endif
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Tất cả báo cáo</a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ In báo cáo</button>
    </div>
</div>

<!-- Quick Navigation Tabs Across Reports -->
<div class="saas-tabs">
    <a class="saas-tab {{ request()->routeIs('reports.bc01') ? 'active' : '' }}" href="{{ route('reports.bc01') }}">
        Cảnh báo bổ sung (BC01)
    </a>
    <a class="saas-tab {{ request()->routeIs('reports.bc02') ? 'active' : '' }}" href="{{ route('reports.bc02') }}">
        Nhập – xuất – tồn (BC02)
    </a>
    <a class="saas-tab {{ request()->routeIs('reports.bc03') ? 'active' : '' }}" href="{{ route('reports.bc03') }}">
        Đề xuất nhập hàng (BC03)
    </a>
    <a class="saas-tab {{ request()->routeIs('reports.bc04') ? 'active' : '' }}" href="{{ route('reports.bc04') }}">
        Rủi ro tồn & Chậm luân chuyển (BC04)
    </a>
    <a class="saas-tab {{ request()->routeIs('reports.bc05') ? 'active' : '' }}" href="{{ route('reports.bc05') }}">
        Dự báo nhu cầu MA & ML (BC05)
    </a>
    <a class="saas-tab {{ request()->routeIs('reports.olap') ? 'active' : '' }}" href="{{ route('reports.olap') }}">
        Phân tích kho đa chiều (OLAP)
    </a>
</div>

<!-- Filters Toolbar -->
<form method="GET" class="filters">
    @if($showStockStatus ?? false)
        <label class="form-field">
            <span class="form-label">Tình trạng tồn kho</span>
            <select name="stock_status">
                <option value="">Tất cả tình trạng</option>
                @foreach([
                    'NORMAL' => 'Mức tồn bình thường',
                    'REORDER' => 'Cần xem xét nhập (≤ ROP)',
                    'MISSING_CONFIG' => 'Chưa cấu hình thông số',
                    'INSUFFICIENT_DATA' => 'Chưa đủ dữ liệu phân tích',
                    'INVALID_DATA' => 'Dữ liệu bất thường'
                ] as $key => $label)
                    <option value="{{ $key }}" @selected(request('stock_status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    @endif

    @if(!$showOlapFilters)
        <label class="form-field">
            <span class="form-label">Từ ngày</span>
            <input type="date" name="from" value="{{ request('from', $from ?? now()->startOfMonth()->toDateString()) }}">
        </label>
        <label class="form-field">
            <span class="form-label">Đến ngày</span>
            <input type="date" name="to" value="{{ request('to', $to ?? now()->toDateString()) }}">
        </label>
    @else
        <label class="form-field">
            <span class="form-label">Từ ngày</span>
            <input type="date" name="from" value="{{ $from }}">
        </label>
        <label class="form-field">
            <span class="form-label">Đến ngày</span>
            <input type="date" name="to" value="{{ $to }}">
        </label>
        <label class="form-field">
            <span class="form-label">Chiều phân tích</span>
            <select name="dimension">
                <option value="category" {{ $dimension === 'category' ? 'selected' : '' }}>Theo nhóm hàng (Danh mục)</option>
                <option value="product" {{ $dimension === 'product' ? 'selected' : '' }}>Chi tiết từng sản phẩm</option>
                <option value="month" {{ $dimension === 'month' ? 'selected' : '' }}>Theo tháng trong năm</option>
            </select>
        </label>
        <label class="form-field">
            <span class="form-label">Lọc loại phiếu</span>
            <select name="type">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tất cả (Nhập và Xuất)</option>
                <option value="import" {{ $type === 'import' ? 'selected' : '' }}>Chỉ phiếu nhập</option>
                <option value="export" {{ $type === 'export' ? 'selected' : '' }}>Chỉ phiếu xuất</option>
            </select>
        </label>
    @endif

    <label class="form-field">
        <span class="form-label">Nhóm danh mục</span>
        <select name="category_id">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="form-field">
        <span class="form-label">Mặt hàng cụ thể</span>
        <select name="product_id">
            <option value="">Tất cả mặt hàng</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                    {{ $product->product_code }} — {{ $product->name }}
                </option>
            @endforeach
        </select>
    </label>

    <button type="submit" class="btn" style="margin-top:auto;height:36px">
        🔍 Lọc kết quả
    </button>
</form>

@if(isset($statusCounts) && count($statusCounts) > 0)
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        @foreach($statusCounts as $st => $count)
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:6px 12px;font-size:11.5px">
                <span class="text-muted">{{ $st }}:</span> <strong>{{ $count }}</strong>
            </div>
        @endforeach
    </div>
@endif

<!-- Data Table Card -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 class="card-title">Kết quả tổng hợp chi tiết</h3>
            <div class="card-subtitle">Có <strong>{{ count($rows) }}</strong> dòng dữ liệu phù hợp với điều kiện lọc</div>
        </div>
        @if($showOlapFilters)
            <span class="badge badge-info">Phân tích đa chiều OLAP</span>
        @endif
    </div>

    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th class="{{ in_array($header, ['Số lượng', 'Tồn kho', 'Tổng nhập', 'Tổng xuất', 'Thành tiền', 'Tồn thực tế', 'Giá trị tồn', 'Số ngày tồn dự kiến']) ? 'text-right' : '' }}">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($row as $key => $cell)
                            <td class="{{ is_numeric(str_replace([',', '.', ' đ', '%'], '', (string)$cell)) && !str_starts_with((string)$cell, '0') && strlen((string)$cell) < 15 && !preg_match('/[a-zA-Z]/', (string)$cell) ? 'text-right' : '' }}">
                                @if(is_string($cell) && str_contains($cell, 'Hàng không tiêu thụ (Dead Stock)'))
                                    <span class="badge badge-danger">{{ $cell }}</span>
                                @elseif(is_string($cell) && str_contains($cell, 'Hàng tồn dư mức cao (Excess Stock)'))
                                    <span class="badge badge-warning">{{ $cell }}</span>
                                @elseif(is_string($cell) && str_contains($cell, 'Hàng chậm luân chuyển (Slow Moving)'))
                                    <span class="badge badge-info">{{ $cell }}</span>
                                @elseif(is_string($cell) && str_contains($cell, 'Luân chuyển bình thường'))
                                    <span class="badge badge-success">{{ $cell }}</span>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="empty-state">
                            <span class="empty-icon">📊</span>
                            <div class="empty-title">Không có số liệu trong kỳ này</div>
                            <p class="empty-desc">Thử nới rộng khoảng thời gian tìm kiếm hoặc chọn danh mục khác.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
