@extends('layouts.app')

@section('title', 'Dự báo nhu cầu tiêu thụ (MA & ML)')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('reports.index') }}">Báo cáo</a>
    <span class="sep">/</span>
    <span class="current">Dự báo nhu cầu</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Đánh giá & Dự báo Nhu cầu Xuất kho (BC05)</h2>
        <p>So sánh độ chính xác giữa mô hình Trung bình trượt (Moving Average) và Học máy hồi quy xu hướng (Linear Regression).</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="{{ route('reports.index') }}">← Tất cả báo cáo</a>
    </div>
</div>

<!-- Quick Navigation Tabs Across Reports -->
<div class="saas-tabs">
    <a class="saas-tab" href="{{ route('reports.bc01') }}">Cảnh báo bổ sung (BC01)</a>
    <a class="saas-tab" href="{{ route('reports.bc02') }}">Nhập – xuất – tồn (BC02)</a>
    <a class="saas-tab" href="{{ route('reports.bc03') }}">Đề xuất nhập hàng (BC03)</a>
    <a class="saas-tab" href="{{ route('reports.bc04') }}">Rủi ro tồn & Chậm luân chuyển (BC04)</a>
    <a class="saas-tab active" href="{{ route('reports.bc05') }}">Dự báo nhu cầu MA & ML (BC05)</a>
    <a class="saas-tab" href="{{ route('reports.olap') }}">Phân tích kho đa chiều (OLAP)</a>
</div>

<!-- Overview Guidance & Filter -->
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="max-width:680px">
            <h3 class="card-title">Phân tích nhu cầu xuất hàng</h3>
            <p style="font-size:12px;color:var(--text-muted);line-height:1.6;margin-top:4px">
                Mỗi sản phẩm được đánh giá riêng theo đơn vị tính của nó. Moving Average và Linear Regression được so sánh đối chuẩn. Linear Regression học xu hướng theo thời gian và chỉ được tự động chọn khi cả hai chỉ số sai số <strong>MAE</strong> và <strong>RMSE</strong> đều thấp hơn MA trên cùng 7 ngày kiểm định độc lập.
            </p>
            <p style="font-size:11.5px;color:var(--text-muted);line-height:1.5;margin-top:4px">
                Chưa đủ dữ liệu: cần ít nhất cửa sổ MA + 7 ngày đã kết thúc và ngày bắt đầu quan sát được xác nhận trong cấu hình DSS. Chỉ xuất bán / tiêu thụ được sử dụng; ngày không bán được tính 0.
            </p>
        </div>

        @if(auth()->user()->role === 'admin')
            <form method="POST" action="{{ route('forecast.run') }}" style="display:flex;gap:8px;align-items:flex-end">
                @csrf
                <div class="form-field">
                    <label for="eval_product_id">Chạy đánh giá cho sản phẩm</label>
                    <select id="eval_product_id" name="product_id" required style="min-width:200px">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                {{ $product->product_code }} — {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn">
                    🚀 Huấn luyện & Đánh giá
                </button>
            </form>
        @endif
    </div>

    <form method="GET" class="filters" style="margin:16px 0 0;padding:10px 14px;background:var(--bg-subtle)">
        <div class="form-field" style="flex:1">
            <label for="filter_product_id">Lọc lịch sử đánh giá theo sản phẩm</label>
            <select id="filter_product_id" name="product_id" onchange="this.form.submit()">
                <option value="">Tất cả mặt hàng đã đánh giá</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                        {{ $product->product_code }} — {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request('product_id'))
            <a href="{{ route('reports.bc05') }}" class="btn btn-quiet" style="margin-top:auto">Xóa lọc</a>
        @endif
    </form>
</div>

<!-- Evaluation Runs List -->
@forelse($runs as $run)
    @php
        $result = json_decode($run->results, true) ?: [];
        $peak = max(1, max($result['daily'] ?? [1]));
        $selectedModel = $result['selected'] ?? 'ma';
    @endphp
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <h3 class="card-title">{{ $run->product_code }} — {{ $run->name }}</h3>
                    <span class="badge {{ $selectedModel === 'linear' ? 'badge-primary' : 'badge-info' }}">
                        Mô hình tối ưu: {{ $selectedModel === 'linear' ? 'Hồi quy xu hướng (Linear Trend)' : 'Trung bình trượt (Moving Average)' }}
                    </span>
                </div>
                <div class="card-subtitle">
                    Kỳ dữ liệu quan sát: {{ \Illuminate\Support\Carbon::parse($run->period_start)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($run->period_end)->format('d/m/Y') }} · 
                    Cửa sổ MA: {{ $run->ma_days }} ngày · {{ $run->model_version }} · 
                    Đánh giá lúc: {{ \Illuminate\Support\Carbon::parse($run->created_at)->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Daily Trend Bar Chart -->
        <div style="margin:16px 0">
            <div style="font-size:11.5px;font-weight:600;color:var(--text-muted);margin-bottom:8px">
                Lịch sử tiêu thụ hàng ngày trong kỳ (7 ngày cuối là tập kiểm định mô hình):
            </div>
            <div style="display:flex;align-items:flex-end;gap:3px;height:120px;padding:8px 0;border-bottom:1px solid var(--border-light);overflow-x:auto" role="img" aria-label="Biểu đồ tiêu thụ">
                @foreach(($result['daily'] ?? []) as $offset => $qty)
                    @php
                        $dayDate = \Illuminate\Support\Carbon::parse($run->period_start)->addDays($offset)->format('d/m');
                        $isTestSet = $offset >= (count($result['daily']) - 7);
                    @endphp
                    <div style="flex:1;min-width:10px;height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center"
                         title="{{ $dayDate }}: {{ $qty }} {{ $run->unit }} ({{ $isTestSet ? 'Tập kiểm định' : 'Tập huấn luyện' }})">
                        <div style="width:100%;max-width:14px;background:{{ $isTestSet ? '#f59e0b' : '#3b82f6' }};border-radius:2px 2px 0 0;height:{{ max(4, ($qty / $peak) * 100) }}%"></div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:16px;margin-top:8px;font-size:11px;color:var(--text-muted)">
                <span style="display:flex;align-items:center;gap:5px"><span style="width:8px;height:8px;background:#3b82f6;border-radius:2px"></span> Dữ liệu lịch sử</span>
                <span style="display:flex;align-items:center;gap:5px"><span style="width:8px;height:8px;background:#f59e0b;border-radius:2px"></span> 7 ngày kiểm thử độc lập</span>
            </div>
        </div>

        <!-- Metric Comparison Table -->
        <div class="table-wrap" style="margin-bottom:12px">
            <table>
                <thead>
                    <tr>
                        <th>Mô hình dự báo</th>
                        <th class="text-right">Sai số tuyệt đối (MAE)</th>
                        <th class="text-right">Sai số căn phương (RMSE)</th>
                        <th class="text-right" style="color:var(--primary)">Nhu cầu dự báo ({{ $run->unit }}/ngày)</th>
                        <th class="text-center">Kết luận lựa chọn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($result['metrics'] ?? []) as $method => $values)
                        @php
                            $isBest = ($method === $selectedModel);
                        @endphp
                        <tr style="{{ $isBest ? 'background:var(--primary-subtle);font-weight:600' : '' }}">
                            <td>
                                <strong>{{ $method === 'ma' ? 'Trung bình trượt (Moving Average)' : 'Học máy Hồi quy tuyến tính (Linear Regression)' }}</strong>
                            </td>
                            <td class="text-right font-mono">{{ number_format($values['mae'], 3) }}</td>
                            <td class="text-right font-mono">{{ number_format($values['rmse'], 3) }}</td>
                            <td class="text-right font-mono" style="font-size:13px;color:var(--primary)">
                                <strong>{{ number_format($values['forecast'], 2) }}</strong> {{ $run->unit }}/ngày
                            </td>
                            <td class="text-center">
                                @if($isBest)
                                    <span class="badge badge-success">✓ Mô hình tối ưu</span>
                                @else
                                    <span class="badge badge-muted">Sai số cao hơn</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Collapsible Technical Verification Audit -->
        <details style="font-size:11.5px;color:var(--text-muted)">
            <summary style="cursor:pointer;color:var(--primary);font-weight:600">
                🔍 Xem dấu vết kỹ thuật & chữ ký toàn vẹn (SHA256)
            </summary>
            <div style="background:var(--bg-subtle);padding:10px 14px;border-radius:var(--radius-md);margin-top:8px">
                <div>Chuỗi tiêu thụ ghi nhận: <code>{{ implode(', ', $result['daily'] ?? []) }}</code></div>
                <div style="margin-top:4px">Chữ ký kiểm toán dữ liệu: <code>{{ $result['series_sha256'] ?? '—' }}</code></div>
            </div>
        </details>
    </div>
@empty
    <div class="card empty-state">
        <span class="empty-icon">📈</span>
        <div class="empty-title">Chưa có kết quả đánh giá mô hình nào</div>
        <p class="empty-desc">Chưa đủ dữ liệu để chạy phân tích hoặc quản trị viên chưa chạy đánh giá cho sản phẩm này.</p>
    </div>
@endforelse

@if($runs->hasPages())
    <div style="margin-top:16px">
        {{ $runs->links() }}
    </div>
@endif
@endsection
