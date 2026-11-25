@extends('layouts.app')

@section('title', 'Tổng quan kho')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Tổng quan</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>
            @if(auth()->user()->role === 'admin')
                Trung tâm Điều hành & Quản lý Kho
            @else
                Bàn làm việc Nghiệp vụ Kho
            @endif
        </h2>
        <p>
            Xin chào <strong>{{ auth()->user()->name }}</strong>. 
            @if(auth()->user()->role === 'admin')
                Dưới đây là tình trạng tồn kho, cảnh báo rủi ro và các đề xuất cần phê duyệt hôm nay.
            @else
                Dưới đây là các nhiệm vụ nhập/xuất kho và đơn hàng nhà cung cấp đang chờ nhận.
            @endif
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('inventory.create', 'import') }}" class="btn">
            ＋ Lập phiếu nhập kho
        </a>
        <a href="{{ route('inventory.create', 'export') }}" class="btn btn-secondary">
            − Lập phiếu xuất kho
        </a>
    </div>
</div>

@if(auth()->user()->role === 'admin')
    <!-- ==========================================
         ADMIN DASHBOARD VIEW
         Focus: Strategic KPIs, Replenishment, Risk, Trend
         ========================================== -->
    <div class="kpi-grid">
        <a href="{{ route('products.index') }}" class="kpi-card" title="Xem danh sách hàng hóa">
            <div class="kpi-head">
                <span class="kpi-label">Tổng mặt hàng</span>
                <span class="kpi-icon info">📦</span>
            </div>
            <div class="kpi-val">{{ number_format($metrics->product_count) }}</div>
            <div class="kpi-sub">
                <span class="text-success">● {{ number_format($metrics->active_product_count) }}</span> đang kinh doanh
            </div>
        </a>

        <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="kpi-card" title="Xem mặt hàng cần chú ý">
            <div class="kpi-head">
                <span class="kpi-label">Nguy cơ thiếu hàng</span>
                <span class="kpi-icon {{ ($metrics->low_stock_count + $metrics->out_of_stock_count) > 0 ? 'warning' : 'info' }}">⚠️</span>
            </div>
            <div class="kpi-val text-danger">{{ number_format($metrics->low_stock_count + $metrics->out_of_stock_count) }}</div>
            <div class="kpi-sub">
                <span>{{ number_format($metrics->out_of_stock_count) }} mặt hàng đã hết</span>
            </div>
        </a>

        <a href="{{ route('dss.index') }}" class="kpi-card" title="Xem đề xuất nhập hàng DSS">
            <div class="kpi-head">
                <span class="kpi-label">Xử lý nhập hàng (DSS)</span>
                <span class="kpi-icon {{ $pendingRecommendations > 0 ? 'warning' : 'info' }}">🚚</span>
            </div>
            <div class="kpi-val" style="color:var(--primary)">{{ number_format($pendingRecommendations) }} <small style="font-size:13px;font-weight:600;color:var(--text-muted)">chờ duyệt</small></div>
            <div class="kpi-sub">
                <span>{{ number_format($approvedOrdersCount) }} chờ đặt NCC · {{ number_format($openOrdersCount) }} đang về</span>
            </div>
        </a>

        <a href="{{ route('reports.bc02') }}" class="kpi-card" title="Xem báo cáo giá trị tồn kho">
            <div class="kpi-head">
                <span class="kpi-label">Giá trị tồn kho ước tính</span>
                <span class="kpi-icon">💰</span>
            </div>
            <div class="kpi-val" style="font-size:20px">{{ number_format($metrics->inventory_value, 0, ',', '.') }} đ</div>
            <div class="kpi-sub">
                <span>Theo giá vốn nhập tham chiếu</span>
            </div>
        </a>
    </div>

    <!-- Admin Operational Sections -->
    <div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:20px;margin-bottom:20px" class="panels">
        <!-- 7-Day Inbound/Outbound Movement -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Biến động nhập – xuất 7 ngày gần nhất</h3>
                    <div class="card-subtitle">Theo dõi lưu lượng hàng hóa luân chuyển qua kho</div>
                </div>
                <a href="{{ route('reports.bc02') }}" class="btn btn-secondary btn-sm">Xem chi tiết BC02 →</a>
            </div>

            @php
                $maxMovement = max(1, collect($chart)->max(fn ($day) => max($day['import'], $day['export'])));
            @endphp
            <div style="display:flex;align-items:flex-end;gap:12px;height:180px;padding:12px 4px 0;border-bottom:1px solid var(--border-light)" role="img" aria-label="Biểu đồ nhập xuất">
                @foreach($chart as $day)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;gap:6px">
                        <div style="display:flex;align-items:flex-end;gap:4px;width:100%;justify-content:center;height:140px">
                            <div style="width:min(16px, 45%);background:#10b981;border-radius:4px 4px 0 0;height:{{ max(4, ($day['import'] / $maxMovement) * 100) }}%" title="Nhập: {{ $day['import'] }}"></div>
                            <div style="width:min(16px, 45%);background:#6366f1;border-radius:4px 4px 0 0;height:{{ max(4, ($day['export'] / $maxMovement) * 100) }}%" title="Xuất: {{ $day['export'] }}"></div>
                        </div>
                        <span style="font-size:10.5px;color:var(--text-muted)">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:18px;margin-top:12px;font-size:11.5px;color:var(--text-muted)">
                <span style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;background:#10b981;border-radius:2px"></span> Nhập kho</span>
                <span style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;background:#6366f1;border-radius:2px"></span> Xuất kho</span>
            </div>
        </section>

        <!-- Priorities & Quick Action Center -->
        <section class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Việc cần xử lý ưu tiên</h3>
                    <div class="card-subtitle">Các quyết định nhập và cảnh báo cần giải quyết</div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px">
                @if($pendingRecommendations > 0)
                    <div style="padding:12px 14px;background:var(--warning-bg);border:1px solid var(--warning-border);border-radius:var(--radius-md)">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <strong style="color:var(--warning);font-size:12.5px">⚡ Có {{ $pendingRecommendations }} đề xuất nhập hàng</strong>
                            <a href="{{ route('dss.index') }}" class="btn btn-sm btn-warn">Xem & Duyệt →</a>
                        </div>
                        <p style="margin:4px 0 0;font-size:11px;color:#92400e">Các mặt hàng có vị thế tồn kho (IP) đã chạm hoặc rơi dưới ngưỡng đặt hàng lại (ROP).</p>
                    </div>
                @endif

                @if($approvedOrdersCount > 0)
                    <div style="padding:12px 14px;background:var(--primary-subtle);border:1px solid var(--primary-border);border-radius:var(--radius-md)">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <strong style="color:var(--primary);font-size:12.5px">📋 Có {{ $approvedOrdersCount }} đề xuất đã duyệt chờ đặt NCC</strong>
                            <a href="{{ route('dss.index') }}" class="btn btn-sm">Chốt đặt hàng →</a>
                        </div>
                        <p style="margin:4px 0 0;font-size:11px;color:var(--primary-text)">Admin đã đồng ý. Cần liên hệ nhà cung cấp và xác nhận đặt để ghi nhận hàng đang về.</p>
                    </div>
                @endif

                @if($metrics->out_of_stock_count > 0)
                    <div style="padding:12px 14px;background:var(--danger-bg);border:1px solid var(--danger-border);border-radius:var(--radius-md)">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <strong style="color:var(--danger);font-size:12.5px">🚨 Có {{ $metrics->out_of_stock_count }} mặt hàng đã hết tồn kho</strong>
                            <a href="{{ route('inventory.index', ['stock' => 'out']) }}" class="btn btn-sm btn-danger">Xem mặt hàng →</a>
                        </div>
                        <p style="margin:4px 0 0;font-size:11px;color:#991b1b">Hàng đã cạn kiệt, có thể gây mất nhu cầu khách hàng (Censored Demand).</p>
                    </div>
                @endif

                @if($pendingRecommendations === 0 && $approvedOrdersCount === 0 && $metrics->out_of_stock_count === 0)
                    <div class="empty-state" style="padding:24px 12px">
                        <span class="empty-icon">✅</span>
                        <div class="empty-title">Kho hàng đang ở trạng thái an toàn</div>
                        <p class="empty-desc">Không có đề xuất nhập gấp hoặc mặt hàng đứt hàng hôm nay.</p>
                    </div>
                @endif
            </div>

            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light);font-size:11px;color:var(--text-muted);display:flex;justify-content:space-between;align-items:center">
                <span>Thuật toán DSS: ROP / EOQ / Fallback SS</span>
                <a href="{{ route('reports.bc04') }}" style="font-weight:600">Kiểm soát rủi ro tồn kho (BC04) →</a>
            </div>
        </section>
    </div>

@else
    <!-- ==========================================
         STAFF DASHBOARD VIEW
         Focus: Daily Task Execution, Receipts, Stock Check
         ========================================== -->
    <div class="kpi-grid">
        <a href="{{ route('inventory.create', 'import') }}" class="kpi-card" style="border-left:4px solid var(--primary)">
            <div class="kpi-head">
                <span class="kpi-label">Nhập kho hôm nay</span>
                <span class="kpi-icon">📥</span>
            </div>
            <div class="kpi-val" style="color:var(--primary)">+ Tạo phiếu</div>
            <div class="kpi-sub">
                <span>Nhận hàng từ NCC hoặc nhập điều chuyển</span>
            </div>
        </a>

        <a href="{{ route('inventory.create', 'export') }}" class="kpi-card" style="border-left:4px solid #6366f1">
            <div class="kpi-head">
                <span class="kpi-label">Xuất kho hôm nay</span>
                <span class="kpi-icon" style="background:#e0e7ff;color:#4f46e5">📤</span>
            </div>
            <div class="kpi-val" style="color:#4f46e5">- Tạo phiếu</div>
            <div class="kpi-sub">
                <span>Xuất tiêu thụ, cấp phát hoặc xuất hủy</span>
            </div>
        </a>

        <a href="{{ route('inventory.index') }}" class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Tổng mặt hàng</span>
                <span class="kpi-icon info">🔍</span>
            </div>
            <div class="kpi-val">{{ number_format($metrics->active_product_count) }} <small style="font-size:12px;font-weight:500;color:var(--text-muted)">mặt hàng</small></div>
            <div class="kpi-sub">
                <span>Kiểm tra số lượng khả dụng tức thời</span>
            </div>
        </a>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Đơn NCC đang chờ nhận</span>
                <span class="kpi-icon warning">🚚</span>
            </div>
            <div class="kpi-val">{{ number_format($openOrdersCount) }} <small style="font-size:12px;font-weight:500;color:var(--text-muted)">đơn</small></div>
            <div class="kpi-sub">
                <span>Hàng đã đặt với nhà cung cấp</span>
            </div>
        </div>
    </div>

    <!-- Staff Receiving Queue -->
    @if($openOrdersList->count() > 0)
        <section class="card" style="margin-bottom:20px;border-left:4px solid #f59e0b">
            <div class="card-header">
                <div>
                    <h3 class="card-title">📦 Đơn hàng nhà cung cấp đang về (Cần kiểm đếm nhập kho)</h3>
                    <div class="card-subtitle">Khi xe giao hàng tới, bấm "Nhập kho theo đơn" để hệ thống tự điền thông tin phiếu nhập</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đề xuất</th>
                            <th>Sản phẩm</th>
                            <th>Đã đặt NCC</th>
                            <th>Đã nhận</th>
                            <th>Còn phải nhận</th>
                            <th>Ngày đặt</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openOrdersList as $order)
                            <tr>
                                <td><span class="badge badge-muted">#{{ $order->id }}</span></td>
                                <td>
                                    <strong>{{ $order->name }}</strong>
                                    <span class="product-meta">{{ $order->product_code }}</span>
                                </td>
                                <td><strong>{{ $order->ordered_quantity }}</strong> {{ $order->unit }}</td>
                                <td>{{ $order->received_quantity }} {{ $order->unit }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ $order->ordered_quantity - $order->received_quantity }} {{ $order->unit }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $order->ordered_at ? \Illuminate\Support\Carbon::parse($order->ordered_at)->format('d/m/Y') : '—' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('inventory.create', ['type' => 'import', 'recommendation_id' => $order->id]) }}" class="btn btn-sm">
                                        📥 Nhập kho theo đơn này
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endif

<!-- Shared Operational Section: Low Stock Warning & Recent Transactions -->
@if(count($topSellers) > 0)
<section class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div>
            <h3 class="card-title">Mặt hàng xuất tiêu thụ nhiều trong tháng</h3>
            <div class="card-subtitle">5 sản phẩm có lượng xuất cao nhất 30 ngày qua</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Sản phẩm</th><th>Mã SKU</th><th class="text-right">Tổng lượng xuất</th></tr></thead>
            <tbody>
                @foreach($topSellers as $seller)
                    <tr>
                        <td><strong>{{ $seller->name }}</strong></td>
                        <td>{{ $seller->product_code }}</td>
                        <td class="text-right"><strong>{{ number_format($seller->sold_quantity) }}</strong> {{ $seller->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="panels">
    <!-- Low Stock Alert List -->
    <section class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Mặt hàng tồn kho thấp / cạn kiệt</h3>
                <div class="card-subtitle">Cần chú ý khi lập phiếu xuất</div>
            </div>
            <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="btn btn-secondary btn-sm">Xem tất cả →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-right">Tồn thực tế</th>
                        <th class="text-right">Tình trạng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStock as $item)
                        <tr>
                            <td>
                                <strong class="product-title">{{ $item->name }}</strong>
                                <span class="product-meta">{{ $item->product_code }}</span>
                            </td>
                            <td><span class="badge badge-muted">{{ $item->category_name }}</span></td>
                            <td class="text-right">
                                <strong class="{{ $item->on_hand <= 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ $item->on_hand }} {{ $item->unit }}
                                </strong>
                            </td>
                            <td class="text-right">
                                @if($item->on_hand <= 0)
                                    <span class="badge badge-danger">Hết hàng</span>
                                @else
                                    <span class="badge badge-warning">Tồn thấp</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state" style="padding:24px">
                                Không có mặt hàng nào bị tồn thấp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Recent Transactions Log -->
    <section class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Giao dịch nhập / xuất gần nhất</h3>
                <div class="card-subtitle">6 phiếu ghi nhận mới nhất trong kho</div>
            </div>
            <a href="{{ route('inventory.index') }}#recent-transactions" class="btn btn-secondary btn-sm">Xem lịch sử →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Loại</th>
                        <th>Ngày</th>
                        <th>Thủ kho thực hiện</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td>
                                <a href="{{ route('inventory.show', $tx->id) }}" class="product-title" style="color:var(--primary)">
                                    {{ $tx->transaction_code }}
                                </a>
                            </td>
                            <td>
                                @if($tx->type === 'import')
                                    <span class="badge badge-success">Nhập kho</span>
                                @else
                                    <span class="badge badge-info">Xuất kho</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                            <td>{{ $tx->staff_name ?? 'Hệ thống' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state" style="padding:24px">
                                Chưa có giao dịch nào được ghi nhận.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
