@extends('layouts.app')

@section('title', 'Nhật ký hoạt động & Kiểm toán')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Nhật ký hoạt động</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Nhật ký hoạt động & Kiểm toán nghiệp vụ (Audit Trail)</h2>
        <p>Ghi nhận bất biến toàn bộ các thao tác nhạy cảm: phê duyệt nhập hàng, đặt hàng, hủy đơn và điều chỉnh mục đích xuất kho.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="{{ route('dss.history') }}">Lịch sử DSS →</a>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 class="card-title">Danh sách vết kiểm toán đã ghi nhận</h3>
            <div class="card-subtitle">Tổng số <strong>{{ $logs->total() }}</strong> sự kiện nghiệp vụ</div>
        </div>
    </div>

    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th style="width:70px">ID</th>
                    <th>Hành động thực hiện</th>
                    <th>Thực thể tác động</th>
                    <th>Người thực hiện</th>
                    <th>Thời điểm ghi nhận</th>
                    <th class="text-center">Đối chiếu dữ liệu (Trước / Sau)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted">#{{ $log->id }}</td>
                        <td>
                            @php
                                $actionName = match($log->action) {
                                    'REORDER_DECISION' => 'Phê duyệt đề xuất',
                                    'REORDER_CONFIRM_ORDER' => 'Xác nhận đặt hàng NCC',
                                    'REORDER_CANCEL' => 'Hủy đơn hàng',
                                    'SYNC_ACTUAL_LEAD_TIME' => 'Đồng bộ Lead Time thực tế',
                                    'REVIEW_PURPOSE' => 'Điều chỉnh mục đích xuất kho',
                                    default => $log->action
                                };
                            @endphp
                            <span class="badge badge-info" style="font-weight:700">
                                {{ $actionName }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $log->entity_type }}</strong>
                            @if($log->entity_id)
                                <span class="badge badge-muted">#{{ $log->entity_id }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $log->name ?? 'Hệ thống tự động' }}</strong>
                        </td>
                        <td class="text-muted">
                            {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="text-center">
                            <details style="display:inline-block;text-align:left">
                                <summary style="cursor:pointer;color:var(--primary);font-weight:600;font-size:11.5px">
                                    🔍 Xem đối chiếu
                                </summary>
                                <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;display:grid;place-items:center;padding:20px" onclick="if(event.target===this)this.closest('details').removeAttribute('open')">
                                    <div class="card" style="width:min(100%, 640px);max-height:85vh;overflow-y:auto;background:#fff;padding:24px" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:1px solid var(--border)">
                                            <h4 style="font-size:15px;color:var(--text-main)">Đối chiếu dữ liệu sự kiện #{{ $log->id }}</h4>
                                            <button type="button" class="btn btn-quiet btn-sm" onclick="this.closest('details').removeAttribute('open')">✕</button>
                                        </div>
                                        <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                            <div>
                                                <strong style="font-size:12px;color:var(--text-muted)">Trước khi thay đổi:</strong>
                                                <pre class="audit-json" style="max-height:260px;overflow-y:auto;background:var(--bg-subtle);padding:10px;border-radius:var(--radius-md);font-size:11px">{{ json_encode(json_decode($log->before_data ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            <div>
                                                <strong style="font-size:12px;color:var(--primary)">Sau khi thay đổi:</strong>
                                                <pre class="audit-json" style="max-height:260px;overflow-y:auto;background:var(--bg-subtle);padding:10px;border-radius:var(--radius-md);font-size:11px">{{ json_encode(json_decode($log->after_data ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            Chưa có sự kiện kiểm toán nào được ghi nhận.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div style="padding:12px 16px;border-top:1px solid var(--border-light)">
            {{ $logs->links('pagination.n07') }}
        </div>
    @endif
</div>
@endsection
