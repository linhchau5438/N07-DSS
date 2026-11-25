{{--
    Đường dẫn: resources/views/dss/history.blade.php
    Mô tả: Giao diện Lịch sử quyết định DSS theo Monochrome Design System chuẩn N07.
    Phụ thuộc: ReorderController@history, layouts/app.blade.php, pagination/n07.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Lịch sử quyết định DSS')
@section('page-title', 'Lịch sử DSS')
@section('page-subtitle', 'Toàn bộ vết tính toán, căn cứ nhu cầu và quyết định phê duyệt nhập hàng.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER --}}
    <x-ui.page-header title="Lịch sử tính toán & quyết định DSS"
                      chip="Nhật ký quyết định bổ sung kho"
                      subtitle="Theo dõi toàn bộ lịch sử chạy thuật toán DSS, căn cứ nhu cầu ROP/SS và các quyết định duyệt/đặt hàng.">
        <x-slot:actions>
            <a href="{{ route('dss.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Quay lại đề xuất DSS</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('audit.index') }}"
                   class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[18px] text-white">security</span>
                    <span class="text-white">Nhật ký kiểm toán</span>
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ --}}
    <x-ui.section-card spacing="space-y-6">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-on-surface">Danh sách bản ghi quyết định</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-container-low text-on-surface-variant border border-surface-container-high/60">
                        {{ $records->total() }} bản ghi
                    </span>
                </div>
            </div>

            <x-ui.data-table minWidth="min-w-[1250px]">
                <x-slot:head>
                    <th class="py-3.5 pl-6 pr-2 min-w-[110px]">Mã / Ngày</th>
                    <th class="py-3.5 px-5 min-w-[240px]">Sản phẩm & SKU</th>
                    <th class="py-3.5 px-5 text-center min-w-[150px]">Trạng thái</th>
                    <th class="py-3.5 px-5 text-right min-w-[110px]">Đề xuất (Q)</th>
                    <th class="py-3.5 px-5 text-right min-w-[110px]">Admin duyệt</th>
                    <th class="py-3.5 px-5 text-right min-w-[110px]">Đặt thực tế</th>
                    <th class="py-3.5 px-5 text-right min-w-[110px]">Đã nhận kho</th>
                    <th class="py-3.5 px-5 min-w-[220px]">Người duyệt & Ghi chú</th>
                    <th class="py-3.5 pr-6 pl-2 text-center min-w-[120px]">Căn cứ</th>
                </x-slot:head>

                @forelse($records as $rec)
                    @php
                        $st = $rec->status;
                        $badgeMap = [
                            'pending' => ['warning', 'Chờ duyệt'],
                            'approved' => ['info', 'Đã duyệt (Chờ đặt)'],
                            'ordered' => ['info', 'Đã đặt NCC (Đang về)'],
                            'partially_received' => ['warning', 'Đã nhận 1 phần'],
                            'fulfilled' => ['success', 'Đã nhận đủ'],
                            'rejected' => ['danger', 'Từ chối'],
                            'cancelled' => ['danger', 'Đã hủy'],
                        ];
                        $statusBadge = $badgeMap[$st] ?? ['neutral', $st];
                    @endphp
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        <td class="py-4 pl-6 pr-2">
                            <span class="font-mono font-bold text-xs text-on-surface">#{{ $rec->id }}</span>
                            <span class="text-[11px] text-on-surface-variant block">{{ \Illuminate\Support\Carbon::parse($rec->created_at)->format('d/m/Y H:i') }}</span>
                        </td>

                        <td class="py-4 px-5">
                            <a href="{{ route('products.show', $rec->product_id) }}" class="font-bold text-sm text-on-surface hover:underline block">
                                {{ $rec->name }}
                            </a>
                            <span class="font-mono text-xs text-on-surface-variant">SKU: {{ $rec->product_code }} · {{ $rec->unit ?? '' }}</span>
                        </td>

                        <td class="py-4 px-5 text-center whitespace-nowrap">
                            <x-ui.status-pill :variant="$statusBadge[0]" :label="$statusBadge[1]" />
                        </td>

                        <td class="py-4 px-5 text-right tabular-nums font-semibold text-[15px] text-on-surface">
                            {{ number_format($rec->suggested_quantity) }}
                        </td>

                        <td class="py-4 px-5 text-right tabular-nums font-semibold text-[15px] text-secondary">
                            {{ $rec->approved_quantity !== null ? number_format($rec->approved_quantity) : '—' }}
                        </td>

                        <td class="py-4 px-5 text-right tabular-nums font-medium text-[15px] text-on-surface-variant">
                            {{ $rec->ordered_quantity !== null ? number_format($rec->ordered_quantity) : '—' }}
                        </td>

                        <td class="py-4 px-5 text-right tabular-nums font-semibold text-[15px] text-emerald-700">
                            {{ number_format($rec->received_quantity ?? 0) }}
                        </td>

                        <td class="py-4 px-5 text-xs text-on-surface-variant">
                            <span class="font-bold text-on-surface block">{{ $rec->reviewer ?? 'Tự động' }}</span>
                            <span class="line-clamp-2">{{ $rec->decision_note ?: ($rec->order_note ?: '—') }}</span>
                        </td>

                        <td class="py-4 pr-6 pl-2 text-center">
                            <details class="inline-block text-left">
                                <summary class="px-2.5 py-1 rounded-lg bg-surface-container-low text-xs font-semibold text-on-surface hover:bg-surface-container cursor-pointer select-none">
                                    Chi tiết
                                </summary>
                                <div class="fixed inset-0 bg-neutral-900/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this)this.closest('details').removeAttribute('open')">
                                    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-xl border border-surface-container-high space-y-4" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
                                            <h3 class="font-headline-sm text-sm font-bold text-on-surface">Căn cứ quyết định đề xuất #{{ $rec->id }}</h3>
                                            <button type="button" onclick="this.closest('details').removeAttribute('open')" class="text-on-surface-variant hover:text-on-surface">✕</button>
                                        </div>
                                        <div class="space-y-3 text-xs">
                                            <div class="p-3 rounded-xl bg-surface-container-low border border-surface-container-high/60">
                                                <strong class="text-on-surface block mb-1">Giải thích tính toán:</strong>
                                                <p class="text-on-surface-variant leading-relaxed">{{ $rec->explanation ?: 'Không có giải thích' }}</p>
                                            </div>
                                            <div>
                                                <strong class="text-on-surface block mb-1">Dữ liệu đầu vào (Snapshot):</strong>
                                                <pre class="p-3 rounded-xl bg-neutral-900 text-emerald-400 font-mono text-[11px] overflow-x-auto">{{ json_encode(json_decode($rec->calculation_snapshot ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-0 border-0">
                            <x-ui.empty-state icon="manage_history"
                                              title="Chưa có lịch sử đề xuất DSS"
                                              message="Khi thuật toán DSS được chạy và ghi nhận quyết định, lịch sử sẽ xuất hiện tại đây." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            <div class="pt-2">
                {{ $records->links('pagination.n07') }}
            </div>
        </div>
    </x-ui.section-card>
</div>
@endsection
