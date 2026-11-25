{{--
    Đường dẫn: resources/views/reports/audit.blade.php
    Mô tả: Giao diện Nhật ký kiểm toán & vết hoạt động nghiệp vụ (Audit Trail) theo Monochrome Design System chuẩn N07.
    Phụ thuộc: AuditController@index, layouts/app.blade.php, pagination/n07.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Nhật ký kiểm toán')
@section('page-title', 'Nhật ký kiểm toán nghiệp vụ')
@section('page-subtitle', 'Lưu vết các thao tác duyệt đề xuất, đặt hàng, hủy đơn và điều chỉnh nghiệp vụ kho.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER --}}
    <x-ui.page-header title="Nhật ký hoạt động & Kiểm toán (Audit Trail)"
                      chip="Ghi nhận bất biến"
                      subtitle="Theo dõi toàn bộ các sự kiện thay đổi dữ liệu nhạy cảm: duyệt đề xuất DSS, xác nhận đặt hàng, hủy đơn và rà soát mục đích xuất.">
        <x-slot:actions>
            <a href="{{ route('reports.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Tất cả báo cáo</span>
            </a>
            <a href="{{ route('dss.history') }}"
               class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-white">history</span>
                <span class="text-white">Lịch sử quyết định DSS</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ --}}
    <x-ui.section-card spacing="space-y-6">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-on-surface">Danh sách sự kiện kiểm toán</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-container-low text-on-surface-variant border border-surface-container-high/60">
                        {{ $logs->total() }} sự kiện
                    </span>
                </div>
            </div>

            <x-ui.data-table minWidth="min-w-[1100px]">
                <x-slot:head>
                    <th class="py-3.5 pl-6 pr-2 min-w-[90px]">ID</th>
                    <th class="py-3.5 px-5 min-w-[200px]">Hành động nghiệp vụ</th>
                    <th class="py-3.5 px-5 min-w-[180px]">Thực thể tác động</th>
                    <th class="py-3.5 px-5 min-w-[160px]">Người thực hiện</th>
                    <th class="py-3.5 px-5 min-w-[160px]">Thời điểm</th>
                    <th class="py-3.5 pr-6 pl-2 text-center min-w-[120px]">Đối chiếu</th>
                </x-slot:head>

                @forelse($logs as $log)
                    @php
                        $actionName = match($log->action) {
                            'REORDER_DECISION' => 'Phê duyệt đề xuất',
                            'CONFIRM_PURCHASE_ORDER', 'REORDER_CONFIRM_ORDER' => 'Xác nhận đặt hàng NCC',
                            'CANCEL_REORDER_RECOMMENDATION', 'REORDER_CANCEL' => 'Hủy đơn hàng',
                            'SYNC_ACTUAL_LEAD_TIME' => 'Đồng bộ Lead Time thực tế',
                            'REVIEW_ISSUE_PURPOSE', 'REVIEW_PURPOSE' => 'Rà soát mục đích xuất kho',
                            default => $log->action
                        };
                        $actionVariant = match($log->action) {
                            'REORDER_DECISION' => 'success',
                            'CONFIRM_PURCHASE_ORDER', 'REORDER_CONFIRM_ORDER' => 'info',
                            'CANCEL_REORDER_RECOMMENDATION', 'REORDER_CANCEL' => 'danger',
                            default => 'neutral'
                        };
                    @endphp
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        <td class="py-4 pl-6 pr-2 font-mono font-bold text-xs text-on-surface-variant">
                            #{{ $log->id }}
                        </td>

                        <td class="py-4 px-5">
                            <x-ui.status-pill :variant="$actionVariant" :label="$actionName" />
                        </td>

                        <td class="py-4 px-5 font-mono text-xs text-on-surface">
                            <span class="font-bold">{{ $log->entity_type }}</span>
                            @if($log->entity_id)
                                <span class="text-on-surface-variant font-semibold">#{{ $log->entity_id }}</span>
                            @endif
                        </td>

                        <td class="py-4 px-5 text-sm font-medium text-on-surface">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-[#1A4D38] flex items-center justify-center text-white font-bold text-[10px] shrink-0">
                                    {{ mb_strtoupper(mb_substr($log->name ?? 'S', 0, 1)) }}
                                </div>
                                <span>{{ $log->name ?? 'Hệ thống tự động' }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-5 text-sm tabular-nums text-on-surface-variant">
                            {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>

                        <td class="py-4 pr-6 pl-2 text-center">
                            <details class="inline-block text-left">
                                <summary class="px-2.5 py-1 rounded-lg bg-surface-container-low text-xs font-semibold text-on-surface hover:bg-surface-container cursor-pointer select-none">
                                    Xem đối chiếu
                                </summary>
                                <div class="fixed inset-0 bg-neutral-900/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this)this.closest('details').removeAttribute('open')">
                                    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-xl border border-surface-container-high space-y-4" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
                                            <h3 class="font-headline-sm text-sm font-bold text-on-surface">Vết kiểm toán sự kiện #{{ $log->id }} ({{ $actionName }})</h3>
                                            <button type="button" onclick="this.closest('details').removeAttribute('open')" class="text-on-surface-variant hover:text-on-surface">✕</button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <strong class="text-on-surface-variant block mb-1">Dữ liệu trước (Before):</strong>
                                                <pre class="p-3 rounded-xl bg-surface-container-low text-on-surface font-mono text-[11px] overflow-x-auto max-h-64">{{ json_encode(json_decode($log->before_data ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            <div>
                                                <strong class="text-secondary block mb-1">Dữ liệu sau (After):</strong>
                                                <pre class="p-3 rounded-xl bg-surface-container-low text-on-surface font-mono text-[11px] overflow-x-auto max-h-64">{{ json_encode(json_decode($log->after_data ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <x-ui.empty-state icon="security"
                                              title="Chưa có nhật ký kiểm toán"
                                              message="Các hành động nhạy cảm trên hệ thống sẽ được tự động ghi nhận vết bất biến tại đây." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            <div class="pt-2">
                {{ $logs->links('pagination.n07') }}
            </div>
        </div>
    </x-ui.section-card>
</div>
@endsection
