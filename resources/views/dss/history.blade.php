@extends('layouts.app')

@section('title', 'Lịch sử đề xuất & quyết định DSS')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('dss.index') }}">Đề xuất nhập hàng</a>
    <span class="sep">/</span>
    <span class="current">Lịch sử quyết định</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Lịch sử tính toán đề xuất & Quyết định nhập hàng</h2>
        <p>Theo dõi toàn bộ vết tính toán, căn cứ nhu cầu và các quyết định phê duyệt / đặt hàng qua các đợt.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="{{ route('dss.index') }}">← Quay lại đề xuất</a>
        @if(auth()->user()->role === 'admin')
            <a class="btn btn-secondary" href="{{ route('audit.index') }}">
                <svg class="ui-icon"><use href="#icon-audit"/></svg> Nhật ký kiểm toán
            </a>
        @endif
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th style="width:80px">Mã / Ngày</th>
                    <th>Sản phẩm / SKU</th>
                    <th class="text-center">Trạng thái xử lý</th>
                    <th class="text-right">Đề xuất (Q)</th>
                    <th class="text-right">Admin duyệt</th>
                    <th class="text-right">Đặt thực tế</th>
                    <th class="text-right">Đã nhận kho</th>
                    <th>Người duyệt & Ghi chú</th>
                    <th class="text-center">Chi tiết căn cứ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    @php
                        $st = $rec->status;
                        $badgeClass = match($st) {
                            'pending' => 'badge-warning',
                            'approved' => 'badge-info',
                            'ordered', 'partially_received' => 'badge-primary',
                            'fulfilled' => 'badge-success',
                            'rejected', 'cancelled' => 'badge-danger',
                            default => 'badge-muted'
                        };
                        $badgeText = match($st) {
                            'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt (Chờ đặt)',
                            'ordered' => 'Đã đặt NCC (Đang về)',
                            'partially_received' => 'Đã nhận một phần',
                            'fulfilled' => 'Đã nhận đủ',
                            'rejected' => 'Từ chối',
                            'cancelled' => 'Đã hủy',
                            default => $st
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong style="color:var(--text-main)">#{{ $rec->id }}</strong>
                            <div class="product-meta">{{ \Illuminate\Support\Carbon::parse($rec->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('products.show', $rec->product_id) }}" class="product-title" style="color:var(--primary)">
                                {{ $rec->name }}
                            </a>
                            <span class="product-meta">SKU: {{ $rec->product_code }} ({{ $rec->unit ?? '' }})</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        </td>
                        <td class="text-right font-mono">
                            <strong>{{ number_format($rec->suggested_quantity) }}</strong>
                        </td>
                        <td class="text-right font-mono" style="color:var(--primary)">
                            {{ $rec->approved_quantity !== null ? number_format($rec->approved_quantity) : '—' }}
                        </td>
                        <td class="text-right font-mono">
                            {{ $rec->ordered_quantity !== null ? number_format($rec->ordered_quantity) : '—' }}
                        </td>
                        <td class="text-right font-mono" style="color:var(--success)">
                            <strong>{{ number_format($rec->received_quantity ?? 0) }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $rec->reviewer ?? 'Tự động' }}</strong>
                                @if($rec->reviewed_at)
                                    <small class="text-muted">({{ \Illuminate\Support\Carbon::parse($rec->reviewed_at)->format('d/m H:i') }})</small>
                                @endif
                            </div>
                            @if($rec->decision_note)
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $rec->decision_note }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <details style="display:inline-block;text-align:left">
                                <summary style="cursor:pointer;color:var(--primary);font-weight:600;font-size:11.5px">
                                    🔍 Xem căn cứ
                                </summary>
                                <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;display:grid;place-items:center;padding:20px" onclick="if(event.target===this)this.closest('details').removeAttribute('open')">
                                    <div class="card" style="width:min(100%, 560px);max-height:85vh;overflow-y:auto;background:#fff;padding:24px" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:1px solid var(--border)">
                                            <h4 style="font-size:15px;color:var(--text-main)">Chi tiết căn cứ đề xuất #{{ $rec->id }}</h4>
                                            <button type="button" class="btn btn-quiet btn-sm" onclick="this.closest('details').removeAttribute('open')">✕</button>
                                        </div>
                                        <div style="margin:16px 0;font-size:12px;line-height:1.6">
                                            <strong>Giải thích thuật toán:</strong>
                                            <p style="margin:4px 0 12px;color:var(--text-muted)">{{ $rec->explanation ?: 'Hệ thống tự động đề xuất dựa trên mô hình kiểm soát tồn kho ROP.' }}</p>
                                            
                                            <strong>Dữ liệu snapshot kỹ thuật:</strong>
                                            <pre class="audit-json" style="max-height:240px;overflow-y:auto;background:var(--bg-subtle);padding:10px;border-radius:var(--radius-md);font-size:11px">{{ json_encode(json_decode($rec->calculation_snapshot ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <span class="empty-icon">📜</span>
                            <div class="empty-title">Chưa có lịch sử đề xuất nào</div>
                            <p class="empty-desc">Các đề xuất sau khi tính toán và duyệt sẽ xuất hiện tại đây.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
        <div style="padding:12px 16px;border-top:1px solid var(--border-light)">
            {{ $records->links('pagination.n07') }}
        </div>
    @endif
</div>
@endsection
