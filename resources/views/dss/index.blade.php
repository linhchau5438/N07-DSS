@extends('layouts.app')

@section('title', 'Đề xuất bổ sung hàng')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Đề xuất nhập hàng (DSS)</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Hỗ trợ ra quyết định nhập hàng (DSS)</h2>
        <p>Phân tích mức tiêu thụ, phát hiện nguy cơ thiếu hàng và theo dõi tiến độ đặt hàng thực tế với nhà cung cấp.</p>
    </div>
    <div class="page-actions">
        <form action="{{ route('dss.generate') }}" method="POST" style="display:inline">
            @csrf
            <input type="hidden" name="period_days" value="30">
            <button type="submit" class="btn btn-secondary">
                🔄 Cập nhật tính toán mới
            </button>
        </form>
        <a href="{{ route('dss.history') }}" class="btn btn-secondary">
            📜 Lịch sử quyết định DSS
        </a>
    </div>
</div>

<!-- Workflow Tabs -->
<div class="saas-tabs" id="dss-tabs">
    <button type="button" class="saas-tab active" onclick="switchDssTab(event, 'pane-pending')">
        ⚡ Cần xem xét duyệt
        <span class="tab-badge" style="background:var(--warning-bg);color:var(--warning)">{{ $pendingCount }}</span>
    </button>
    <button type="button" class="saas-tab" onclick="switchDssTab(event, 'pane-approved')">
        📋 Đã duyệt, chờ đặt hàng NCC
        <span class="tab-badge" style="background:var(--primary-subtle);color:var(--primary)">{{ $approvedOrders->count() }}</span>
    </button>
    <button type="button" class="saas-tab" onclick="switchDssTab(event, 'pane-ordered')">
        🚚 Đang chờ NCC giao hàng
        <span class="tab-badge">{{ $openOrders->count() }}</span>
    </button>
    <button type="button" class="saas-tab" onclick="switchDssTab(event, 'pane-all')">
        📦 Tất cả mặt hàng & Trạng thái tồn
        <span class="tab-badge">{{ $products->total() }}</span>
    </button>
</div>

<!-- ==============================================================
     TAB 1: CẦN XEM XÉT DUYỆT (PENDING RECOMMENDATIONS)
     ============================================================== -->
<div id="pane-pending" class="dss-pane">
    @php
        $pendingProducts = collect($products->items())->filter(function($p) {
            return $p->recommendation_status === 'pending' && $p->suggested_quantity > 0;
        });
    @endphp

    @if($pendingProducts->count() > 0)
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 20px;background:var(--warning-bg);border-bottom:1px solid var(--warning-border);display:flex;align-items:center;justify-content:space-between">
                <div>
                    <strong style="color:var(--warning);font-size:13px">Có {{ $pendingProducts->count() }} mặt hàng vị thế tồn kho (IP) rơi dưới ngưỡng đặt hàng lại (ROP)</strong>
                    <div style="font-size:11px;color:#92400e;margin-top:2px">Admin xem xét căn cứ nhu cầu và phê duyệt số lượng nhập phù hợp.</div>
                </div>
            </div>

            <div class="table-wrap" style="border:none">
                <table>
                    <thead>
                        <tr>
                            <th>Mã SKU / Sản phẩm</th>
                            <th class="text-right">Tồn thực tế</th>
                            <th class="text-right">Hàng đang về</th>
                            <th class="text-right">Vị thế tồn (IP)</th>
                            <th class="text-right">Ngưỡng ROP</th>
                            <th class="text-right" style="color:var(--primary)">Lượng đề xuất (Q)</th>
                            <th>Đặc điểm phân tích</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingProducts as $p)
                            @php
                                $snapshot = json_decode($p->calculation_snapshot ?? '{}', true) ?: [];
                                $stockout = $snapshot['stockout_analysis'] ?? [];
                                $actualLt = $snapshot['actual_lead_time'] ?? [];
                                $fallbackApplied = !empty($snapshot['safety_fallback_applied']);
                            @endphp
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <div>
                                            <a href="{{ route('products.show', $p->id) }}" class="product-title" style="color:var(--primary)">
                                                {{ $p->name }}
                                            </a>
                                            <span class="product-meta">SKU: <strong>{{ $p->product_code }}</strong> ({{ $p->unit }}) · {{ $p->category_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <strong class="{{ $p->current_on_hand <= 0 ? 'text-danger' : '' }}">
                                        {{ number_format($p->current_on_hand) }}
                                    </strong>
                                    <small class="text-muted">{{ $p->unit }}</small>
                                </td>
                                <td class="text-right">
                                    {{ ($p->on_order ?? 0) > 0 ? '+' . number_format($p->on_order) : '0' }}
                                </td>
                                <td class="text-right">
                                    <strong>{{ number_format($p->inventory_position ?? $p->current_on_hand) }}</strong>
                                </td>
                                <td class="text-right">
                                    {{ number_format($p->reorder_point, 1) }}
                                </td>
                                <td class="text-right" style="font-size:14px;color:var(--primary)">
                                    <strong>{{ number_format($p->suggested_quantity) }}</strong>
                                    <small style="font-size:11px;color:var(--text-muted)">{{ $p->unit }}</small>
                                </td>
                                <td>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                                        @if($fallbackApplied)
                                            <span class="badge badge-warning" title="Kích hoạt đệm an toàn cơ sở 15% do phương sai nhu cầu xấp xỉ 0">
                                                🛡️ Fallback SS (15%)
                                            </span>
                                        @endif
                                        @if(!empty($stockout['is_censored']))
                                            <span class="badge badge-danger" title="Méo nhu cầu do đứt hàng {{ $stockout['stockout_days'] }} ngày">
                                                ⚠️ Đứt hàng ({{ $stockout['stockout_days'] }} ngày)
                                            </span>
                                        @endif
                                        @if(!empty($actualLt['has_history']) && (int)$actualLt['avg_lead_time_days'] !== (int)$p->lead_time_days)
                                            <span class="badge badge-info" title="Thời gian giao hàng thực tế ({{ $actualLt['avg_lead_time_days'] }} ngày) lệch kế hoạch ({{ $p->lead_time_days }} ngày)">
                                                ⏱️ Giao TB {{ $actualLt['avg_lead_time_days'] }} ngày
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-warn" onclick="openDecisionModal('modal-decide-{{ $p->id }}')">
                                        Xem căn cứ & Duyệt →
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card empty-state">
            <span class="empty-icon">✅</span>
            <div class="empty-title">Không có mặt hàng nào cần duyệt nhập lúc này</div>
            <p class="empty-desc">Tất cả các sản phẩm đều đang có lượng tồn kho hoặc hàng đang về nằm trên mức an toàn (IP > ROP).</p>
        </div>
    @endif
</div>

<!-- ==============================================================
     TAB 2: ĐÃ DUYỆT, CHỜ ĐẶT HÀNG NCC (APPROVED ORDERS)
     ============================================================== -->
<div id="pane-approved" class="dss-pane" style="display:none">
    @if($approvedOrders->count() > 0)
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 20px;background:var(--primary-subtle);border-bottom:1px solid var(--primary-border);display:flex;align-items:center;justify-content:space-between">
                <div>
                    <strong style="color:var(--primary-text);font-size:13px">Có {{ $approvedOrders->count() }} đề xuất đã được Admin phê duyệt, chờ đặt hàng với Nhà cung cấp</strong>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Sau khi liên hệ NCC và chốt hợp đồng/đơn hàng, thực hiện "Xác nhận đặt hàng" để ghi nhận hàng đang về (On Order).</div>
                </div>
            </div>

            <div class="table-wrap" style="border:none">
                <table>
                    <thead>
                        <tr>
                            <th style="width:80px">Mã đề xuất</th>
                            <th>Sản phẩm / SKU</th>
                            <th class="text-right">Số lượng đề xuất</th>
                            <th class="text-right" style="color:var(--primary)">Số lượng đã duyệt</th>
                            <th>Ghi chú của Admin</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedOrders as $ao)
                            <tr>
                                <td><span class="badge badge-muted">#{{ $ao->id }}</span></td>
                                <td>
                                    <strong style="color:var(--text-main)">{{ $ao->name }}</strong>
                                    <span class="product-meta">SKU: {{ $ao->product_code }} ({{ $ao->unit }})</span>
                                </td>
                                <td class="text-right text-muted">{{ number_format($ao->suggested_quantity) }} {{ $ao->unit }}</td>
                                <td class="text-right" style="font-size:14px;color:var(--primary)">
                                    <strong>{{ number_format($ao->approved_quantity) }}</strong> {{ $ao->unit }}
                                </td>
                                <td class="text-muted" style="max-width:280px">
                                    {{ $ao->decision_note ?: 'Duyệt theo đề xuất DSS.' }}
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm" onclick="openOrderModal('modal-order-{{ $ao->id }}')">
                                        📞 Xác nhận đặt hàng NCC →
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card empty-state">
            <span class="empty-icon">📋</span>
            <div class="empty-title">Không có đơn hàng nào đang chờ đặt</div>
            <p class="empty-desc">Tất cả đề xuất đã duyệt đều đã được chuyển thành đơn đặt hàng thực tế với nhà cung cấp.</p>
        </div>
    @endif
</div>

<!-- ==============================================================
     TAB 3: ĐANG CHỜ NCC GIAO HÀNG (OPEN ORDERS - ON ORDER)
     ============================================================== -->
<div id="pane-ordered" class="dss-pane" style="display:none">
    @if($openOrders->count() > 0)
        <div class="card" style="padding:0;overflow:hidden">
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 class="card-title">Tiến độ đơn hàng nhà cung cấp đang giao (Hàng đang về - On Order)</h3>
                    <div class="card-subtitle">Hàng đang trên đường vận chuyển, tính vào vị thế tồn kho (IP) để tránh đặt trùng lặp</div>
                </div>
            </div>

            <div class="table-wrap" style="border:none">
                <table>
                    <thead>
                        <tr>
                            <th style="width:80px">Mã đề xuất</th>
                            <th>Sản phẩm / SKU</th>
                            <th>Nhà cung cấp</th>
                            <th>Hẹn giao dự kiến</th>
                            <th class="text-right">Đã đặt NCC</th>
                            <th class="text-right">Đã nhận kho</th>
                            <th class="text-right">Còn phải nhận</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openOrders as $oo)
                            <tr>
                                <td><span class="badge badge-muted">#{{ $oo->id }}</span></td>
                                <td>
                                    <strong style="color:var(--text-main)">{{ $oo->name }}</strong>
                                    <span class="product-meta">SKU: {{ $oo->product_code }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $oo->supplier_name ?: 'NCC mặc định' }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ $oo->expected_delivery_date ? \Illuminate\Support\Carbon::parse($oo->expected_delivery_date)->format('d/m/Y') : 'Chưa hẹn ngày' }}
                                </td>
                                <td class="text-right"><strong>{{ number_format($oo->ordered_quantity) }}</strong> {{ $oo->unit }}</td>
                                <td class="text-right">{{ number_format($oo->received_quantity) }} {{ $oo->unit }}</td>
                                <td class="text-right font-mono" style="color:var(--warning)">
                                    <strong>{{ number_format($oo->ordered_quantity - $oo->received_quantity) }}</strong> {{ $oo->unit }}
                                </td>
                                <td class="text-right">
                                    <div style="display:inline-flex;gap:4px">
                                        <a href="{{ route('inventory.create', ['type' => 'import', 'recommendation_id' => $oo->id]) }}" class="btn btn-sm">
                                            📥 Nhập kho theo đơn
                                        </a>
                                        <form action="{{ route('dss.cancel', $oo->id) }}" method="POST" style="display:inline"
                                              onsubmit="return confirm('Hủy phần đơn hàng còn lại này? Số lượng chưa nhận sẽ được trừ khỏi On Order và IP.')">
                                            @csrf
                                            <input type="hidden" name="reason" value="Hủy đơn do nhà cung cấp không thể giao tiếp">
                                            <button type="submit" class="btn btn-sm btn-quiet" style="color:var(--danger)">
                                                Hủy phần còn lại
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card empty-state">
            <span class="empty-icon">🚚</span>
            <div class="empty-title">Không có đơn hàng nào đang trên đường vận chuyển</div>
            <p class="empty-desc">Kho hiện không có đơn hàng nhà cung cấp đang chờ giao.</p>
        </div>
    @endif
</div>

<!-- ==============================================================
     TAB 4: TẤT CẢ MẶT HÀNG & TRẠNG THÁI TỒN
     ============================================================== -->
<div id="pane-all" class="dss-pane" style="display:none">
    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-wrap" style="border:none">
            <table>
                <thead>
                    <tr>
                        <th>Mặt hàng / SKU</th>
                        <th class="text-right">Tồn thực tế</th>
                        <th class="text-right">Hàng đang về</th>
                        <th class="text-right">Vị thế tồn (IP)</th>
                        <th class="text-right">Tồn an toàn (SS)</th>
                        <th class="text-right">Ngưỡng ROP</th>
                        <th class="text-right">Lượng đề xuất (Q)</th>
                        <th class="text-center">Trạng thái đề xuất</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td>
                                <div>
                                    <a href="{{ route('products.show', $p->id) }}" class="product-title" style="color:var(--primary)">
                                        {{ $p->name }}
                                    </a>
                                    <span class="product-meta">SKU: {{ $p->product_code }} ({{ $p->unit }})</span>
                                </div>
                            </td>
                            <td class="text-right">{{ number_format($p->current_on_hand) }}</td>
                            <td class="text-right">{{ ($p->on_order ?? 0) > 0 ? '+' . number_format($p->on_order) : '0' }}</td>
                            <td class="text-right"><strong>{{ number_format($p->inventory_position ?? $p->current_on_hand) }}</strong></td>
                            <td class="text-right">{{ $p->safety_stock !== null ? number_format($p->safety_stock, 1) : '—' }}</td>
                            <td class="text-right">{{ $p->reorder_point !== null ? number_format($p->reorder_point, 1) : '—' }}</td>
                            <td class="text-right">
                                @if(($p->suggested_quantity ?? 0) > 0)
                                    <strong style="color:var(--primary)">{{ number_format($p->suggested_quantity) }}</strong>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $st = $p->recommendation_status;
                                    $badgeClass = match($st) {
                                        'pending' => 'badge-warning',
                                        'approved' => 'badge-info',
                                        'ordered', 'partially_received' => 'badge-primary',
                                        'fulfilled' => 'badge-success',
                                        'rejected', 'cancelled' => 'badge-danger',
                                        default => 'badge-muted'
                                    };
                                    $badgeText = match($st) {
                                        'pending' => 'Chờ Admin duyệt',
                                        'approved' => 'Đã duyệt (Chờ đặt NCC)',
                                        'ordered' => 'Đang chờ NCC giao',
                                        'partially_received' => 'Đã nhận 1 phần',
                                        'fulfilled' => 'Đã nhận đủ',
                                        'rejected' => 'Từ chối',
                                        'cancelled' => 'Đã hủy',
                                        'insufficient_data' => 'Thiếu dữ liệu',
                                        'missing_config' => 'Chưa cấu hình',
                                        default => 'Bình thường'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('products.show', $p->id) }}#tab-dss" class="btn btn-sm btn-secondary" title="Xem hoặc chỉnh sửa tham số">
                                    ⚙️ Thông số
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div style="padding:12px 16px;border-top:1px solid var(--border-light)">
                {{ $products->links('pagination.n07') }}
            </div>
        @endif
    </div>
</div>

<!-- ==============================================================
     MODALS FOR REORDER DECISION (MODAL / SLIDE-OVER DRAWER)
     ============================================================== -->
@foreach($products as $p)
    @php
        $snapshot = json_decode($p->calculation_snapshot ?? '{}', true) ?: [];
        $stockout = $snapshot['stockout_analysis'] ?? [];
        $actualLt = $snapshot['actual_lead_time'] ?? [];
        $fallbackApplied = !empty($snapshot['safety_fallback_applied']);
    @endphp

    <!-- Modal Xem căn cứ & Duyệt đề xuất -->
    <div id="modal-decide-{{ $p->id }}" class="modal-backdrop">
        <div class="modal-box" style="width:min(100%, 640px)">
            <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div>
                    <h3 style="font-size:16px;color:var(--text-main)">Phê duyệt đề xuất nhập hàng</h3>
                    <div style="font-size:11.5px;color:var(--text-muted)">{{ $p->product_code }} — {{ $p->name }}</div>
                </div>
                <button type="button" class="btn btn-quiet btn-sm" onclick="closeModal('modal-decide-{{ $p->id }}')">✕</button>
            </div>

            <!-- Cảnh báo méo nhu cầu nếu có -->
            @if(!empty($stockout['is_censored']))
                <div style="background:#fff7ed;padding:12px 14px;border-radius:var(--radius-md);margin:14px 0;border-left:4px solid #f97316;color:#9a3412;font-size:12px">
                    <strong>⚠️ Cảnh báo méo nhu cầu (Censored Demand):</strong><br>
                    Sản phẩm hết hàng <strong>{{ $stockout['stockout_days'] }}/{{ $stockout['total_days'] }} ngày</strong> ({{ round($stockout['stockout_ratio'] * 100, 1) }}%).
                    Nhu cầu thực tế khi có hàng là <strong>{{ $stockout['adjusted_demand'] }} {{ $p->unit }}/ngày</strong> (cao hơn mức ghi nhận {{ $p->average_daily_demand }} {{ $p->unit }}/ngày).
                    
                    @if(!empty($snapshot['adjusted_calculation']['quantity']) && (int)$snapshot['adjusted_calculation']['quantity'] > 0)
                        <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #fdba74;display:flex;align-items:center;justify-content:space-between">
                            <span>💡 Đề xuất hiệu chỉnh bù đắp: <strong>{{ $snapshot['adjusted_calculation']['quantity'] }} {{ $p->unit }}</strong></span>
                            @if(auth()->user()->role === 'admin')
                                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('approved-qty-{{ $p->id }}').value='{{ $snapshot['adjusted_calculation']['quantity'] }}';document.getElementById('decision-note-{{ $p->id }}').value='Duyệt số lượng hiệu chỉnh bù đứt hàng (Censored Demand)'">
                                    Áp dụng số này
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Căn cứ tính toán tóm tắt -->
            <div style="background:var(--bg-subtle);padding:14px;border-radius:var(--radius-md);margin:14px 0;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px">
                <div>
                    <span class="text-muted">Nhu cầu tiêu thụ trung bình (d):</span>
                    <strong style="display:block">{{ number_format($p->average_daily_demand, 2) }} {{ $p->unit }}/ngày</strong>
                </div>
                <div>
                    <span class="text-muted">Lead Time: Thời gian giao hàng dự kiến (L):</span>
                    <strong style="display:block">{{ $p->lead_time_days }} ngày</strong>
                </div>
                <div>
                    <span class="text-muted">Tồn kho dự phòng (SS):</span>
                    <strong style="display:block">{{ number_format($p->safety_stock, 1) }} {{ $p->unit }} @if($fallbackApplied) (🛡️ Fallback SS) @endif</strong>
                </div>
                <div>
                    <span class="text-muted">Ngưỡng đặt hàng lại (ROP):</span>
                    <strong style="display:block;color:var(--warning)">{{ number_format($p->reorder_point, 1) }} {{ $p->unit }}</strong>
                </div>
                <div>
                    <span class="text-muted">Tồn kho thực tế (On Hand):</span>
                    <strong style="display:block">{{ number_format($p->current_on_hand) }} {{ $p->unit }}</strong>
                </div>
                <div>
                    <span class="text-muted">Vị thế tồn kho (IP = On Hand + On Order):</span>
                    <strong style="display:block;color:var(--primary)">{{ number_format($p->inventory_position ?? $p->current_on_hand) }} {{ $p->unit }}</strong>
                </div>
            </div>

            <!-- Form phê duyệt của Admin -->
            @if(auth()->user()->role === 'admin' && $p->recommendation_status === 'pending')
                <form action="{{ route('dss.decide', $p->recommendation_id ?? 0) }}" method="POST" style="margin-top:16px">
                    @csrf
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="approved-qty-{{ $p->id }}">Số lượng duyệt nhập ({{ $p->unit }}) *</label>
                            <input id="approved-qty-{{ $p->id }}" type="number" name="approved_quantity" min="1" max="1000000" required
                                   value="{{ $p->suggested_quantity }}" style="font-size:14px;font-weight:700;color:var(--primary)">
                            <span class="form-help">Mặc định điền số lượng đề xuất của hệ thống ({{ $p->suggested_quantity }}).</span>
                        </div>

                        <div class="form-field">
                            <label for="decision-note-{{ $p->id }}">Ghi chú phê duyệt / Điều chỉnh</label>
                            <input id="decision-note-{{ $p->id }}" name="decision_note" maxlength="255"
                                   placeholder="Lý do điều chỉnh hoặc chỉ đạo đặc biệt...">
                            <span class="form-help">Lưu vết vào nhật ký để kiểm tra đối soát.</span>
                        </div>
                    </div>

                    <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px">
                        <button type="submit" name="decision" value="reject" class="btn btn-danger"
                                onclick="return confirm('Bạn có chắc muốn TỪ CHỐI đề xuất nhập hàng này?')">
                            ✕ Từ chối đề xuất
                        </button>
                        <button type="submit" name="decision" value="approve" class="btn">
                            ✓ Phê duyệt đề xuất
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Modal Xác nhận đặt hàng NCC (Cho Approved Orders) -->
    <div id="modal-order-{{ $p->id }}" class="modal-backdrop">
        <div class="modal-box" style="width:min(100%, 540px)">
            <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div>
                    <h3 style="font-size:16px;color:var(--text-main)">Xác nhận đặt hàng với Nhà cung cấp</h3>
                    <div style="font-size:11.5px;color:var(--text-muted)">{{ $p->product_code }} — {{ $p->name }}</div>
                </div>
                <button type="button" class="btn btn-quiet btn-sm" onclick="closeModal('modal-order-{{ $p->id }}')">✕</button>
            </div>

            <form action="{{ route('dss.order', $p->recommendation_id ?? 0) }}" method="POST" style="margin-top:16px">
                @csrf
                <div class="form-grid">
                    <div class="form-field form-field-full">
                        <label for="supplier_name_{{ $p->id }}">Tên nhà cung cấp được chọn *</label>
                        <input id="supplier_name_{{ $p->id }}" name="supplier_name" maxlength="150" required
                               placeholder="Ví dụ: Công ty TNHH Phân phối ABC, Đại lý XYZ...">
                    </div>

                    <div class="form-field">
                        <label for="ordered_qty_{{ $p->id }}">Số lượng đặt thực tế ({{ $p->unit }}) *</label>
                        <input id="ordered_qty_{{ $p->id }}" type="number" name="ordered_quantity" min="1" max="1000000" required
                               value="{{ $p->approved_quantity ?: $p->suggested_quantity }}" style="font-weight:700;color:var(--primary)">
                        <span class="form-help">Số lượng nhà cung cấp đã đồng ý giao.</span>
                    </div>

                    <div class="form-field">
                        <label for="exp_date_{{ $p->id }}">Ngày hẹn giao dự kiến</label>
                        <input id="exp_date_{{ $p->id }}" type="date" name="expected_delivery_date"
                               min="{{ now()->toDateString() }}">
                        <span class="form-help">Hạn nhà cung cấp hứa giao hàng.</span>
                    </div>

                    <div class="form-field form-field-full">
                        <label for="order_note_{{ $p->id }}">Ghi chú đơn hàng / Số hợp đồng</label>
                        <input id="order_note_{{ $p->id }}" name="order_note" maxlength="255"
                               placeholder="Số PO, số điện thoại tài xế hoặc yêu cầu quy cách...">
                    </div>
                </div>

                <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-order-{{ $p->id }}')">Hủy bỏ</button>
                    <button type="submit" class="btn">
                        📞 Xác nhận đã đặt hàng (Ghi nhận On Order)
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    function switchDssTab(e, paneId) {
        document.querySelectorAll('#dss-tabs .saas-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.dss-pane').forEach(p => p.style.display = 'none');
        e.currentTarget.classList.add('active');
        document.getElementById(paneId).style.display = 'block';
    }

    function openDecisionModal(modalId) {
        document.getElementById(modalId)?.classList.add('is-open');
    }

    function openOrderModal(modalId) {
        document.getElementById(modalId)?.classList.add('is-open');
    }

    function closeModal(modalId) {
        document.getElementById(modalId)?.classList.remove('is-open');
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.remove('is-open');
        }
    });
</script>
@endpush
@endsection
