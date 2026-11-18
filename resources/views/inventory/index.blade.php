@extends('layouts.app')

@section('title', 'Tồn kho')
@section('page-title', 'Tồn kho')
@section('page-subtitle', 'Theo dõi lượng hàng, mức an toàn và thời điểm cần bổ sung.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>Tồn kho hiện tại</h2>
            <p>Xem nhanh lượng tồn và ưu tiên những mặt hàng cần xử lý.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('reports.bc01') }}" class="btn btn-secondary">Báo cáo tồn</a>
            <a href="{{ route('inventory.create', 'import') }}" class="btn">＋ Tạo phiếu nhập</a>
        </div>
    </div>

    <div class="grid inventory-metrics">
        <article class="stat-card">
            <span class="stat-label">Sản phẩm đang kinh doanh</span>
            <strong class="stat-value">{{ number_format($metrics->active_product_count) }}</strong>
            <span class="stat-meta">Mặt hàng hoạt động</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Giá trị tồn kho</span>
            <strong class="stat-value">{{ number_format($metrics->inventory_value, 0, ',', '.') }} đ</strong>
            <span class="stat-meta">Ước tính theo giá nhập hiện tại</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Sắp hết / hết hàng</span>
            <strong class="stat-value">{{ number_format($metrics->low_stock_count) }}</strong>
            <span class="stat-meta">Theo ngưỡng cảnh báo tồn kho</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Đề xuất cần xem xét</span>
            <strong class="stat-value">{{ number_format($metrics->reorder_count) }}</strong>
            <span class="stat-meta">Không tự động tạo phiếu nhập</span>
        </article>
    </div>

    @if($metrics->reorder_count > 0)
        <a href="{{ route('dss.index') }}" class="inventory-alert-banner">
            <span><strong>{{ number_format($metrics->reorder_count) }} mặt hàng có gợi ý bổ sung</strong>
                <small>Kiểm tra lượng tồn và đề xuất trước khi lập phiếu nhập.</small></span>
            <span class="inventory-alert-action">Xem đề xuất →</span>
        </a>
    @endif

    <section class="content" style="margin-bottom:16px">
        <form action="{{ route('inventory.index') }}" method="GET" class="filters">
            <div class="field-search">
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Tìm theo tên hoặc mã sản phẩm">
            </div>
            <select class="filter-select-auto" name="category_id" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="filter-select-auto" name="stock" onchange="this.form.submit()">
                @foreach(['NORMAL'=>'DSS: Bình thường','REORDER'=>'DSS: Cần xem xét nhập','MISSING_CONFIG'=>'DSS: Chưa cấu hình','INSUFFICIENT_DATA'=>'DSS: Thiếu dữ liệu','INVALID_DATA'=>'DSS: Dữ liệu bất thường'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['stock'] ?? '')===$key)>{{ $label }}</option>@endforeach
<option value="all" {{ ($filters['stock'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả tình trạng</option>
                <option value="attention" {{ ($filters['stock'] ?? '') === 'attention' ? 'selected' : '' }}>Cần chú ý (0-10)</option>
                <option value="low" {{ ($filters['stock'] ?? '') === 'low' ? 'selected' : '' }}>Tồn thấp (1-10)</option>
                <option value="out" {{ ($filters['stock'] ?? '') === 'out' ? 'selected' : '' }}>Hết hàng</option>
            </select>
            <button type="submit" class="btn">Tìm sản phẩm</button>
            @if($search || !empty($filters['category_id']) || (($filters['stock'] ?? 'all') !== 'all'))
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="table-caption">
            <div><h3>Tồn kho hiện tại</h3><p>{{ $products->total() }} mặt hàng · số lượng tính từ phiếu nhập / xuất</p></div>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Sản phẩm</th><th>Đơn vị</th><th>Tồn kho</th><th>Tồn an toàn</th><th>Điểm đặt lại</th><th>Trạng thái</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                     class="product-thumb" alt="{{ $product->name }}">
                                <span><a href="{{ route('products.show', $product->id) }}" class="product-title">{{ $product->name }}</a>
                                    <span class="product-meta">{{ $product->product_code }}<span class="product-meta">Đang về: {{ $product->on_order ?? 0 }} · IP: {{ $product->inventory_position ?? $product->on_hand }} · {{ ['REORDER'=>'Cần xem xét nhập','NORMAL'=>'Bình thường','MISSING_CONFIG'=>'Chưa cấu hình','INSUFFICIENT_DATA'=>'Thiếu dữ liệu','INVALID_DATA'=>'Dữ liệu bất thường'][$product->stock_status] ?? 'Chưa tính' }}</span> · {{ $product->category_name }}</span></span>
                            </div>
                        </td>
                        <td>{{ $product->unit }}</td>
                        <td><strong>{{ $product->on_hand }} {{ $product->unit }}</strong></td>
                        <td>{{ $product->safety_stock ?? '—' }}</td>
                        <td>{{ $product->reorder_point ?? '—' }}</td>
                        <td>
                            @if($product->on_hand <= 0)
                                <span class="badge badge-danger">Hết hàng</span>
                            @elseif($product->reorder_point !== null && $product->on_hand <= $product->reorder_point)
                                <span class="badge badge-info">Cần nhập</span>
                            @elseif($product->on_hand <= max(10, (int) ($product->reorder_point ?? 0) * 1.25))
                                <span class="badge badge-warning">Sắp hết</span>
                            @else
                                <span class="badge badge-success">Đủ hàng</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="6">Không tìm thấy sản phẩm theo điều kiện lọc.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links('pagination.n07') }}
    </section>

    <section class="content section-anchor" id="recent-transactions">
        <div class="table-caption">
            <div><h3>Lịch sử nhập xuất</h3><p>12 phiếu gần nhất · chọn mã phiếu để xem nội dung</p></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mã phiếu</th><th>Loại</th><th>Ngày</th><th>Nhân viên</th><th class="text-right">Giá trị</th><th>Ghi chú</th></tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td><a href="{{ route('inventory.show', $transaction->id) }}" class="product-title">{{ $transaction->transaction_code }}</a></td>
                        <td><span class="badge {{ $transaction->type === 'import' ? 'badge-success' : 'badge-info' }}">{{ $transaction->type === 'import' ? 'Nhập kho' : 'Xuất kho' }}</span></td>
                        <td>{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                        <td>{{ $transaction->staff_name ?? 'Tài khoản đã xóa' }}</td>
                        <td class="text-right">{{ number_format($transaction->total_value, 0, ',', '.') }} đ</td>
                        <td>{{ $transaction->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="6">Chưa có phiếu nhập hoặc xuất nào.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
