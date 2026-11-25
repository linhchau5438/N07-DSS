@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm: ' . $product->name)

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('products.index') }}">Sản phẩm</a>
    <span class="sep">/</span>
    <span class="current">{{ $product->product_code }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div style="display:flex;align-items:center;gap:16px">
        <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
             alt="{{ $product->name }}"
             style="width:52px;height:52px;object-fit:cover;border-radius:var(--radius-md);border:1px solid var(--border);background:#fff">
        <div>
            <h2>{{ $product->name }}</h2>
            <p>
                Mã SKU: <strong>{{ $product->product_code }}</strong> · 
                Danh mục: <span class="badge badge-muted">{{ $product->category_name }}</span> · 
                @if($product->status == 1)
                    <span class="badge badge-success">Đang kinh doanh</span>
                @else
                    <span class="badge badge-muted">Ngừng kinh doanh</span>
                @endif
            </p>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('products.index') }}" class="btn btn-secondary">← Danh sách sản phẩm</a>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('products.edit', $product->id) }}" class="btn">
                Chỉnh sửa thông tin
            </a>
            <form action="{{ route('products.status', $product->id) }}" method="POST"
                  onsubmit="return confirm('{{ $product->status == 1 ? 'Ngừng kinh doanh sản phẩm này?' : 'Mở kinh doanh lại sản phẩm này?' }}')">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ $product->status == 1 ? 0 : 1 }}">
                <button class="btn {{ $product->status == 1 ? 'btn-warn' : 'btn-secondary' }}" type="submit">
                    {{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Kích hoạt lại' }}
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Progressive Tabs -->
<div class="saas-tabs">
    <button type="button" class="saas-tab active" onclick="switchProductTab(event, 'tab-overview')">
        📊 Tổng quan tồn kho & Thông tin
    </button>
    <button type="button" class="saas-tab" onclick="switchProductTab(event, 'tab-history')">
        📜 Lịch sử nhập xuất <span class="tab-badge">{{ count($movements) }}</span>
    </button>
    @if(auth()->user()->role === 'admin')
        <button type="button" class="saas-tab" onclick="switchProductTab(event, 'tab-dss')">
            ⚙️ Cấu hình thông số nhập hàng (DSS)
        </button>
    @endif
</div>

<!-- Tab 1: Tổng quan tồn kho & Thông tin -->
<div id="tab-overview" class="tab-pane">
    <div class="kpi-grid" style="margin-bottom:20px">
        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Tồn kho thực tế</span>
                <span class="kpi-icon info">📦</span>
            </div>
            <div class="kpi-val {{ $product->on_hand <= 0 ? 'text-danger' : '' }}">
                {{ number_format($product->on_hand) }} <small style="font-size:13px;font-weight:600;color:var(--text-muted)">{{ $product->unit }}</small>
            </div>
            <div class="kpi-sub">
                <span>Số lượng kiểm đếm sẵn có trong kho</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Ngưỡng đặt hàng lại (ROP)</span>
                <span class="kpi-icon warning">🎯</span>
            </div>
            <div class="kpi-val">
                {{ $recommendation ? number_format($recommendation->reorder_point, 1) : '—' }} <small style="font-size:13px;font-weight:600;color:var(--text-muted)">{{ $product->unit }}</small>
            </div>
            <div class="kpi-sub">
                <span>Kích hoạt đề xuất khi vị thế tồn kho ≤ ROP</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Tồn kho dự phòng (SS)</span>
                <span class="kpi-icon">🛡️</span>
            </div>
            <div class="kpi-val">
                {{ $recommendation ? number_format($recommendation->safety_stock, 1) : '—' }} <small style="font-size:13px;font-weight:600;color:var(--text-muted)">{{ $product->unit }}</small>
            </div>
            <div class="kpi-sub">
                <span>Đệm an toàn chống biến động nhu cầu & trễ hạn</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Lượng đề xuất nhập (Q)</span>
                <span class="kpi-icon {{ ($recommendation && $recommendation->suggested_quantity > 0) ? 'warning' : 'info' }}">🚚</span>
            </div>
            <div class="kpi-val" style="color:var(--primary)">
                {{ $recommendation ? number_format($recommendation->suggested_quantity) : '0' }} <small style="font-size:13px;font-weight:600;color:var(--text-muted)">{{ $product->unit }}</small>
            </div>
            <div class="kpi-sub">
                @if($recommendation && $recommendation->suggested_quantity > 0)
                    <a href="{{ route('dss.index') }}" style="color:var(--warning);font-weight:700">Xem đề xuất cần duyệt →</a>
                @else
                    <span>Chưa cần nhập bổ sung</span>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="panels">
        <!-- Thông số quy cách -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Quy cách hàng hóa & giá tham chiếu</h3>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:12.5px">
                <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                    <span class="text-muted">Mã định danh SKU</span>
                    <strong>{{ $product->product_code }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                    <span class="text-muted">Danh mục hàng</span>
                    <strong>{{ $product->category_name }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                    <span class="text-muted">Đơn vị tính</span>
                    <strong>{{ $product->unit }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                    <span class="text-muted">Giá vốn nhập gần nhất</span>
                    <strong style="color:var(--primary)">{{ $product->purchase_price ? number_format($product->purchase_price, 0, ',', '.') . ' đ' : '—' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                    <span class="text-muted">Ước tính giá trị tồn kho</span>
                    <strong>{{ number_format($product->on_hand * ($product->purchase_price ?? 0), 0, ',', '.') }} đ</strong>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span class="text-muted">Mô tả hàng hóa</span>
                    <span style="max-width:280px;text-align:right">{{ $product->description ?: 'Chưa có mô tả' }}</span>
                </div>
            </div>
        </section>

        <!-- Trạng thái DSS -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Căn cứ hỗ trợ ra quyết định (DSS)</h3>
                </div>
                <a href="{{ route('dss.index') }}" class="btn btn-secondary btn-sm">Màn hình DSS →</a>
            </div>
            @if($recommendation)
                <div style="background:var(--bg-subtle);padding:14px;border-radius:var(--radius-md);margin-bottom:14px;font-size:12px;line-height:1.6">
                    <strong style="color:var(--text-main);display:block;margin-bottom:4px">Giải thích đề xuất gần nhất:</strong>
                    {{ $recommendation->explanation }}
                </div>
                <div style="font-size:12px;color:var(--text-muted)">
                    Nhu cầu tiêu thụ trung bình quan sát: <strong>{{ number_format($recommendation->average_daily_demand, 2) }} {{ $product->unit }}/ngày</strong>.
                </div>
            @else
                <div class="empty-state" style="padding:24px">
                    <p class="empty-desc">Chưa có đề xuất tính toán gần đây cho sản phẩm này.</p>
                </div>
            @endif
        </section>
    </div>
</div>

<!-- Tab 2: Lịch sử giao dịch nhập/xuất -->
<div id="tab-history" class="tab-pane" style="display:none">
    <section class="card" style="padding:0;overflow:hidden">
        <div class="table-wrap" style="border:none">
            <table>
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Loại giao dịch</th>
                        <th>Ngày ghi nhận</th>
                        <th class="text-right">Số lượng</th>
                        <th>Mục đích / Ghi chú</th>
                        <th>Thủ kho thực hiện</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <a href="{{ route('inventory.show', $m->id) }}" class="product-title" style="color:var(--primary)">
                                    {{ $m->transaction_code }}
                                </a>
                            </td>
                            <td>
                                @if($m->type === 'import')
                                    <span class="badge badge-success">Nhập kho</span>
                                @else
                                    <span class="badge badge-info">Xuất kho</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($m->transaction_date)->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <strong>{{ number_format($m->quantity) }}</strong> {{ $product->unit }}
                            </td>
                            <td class="text-muted">{{ $m->issue_purpose ?? '—' }}</td>
                            <td>{{ $m->staff_name ?? 'Hệ thống' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                Chưa có giao dịch nào được ghi nhận cho sản phẩm này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Tab 3: Cấu hình bổ sung hàng (DSS) -->
@if(auth()->user()->role === 'admin')
<div id="tab-dss" class="tab-pane" style="display:none">
    <section class="card" style="max-width:800px">
        <div class="card-header">
            <div>
                <h3 class="card-title">Thiết lập tham số nhập hàng & Tồn an toàn</h3>
                <div class="card-subtitle">Hệ thống áp dụng các tham số này để tự động tính toán ROP và lượng đặt hàng Q</div>
            </div>
        </div>

        <form action="{{ route('dss.settings', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-field">
                    <label for="lead_time_days">Thời gian giao hàng dự kiến của NCC (Lead Time, ngày) *</label>
                    <input id="lead_time_days" type="number" name="lead_time_days" min="1" max="365" required
                           value="{{ old('lead_time_days', $settings->lead_time_days ?? 7) }}">
                    <span class="form-help">Số ngày từ lúc đặt hàng thành công đến khi hàng về tới kho.</span>
                </div>

                <div class="form-field">
                    <label for="service_level">Mức độ phục vụ mong muốn (Z-Score) *</label>
                    <select id="service_level" name="service_level" required>
                        <option value="0.95" {{ (old('service_level', $settings->service_level ?? '0.95') == '0.95') ? 'selected' : '' }}>95% (Khuyến nghị, Z = 1.65)</option>
                        <option value="0.90" {{ (old('service_level', $settings->service_level ?? '') == '0.90') ? 'selected' : '' }}>90% (Hàng tiêu thụ ổn định, Z = 1.28)</option>
                        <option value="0.99" {{ (old('service_level', $settings->service_level ?? '') == '0.99') ? 'selected' : '' }}>99% (Mặt hàng trọng yếu, Z = 2.33)</option>
                        <option value="0.00" {{ (old('service_level', $settings->service_level ?? '') == '0.00') ? 'selected' : '' }}>0% (Không dùng tồn an toàn, Z = 0, SS = 0)</option>
                    </select>
                    <span class="form-help">Tỷ lệ đáp ứng đơn hàng mong muốn không bị gián đoạn.</span>
                </div>

                <div class="form-field">
                    <label for="safety_mode">Chế độ tồn kho an toàn (Safety Stock Mode) *</label>
                    <select id="safety_mode" name="safety_mode" required>
                        <option value="statistical" {{ (old('safety_mode', $settings->safety_mode ?? 'statistical') == 'statistical') ? 'selected' : '' }}>Thống kê theo phương sai (Statistical)</option>
                        <option value="manual" {{ (old('safety_mode', $settings->safety_mode ?? '') == 'manual') ? 'selected' : '' }}>Mức sàn chỉ định thủ công (Manual Floor)</option>
                    </select>
                    <span class="form-help">Hệ thống luôn có đệm cơ sở (Base Fallback 15%) bảo vệ khi phương sai xấp xỉ 0.</span>
                </div>

                <div class="form-field">
                    <label for="manual_safety_stock">Tồn an toàn tối thiểu chỉ định (Floor SS)</label>
                    <input id="manual_safety_stock" type="number" name="manual_safety_stock" min="0" step="0.1"
                           value="{{ old('manual_safety_stock', $settings->manual_safety_stock ?? 0) }}">
                    <span class="form-help">Mức sàn tối thiểu không thể thấp hơn (đơn vị: {{ $product->unit }}).</span>
                </div>

                <div class="form-field">
                    <label for="pack_size">Quy cách kiện / gói đặt hàng (Pack Size)</label>
                    <input id="pack_size" type="number" name="pack_size" min="1"
                           value="{{ old('pack_size', $settings->pack_size ?? 1) }}">
                    <span class="form-help">Hệ thống sẽ làm tròn số lượng đặt theo bội số của kiện (Ví dụ: 1 thùng = 24 lon).</span>
                </div>

                <div class="form-field">
                    <label for="ma_days">Số ngày quan sát trung bình tiêu thụ (MA Days)</label>
                    <input id="ma_days" type="number" name="ma_days" min="7" max="180"
                           value="{{ old('ma_days', $settings->ma_days ?? 30) }}">
                    <span class="form-help">Khoảng thời gian lịch sử dùng để tính nhu cầu hàng ngày (7 đến 180 ngày).</span>
                </div>
            </div>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);display:flex;gap:10px">
                <button type="submit" class="btn">
                    ✓ Cập nhật cấu hình nhập hàng
                </button>
            </div>
        </form>
    </section>
</div>
@endif

@push('scripts')
<script>
    function switchProductTab(e, tabId) {
        document.querySelectorAll('.saas-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        e.currentTarget.classList.add('active');
        document.getElementById(tabId).style.display = 'block';
    }
</script>
@endpush
@endsection
