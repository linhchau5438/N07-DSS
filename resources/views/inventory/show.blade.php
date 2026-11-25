@extends('layouts.app')

@section('title', 'Chi tiết phiếu ' . $transaction->transaction_code)

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('inventory.index') }}">Kho hàng</a>
    <span class="sep">/</span>
    <span class="current">{{ $transaction->transaction_code }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <div style="display:flex;align-items:center;gap:10px">
            <h2>{{ $transaction->transaction_code }}</h2>
            <span class="badge {{ $transaction->type === 'import' ? 'badge-success' : 'badge-info' }}" style="font-size:12px;padding:3px 10px">
                {{ $transaction->type === 'import' ? '📥 Phiếu Nhập Kho' : '📤 Phiếu Xuất Kho' }}
            </span>
        </div>
        <p>Ghi nhận ngày {{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }} · Thủ kho thực hiện: <strong>{{ $transaction->staff_name ?? 'Hệ thống' }}</strong></p>
    </div>
    <div class="page-actions">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Danh sách tồn kho</a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            🖨️ In phiếu
        </button>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:20px;max-width:1100px">
    <!-- Voucher Metadata Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Mục đích giao dịch</span>
            </div>
            <div class="kpi-val" style="font-size:16px;margin:8px 0 2px">
                {{ [
                    'consumption' => 'Xuất tiêu thụ / Cấp phát',
                    'sale' => 'Xuất bán hàng',
                    'damage' => 'Xuất hàng hỏng, hủy',
                    'transfer' => 'Xuất điều chuyển',
                    'return_vendor' => 'Xuất trả nhà cung cấp',
                    'adjustment' => 'Điều chỉnh kiểm kê',
                    'receipt' => 'Nhập hàng vào kho',
                    'other' => 'Khác'
                ][$transaction->issue_purpose] ?? ($transaction->issue_purpose ?: 'Nhập kho') }}
            </div>
            <div class="kpi-sub">
                <span>{{ $transaction->type === 'import' ? 'Nhập từ nhà cung cấp' : 'Mục đích xuất kho' }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Số chứng từ tham chiếu</span>
            </div>
            <div class="kpi-val" style="font-size:16px;margin:8px 0 2px">
                {{ $transaction->reference_no ?: '—' }}
            </div>
            <div class="kpi-sub">
                <span>Số hóa đơn / Vận đơn kèm theo</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Tổng giá trị phiếu</span>
            </div>
            <div class="kpi-val" style="font-size:18px;margin:8px 0 2px;color:var(--primary)">
                {{ number_format($transaction->total_value, 0, ',', '.') }} đ
            </div>
            <div class="kpi-sub">
                <span>Tổng tiền hàng trên phiếu</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-head">
                <span class="kpi-label">Ghi chú diễn giải</span>
            </div>
            <div style="font-size:12px;color:var(--text-main);margin-top:8px;font-weight:500">
                {{ $transaction->note ?: 'Không có ghi chú thêm.' }}
            </div>
        </div>
    </div>

    <!-- Admin Purpose Review Tool if Export -->
    @if($transaction->type === 'export' && auth()->user()->role === 'admin')
        <details class="card" style="padding:14px 18px">
            <summary style="cursor:pointer;font-weight:600;color:var(--primary);font-size:12.5px">
                ⚙️ Rà soát & điều chỉnh mục đích xuất kho (Lưu vết Audit Log)
            </summary>
            <form action="{{ route('inventory.purpose', $transaction->id) }}" method="POST" style="margin-top:14px">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-field">
                        <label for="issue_purpose">Mục đích xuất mới</label>
                        <select id="issue_purpose" name="issue_purpose" required>
                            @foreach([
                                'consumption' => 'Xuất tiêu thụ / sử dụng nội bộ / cấp phát',
                                'sale' => 'Xuất bán thương mại',
                                'damage' => 'Xuất hàng hỏng, hủy',
                                'transfer' => 'Xuất điều chuyển',
                                'other' => 'Khác'
                            ] as $key => $label)
                                <option value="{{ $key }}" @selected($transaction->issue_purpose === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="reason">Lý do điều chỉnh (bắt buộc để ghi nhận kiểm toán) *</label>
                        <input id="reason" name="reason" required maxlength="1000" placeholder="Ví dụ: Rà soát lại do nhân viên chọn nhầm mục đích...">
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-sm">Lưu cập nhật mục đích</button>
                </div>
            </form>
        </details>
    @endif

    <!-- Items Table -->
    <section class="card" style="padding:0;overflow:hidden">
        <div class="card-header" style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div>
                <h3 class="card-title">Chi tiết hàng hóa trên phiếu</h3>
                <div class="card-subtitle">Có {{ count($items) }} mặt hàng được xử lý trong đợt này</div>
            </div>
        </div>
        <div class="table-wrap" style="border:none">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px" class="text-center">#</th>
                        <th>Mã SKU</th>
                        <th>Tên sản phẩm</th>
                        <th class="text-right">Số lượng</th>
                        <th class="text-center">ĐVT</th>
                        <th class="text-right">Đơn giá tham chiếu</th>
                        <th class="text-right">Thành tiền</th>
                        <th class="text-center">Liên kết Đơn DSS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td><strong style="color:var(--text-main)">{{ $item->product_code }}</strong></td>
                            <td>
                                @if(!empty($item->product_id))
                                    <a href="{{ route('products.show', $item->product_id) }}" class="product-title" style="color:var(--primary)">
                                        {{ $item->name }}
                                    </a>
                                @else
                                    <span class="product-title" style="color:var(--text-main)">{{ $item->name }}</span>
                                @endif
                            </td>
                            <td class="text-right" style="font-size:13px">
                                <strong>{{ number_format($item->quantity) }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-muted">{{ $item->unit }}</span>
                            </td>
                            <td class="text-right">
                                {{ number_format($item->unit_price, 0, ',', '.') }} đ
                            </td>
                            <td class="text-right font-mono">
                                <strong>{{ number_format($item->line_total, 0, ',', '.') }} đ</strong>
                            </td>
                            <td class="text-center">
                                @if($item->reorder_recommendation_id)
                                    <span class="badge badge-info" title="Liên kết với đề xuất nhập hàng">
                                        #{{ $item->reorder_recommendation_id }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--bg-subtle);font-weight:700">
                        <td colspan="3" class="text-right">Tổng cộng:</td>
                        <td class="text-right">{{ number_format(collect($items)->sum('quantity')) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-right font-mono" style="color:var(--primary);font-size:14px">
                            {{ number_format($transaction->total_value, 0, ',', '.') }} đ
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
</div>
@endsection
