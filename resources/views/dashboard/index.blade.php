@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Tổng quan hàng hóa và tình hình kho hôm nay.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>Chào {{ auth()->user()->name }}</h2>
            <p>Đây là những thông tin cần ưu tiên trong cửa hàng.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('inventory.create', 'import') }}" class="btn">＋ Tạo phiếu nhập</a>
            <a href="{{ route('inventory.create', 'export') }}" class="btn btn-secondary">− Tạo phiếu xuất</a>
        </div>
    </div>

    <div class="grid dashboard-metrics">
        <article class="stat-card">
            <span class="stat-label">Tổng mặt hàng</span>
            <strong class="stat-value">{{ number_format($metrics->product_count) }}</strong>
            <span class="stat-meta">{{ number_format($metrics->active_product_count) }} mặt hàng đang kinh doanh</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Sắp hết / hết hàng</span>
            <strong class="stat-value">{{ number_format($metrics->low_stock_count + $metrics->out_of_stock_count) }}</strong>
            <span class="stat-meta">{{ number_format($metrics->out_of_stock_count) }} mặt hàng đã hết</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Sản phẩm cần nhập</span>
            <strong class="stat-value">{{ number_format($reorderCount) }}</strong>
            <span class="stat-meta">{{ number_format($pendingRecommendations) }} gợi ý đang chờ Admin</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Giá trị tồn kho</span>
            <strong class="stat-value">{{ number_format($metrics->inventory_value, 0, ',', '.') }} đ</strong>
            <span class="stat-meta">Ước tính theo giá nhập hiện tại</span>
        </article>
    </div>

    <div class="grid grid-2 dashboard-lower" style="margin-bottom:15px">
        <section class="panel">
            <div class="table-caption">
                <div><h3>Nhập – xuất trong 7 ngày</h3><p>Tổng số lượng hàng ghi nhận mỗi ngày</p></div>
                <a href="{{ route('reports.bc01') }}" class="btn btn-secondary btn-sm">Báo cáo</a>
            </div>
            @php
                $maxMovement = max(1, collect($chart)->max(fn ($day) => max($day['import'], $day['export'])));
            @endphp
            <div class="chart" role="img" aria-label="Biểu đồ số lượng nhập và xuất trong 7 ngày">
                @foreach($chart as $day)
                    <div class="chart-day">
                        <div class="chart-bars">
                            <div class="chart-bar chart-bar-import" title="Nhập: {{ $day['import'] }}"
                                 style="height: {{ max(2, $day['import'] / $maxMovement * 90) }}%"></div>
                            <div class="chart-bar chart-bar-export" title="Xuất: {{ $day['export'] }}"
                                 style="height: {{ max(2, $day['export'] / $maxMovement * 90) }}%"></div>
                        </div>
                        <span class="chart-label">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="chart-legend">
                <span><i class="legend-dot" style="background:#5bbd8c"></i>Nhập kho</span>
                <span><i class="legend-dot" style="background:#6d8fe7"></i>Xuất kho</span>
            </div>
        </section>

        <section class="panel dashboard-priority-panel">
            <div class="table-caption">
                <div><h3>Ưu tiên hôm nay</h3><p>Các việc nên kiểm tra trước</p></div>
            </div>
            @if($reorderCount > 0)
                <a href="{{ route('dss.index') }}" class="priority-notice priority-warning">
                    <strong>{{ $reorderCount }} sản phẩm có đề xuất nhập hàng</strong>
                    <span>{{ $pendingRecommendations }} đề xuất đang chờ Admin duyệt.</span>
                    <b>Xem đề xuất →</b>
                </a>
            @else
                <div class="priority-notice priority-success">
                    <strong>Chưa có gợi ý nhập hàng cần xử lý</strong>
                    <span>Kiểm tra lại sau khi có thêm giao dịch xuất kho.</span>
                </div>
            @endif
            <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="priority-notice priority-muted">
                <strong>{{ number_format($metrics->low_stock_count + $metrics->out_of_stock_count) }} mặt hàng có tồn kho thấp</strong>
                <span>{{ number_format($metrics->out_of_stock_count) }} mặt hàng hiện hết hàng.</span>
                <b>Xem cảnh báo →</b>
            </a>
            <div class="priority-foot">Gợi ý chỉ hỗ trợ quyết định; không tự động tạo phiếu nhập.</div>
        </section>
    </div>

    <div class="grid grid-2 dashboard-lower" style="margin-bottom:15px">
        <section class="panel">
            <div class="table-caption">
                <div><h3>Hoạt động gần đây</h3><p>6 phiếu nhập / xuất mới nhất</p></div>
                <a href="{{ route('inventory.index') }}#recent-transactions" class="btn btn-secondary btn-sm">Lịch sử</a>
            </div>
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th>Phiếu</th><th>Loại</th><th>Ngày</th><th>Người thực hiện</th></tr></thead>
                    <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td><a href="{{ route('inventory.show', $transaction->id) }}" class="product-title">{{ $transaction->transaction_code }}</a></td>
                            <td><span class="badge {{ $transaction->type === 'import' ? 'badge-success' : 'badge-info' }}">{{ $transaction->type === 'import' ? 'Nhập' : 'Xuất' }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                            <td>{{ $transaction->staff_name ?? 'Tài khoản đã xóa' }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="4">Chưa có hoạt động nhập xuất.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="table-caption">
                <div><h3>Hàng xuất nhiều</h3><p>Mặt hàng có lượng xuất cao trong 30 ngày qua</p></div>
                <a href="{{ route('reports.bc03') }}" class="btn btn-secondary btn-sm">Báo cáo</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>Số lượng xuất</th></tr></thead>
                    <tbody>
                    @forelse($topSellers as $product)
                        <tr>
                            <td><span class="product-title">{{ $product->name }}</span><span class="product-meta">{{ $product->product_code }}</span></td>
                            <td><span class="badge badge-info">{{ $product->sold_quantity }} {{ $product->unit }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="2">Chưa có dữ liệu xuất hàng trong kỳ này.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <section class="panel">
            <div class="table-caption">
                <div><h3>Hàng cần chú ý</h3><p>Sản phẩm đang kinh doanh có tồn từ 10 đơn vị trở xuống</p></div>
                <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="btn btn-secondary btn-sm">Tất cả cảnh báo</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>Tồn hiện tại</th><th>Tình trạng</th></tr></thead>
                    <tbody>
                    @forelse($lowStock as $product)
                        <tr>
                            <td><span class="product-title">{{ $product->name }}</span><span class="product-meta">{{ $product->product_code }} · {{ $product->category_name }}</span></td>
                            <td><strong>{{ $product->on_hand }} {{ $product->unit }}</strong></td>
                            <td>
                                @if($product->on_hand <= 0)
                                    <span class="badge badge-danger">Hết hàng</span>
                                @else
                                    <span class="badge badge-warning">Tồn thấp</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="3">Chưa có sản phẩm nào cần cảnh báo.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
