@extends('layouts.app')

@section('title', 'Tra cứu tồn kho')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Tồn kho hiện tại</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Tra cứu tồn kho & vị thế hàng hóa</h2>
        <p>Theo dõi số lượng tồn thực tế trong kho, hàng đang chờ nhà cung cấp giao và ngưỡng cảnh báo bổ sung.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.bc01') }}" class="btn btn-secondary">
            Báo cáo cảnh báo ROP →
        </a>
        <a href="{{ route('inventory.create', 'import') }}" class="btn">
            ＋ Lập phiếu nhập kho
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-head">
            <span class="kpi-label">Mặt hàng đang quản lý</span>
            <span class="kpi-icon info">📦</span>
        </div>
        <div class="kpi-val">{{ number_format($metrics->active_product_count) }}</div>
        <div class="kpi-sub">
            <span>Đang hoạt động trong kho</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-head">
            <span class="kpi-label">Ước tính giá trị tồn kho</span>
            <span class="kpi-icon">💰</span>
        </div>
        <div class="kpi-val" style="font-size:20px">{{ number_format($metrics->inventory_value, 0, ',', '.') }} đ</div>
        <div class="kpi-sub">
            <span>Tính theo giá nhập vốn gần nhất</span>
        </div>
    </div>

    <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="kpi-card">
        <div class="kpi-head">
            <span class="kpi-label">Cần chú ý / Thiếu hàng</span>
            <span class="kpi-icon {{ $metrics->low_stock_count > 0 ? 'warning' : 'info' }}">⚠️</span>
        </div>
        <div class="kpi-val text-danger">{{ number_format($metrics->low_stock_count) }}</div>
        <div class="kpi-sub">
            <span>Dưới ngưỡng an toàn hoặc hết hàng</span>
        </div>
    </a>

    <a href="{{ route('dss.index') }}" class="kpi-card">
        <div class="kpi-head">
            <span class="kpi-label">Đề xuất nhập hàng DSS</span>
            <span class="kpi-icon {{ $metrics->reorder_count > 0 ? 'warning' : 'info' }}">🚚</span>
        </div>
        <div class="kpi-val" style="color:var(--primary)">{{ number_format($metrics->reorder_count) }}</div>
        <div class="kpi-sub">
            <span>Vị thế tồn kho (IP) ≤ Ngưỡng ROP</span>
        </div>
    </a>
</div>

<!-- Banner if suggestions exist -->
@if($metrics->reorder_count > 0)
    <div style="background:var(--warning-bg);border:1px solid var(--warning-border);border-radius:var(--radius-md);padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:20px">⚡</span>
            <div>
                <strong style="color:var(--warning);font-size:13px">Hệ thống phát hiện {{ number_format($metrics->reorder_count) }} mặt hàng chạm ngưỡng cần nhập (ROP)</strong>
                <div style="font-size:11.5px;color:#92400e">Xem xét duyệt đề xuất để chuyển thành đơn đặt hàng thực tế với nhà cung cấp.</div>
            </div>
        </div>
        <a href="{{ route('dss.index') }}" class="btn btn-sm btn-warn">
            Xem danh sách đề xuất →
        </a>
    </div>
@endif

<!-- Search & Filters Toolbar -->
<form action="{{ route('inventory.index') }}" method="GET" class="filters">
    <div class="field-search">
        <input type="search" name="search" value="{{ $search }}"
               placeholder="⌕  Tìm theo tên mặt hàng hoặc mã SKU..."
               aria-label="Tìm kiếm">
    </div>

    <select name="category_id" onchange="this.form.submit()">
        <option value="">Tất cả danh mục</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <select name="stock" onchange="this.form.submit()">
        <option value="all" {{ ($filters['stock'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả tình trạng</option>
        <option value="attention" {{ ($filters['stock'] ?? '') === 'attention' ? 'selected' : '' }}>⚠️ Cần chú ý (Hết hoặc tồn thấp)</option>
        <option value="out" {{ ($filters['stock'] ?? '') === 'out' ? 'selected' : '' }}>🚨 Hết hàng (Tồn = 0)</option>
        <option value="low" {{ ($filters['stock'] ?? '') === 'low' ? 'selected' : '' }}>📉 Tồn thấp (1 – 10)</option>
        <option value="REORDER" {{ ($filters['stock'] ?? '') === 'REORDER' ? 'selected' : '' }}>🚚 DSS: Cần xem xét nhập (IP ≤ ROP)</option>
        <option value="NORMAL" {{ ($filters['stock'] ?? '') === 'NORMAL' ? 'selected' : '' }}>✅ DSS: Mức tồn bình thường</option>
    </select>

    <button type="submit" class="btn btn-secondary">Lọc dữ liệu</button>
    @if($search || !empty($filters['category_id']) || (($filters['stock'] ?? 'all') !== 'all'))
        <a href="{{ route('inventory.index') }}" class="btn btn-quiet">Xóa bộ lọc</a>
    @endif
</form>

<!-- Inventory Data Table -->
<div class="card" style="padding:0;overflow:hidden;margin-bottom:24px">
    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm / Mã SKU</th>
                    <th>Danh mục</th>
                    <th class="text-right">Tồn thực tế</th>
                    <th class="text-right">Hàng đang về</th>
                    <th class="text-right">Vị thế tồn (IP)</th>
                    <th class="text-right">Ngưỡng ROP</th>
                    <th class="text-center">Tình trạng tồn</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                     class="product-thumb" alt="{{ $product->name }}">
                                <div>
                                    <a href="{{ route('products.show', $product->id) }}" class="product-title" style="color:var(--primary)">
                                        {{ $product->name }}
                                    </a>
                                    <span class="product-meta">SKU: <strong>{{ $product->product_code }}</strong> ({{ $product->unit }})</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-muted">{{ $product->category_name }}</span>
                        </td>
                        <td class="text-right">
                            <strong class="{{ $product->on_hand <= 0 ? 'text-danger' : '' }}" style="font-size:13.5px">{{ number_format($product->on_hand) }} {{ $product->unit }}</strong>
                        </td>
                        <td class="text-right">
                            @if(($product->on_order ?? 0) > 0)
                                <span class="badge badge-warning" title="Hàng nhà cung cấp đang giao">
                                    +{{ number_format($product->on_order) }} {{ $product->unit }}
                                </span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <strong style="color:var(--text-main)">{{ number_format($product->inventory_position ?? $product->on_hand) }}</strong>
                        </td>
                        <td class="text-right">
                            {{ $product->reorder_point !== null ? number_format($product->reorder_point, 1) : '—' }}
                        </td>
                        <td class="text-center">
                            @if($product->on_hand <= 0)
                                <span class="badge badge-danger">Hết hàng</span>
                            @elseif($product->reorder_point !== null && ($product->inventory_position ?? $product->on_hand) <= $product->reorder_point)
                                <span class="badge badge-warning">Cần nhập (≤ ROP)</span>
                            @elseif($product->on_hand <= 10)
                                <span class="badge badge-warning">Tồn thấp</span>
                            @else
                                <span class="badge badge-success">Đủ hàng</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-secondary" title="Xem chi tiết">
                                    Chi tiết
                                </a>
                                <a href="{{ route('inventory.create', ['type' => 'import', 'product_id' => $product->id]) }}" class="btn btn-sm btn-secondary" title="Nhập thêm hàng">
                                    ＋ Nhập
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <span class="empty-icon">🔍</span>
                            <div class="empty-title">Không tìm thấy mặt hàng nào</div>
                            <p class="empty-desc">Thử kiểm tra lại từ khóa tìm kiếm hoặc bỏ bớt các điều kiện lọc.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div style="padding:12px 16px;border-top:1px solid var(--border-light)">
            {{ $products->links('pagination.n07') }}
        </div>
    @endif
</div>

<!-- Recent Transactions Section -->
<section class="card" id="recent-transactions" style="padding:0;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 class="card-title">📜 Lịch sử nhập - xuất kho gần đây</h3>
            <div class="card-subtitle">12 phiếu giao dịch mới nhất được ghi nhận</div>
        </div>
        <div class="page-actions">
            <a href="{{ route('inventory.create', 'import') }}" class="btn btn-sm btn-secondary">＋ Lập phiếu nhập</a>
            <a href="{{ route('inventory.create', 'export') }}" class="btn btn-sm btn-secondary">− Lập phiếu xuất</a>
        </div>
    </div>

    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Loại giao dịch</th>
                    <th>Ngày ghi nhận</th>
                    <th>Thủ kho thực hiện</th>
                    <th class="text-right">Tổng giá trị</th>
                    <th>Ghi chú / Số chứng từ</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td>
                            <a href="{{ route('inventory.show', $tx->id) }}" class="product-title" style="color:var(--primary)">
                                {{ $tx->transaction_code }}
                            </a>
                        </td>
                        <td>
                            @if($tx->type === 'import')
                                <span class="badge badge-success">📥 Nhập kho</span>
                            @else
                                <span class="badge badge-info">📤 Xuất kho</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                        <td>{{ $tx->staff_name ?? 'Hệ thống' }}</td>
                        <td class="text-right font-mono">
                            <strong>{{ number_format($tx->total_value, 0, ',', '.') }} đ</strong>
                        </td>
                        <td class="text-muted" style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $tx->note ?: ($tx->reference_no ? 'Số CT: ' . $tx->reference_no : '—') }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('inventory.show', $tx->id) }}" class="btn btn-sm btn-secondary">
                                Xem phiếu
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            Chưa có giao dịch nhập hoặc xuất nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
