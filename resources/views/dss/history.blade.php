{{--
    Đường dẫn: resources/views/dss/history.blade.php
    Mô tả: Giao diện Lịch sử tính toán và quyết định DSS chuẩn Monochrome N07.
    Phụ thuộc: ReorderController@history, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Lịch sử đề xuất & quyết định DSS')
@section('page-title', 'Lịch sử phân tích và quyết định DSS')
@section('page-subtitle', 'Truy vết toàn bộ căn cứ định lượng, lịch sử phê duyệt và tiến độ nhận hàng của các đợt đề xuất.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER TRANG --}}
    <x-ui.page-header title="Lịch sử tính toán & quyết định DSS"
                      chip="Nhật ký ra quyết định nhập hàng"
                      subtitle="Theo dõi chi tiết các đợt chạy thuật toán DSS, lịch sử phê duyệt của Admin và đối soát lượng hàng thực nhận.">
        <x-slot:actions>
            <a href="{{ route('dss.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Quay lại đề xuất DSS</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('audit.index') }}"
                   class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px] text-white">verified_user</span>
                    <span class="text-white">Nhật ký nghiệp vụ</span>
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU DÙNG x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- 1. BANNER NGUYÊN TẮC LƯU VẾT DSS --}}
        <x-ui.banner-hint icon="history_edu"
                          title="Lưu vết kiểm toán quyết định nhập hàng"
                          message="Mỗi lần chạy DSS đều lưu lại ảnh chụp số liệu (snapshot) gồm d, L, SS, T, IP, ROP và lượng đề xuất ban đầu. Khi Admin điều chỉnh số lượng duyệt hoặc từ chối, lý do và danh tính người duyệt được lưu bất biến phục vụ đối soát.">
            <x-slot:action>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-900 border border-emerald-500/30 font-mono text-[11px] font-bold">
                    Dữ liệu kiểm toán bất biến
                </span>
            </x-slot:action>
        </x-ui.banner-hint>

        {{-- 2. DẢI 4 THẺ CHỈ SỐ TỔNG KẾT NHANH --}}
        @php
            $totalCount = $records->total();
            $approvedCount = $records->where('status', 'approved')->count();
            $pendingCount = $records->where('status', 'pending')->count();
            $fulfilledCount = $records->where('status', 'fulfilled')->count();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.kpi-card label="Tổng lượt phân tích"
                           :value="$totalCount"
                           unit="lần"
                           icon="analytics"
                           tone="grey"
                           status="neutral">
                <span class="truncate">Lịch sử toàn bộ đợt DSS</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Đã phê duyệt (Trang này)"
                           :value="$approvedCount"
                           unit="lô"
                           icon="check_circle"
                           tone="sage"
                           status="success">
                <span class="truncate">Admin đã xác nhận nhập</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Chờ xem xét (Trang này)"
                           :value="$pendingCount"
                           unit="mặt hàng"
                           icon="schedule"
                           tone="grey"
                           :status="$pendingCount > 0 ? 'warning' : 'neutral'">
                <span class="truncate">Cần Admin phê duyệt</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Đã hoàn tất nhận hàng"
                           :value="$fulfilledCount"
                           unit="lô"
                           icon="inventory"
                           tone="sage"
                           status="success">
                <span class="truncate">Đã nhập kho đủ số lượng</span>
            </x-ui.kpi-card>
        </div>

        {{-- 3. BẢNG DỮ LIỆU LỊCH SỬ DSS DÙNG x-ui.data-table --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Sổ nhật ký đề xuất & Quyết định nhập hàng</span>
                </h3>
                <span class="text-xs text-on-surface-variant font-mono">Hiển thị {{ $records->count() }} / {{ $records->total() }} bản ghi</span>
            </div>

            <x-ui.data-table minWidth="min-w-[1100px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-3 w-28">Mã đợt</th>
                    <th class="py-3.5 px-4 min-w-[200px]">Mặt hàng & SKU</th>
                    <th class="py-3.5 px-3 text-center w-32">Trạng thái tồn</th>
                    <th class="py-3.5 px-3 text-center w-32">Xử lý</th>
                    <th class="py-3.5 px-4 text-right w-44">Số lượng (Gốc · Duyệt · Nhận)</th>
                    <th class="py-3.5 px-4 min-w-[220px]">Người duyệt & Lý do</th>
                    <th class="py-3.5 pr-6 pl-3 text-center w-28">Chi tiết</th>
                </x-slot:header>

                @forelse($records as $record)
                    @php
                        $statusMap = [
                            'pending' => ['Chờ duyệt', 'warning'],
                            'approved' => ['Đã duyệt', 'success'],
                            'rejected' => ['Từ chối', 'danger'],
                            'fulfilled' => ['Đã nhận đủ', 'success'],
                            'no_action' => ['Chưa cần nhập', 'neutral'],
                            'superseded' => ['Đã bị thay thế', 'neutral'],
                        ];
                        $statusBadge = $statusMap[$record->status] ?? [$record->status, 'neutral'];

                        $stockMap = [
                            'REORDER' => ['Cần xem xét', 'danger'],
                            'NORMAL' => ['Bình thường', 'success'],
                            'MISSING_CONFIG' => ['Chưa cấu hình', 'warning'],
                            'INSUFFICIENT_DATA' => ['Thiếu dữ liệu', 'neutral'],
                            'INVALID_DATA' => ['Bất thường', 'danger'],
                        ];
                        $stockBadge = $stockMap[$record->stock_status] ?? [$record->stock_status ?? 'NORMAL', 'neutral'];

                        $snapshot = json_decode($record->calculation_snapshot ?? '{}', true) ?: [];
                    @endphp
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[76px]">
                        {{-- Cột 1: Mã đợt & Ngày giờ --}}
                        <td class="py-4 pl-6 pr-3">
                            <span class="font-mono font-bold text-xs text-primary block">#{{ $record->id }}</span>
                            <span class="text-[11px] font-mono text-on-surface-variant block mt-0.5">
                                {{ \Illuminate\Support\Carbon::parse($record->created_at)->format('H:i d/m/Y') }}
                            </span>
                        </td>

                        {{-- Cột 2: Mặt hàng & SKU --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-surface-container-low border border-surface-container-high/60 flex items-center justify-center font-mono font-bold text-[11px] text-on-surface-variant shrink-0">
                                    {{ mb_substr($record->product_code, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $record->product_id) }}"
                                       class="font-bold text-sm text-on-surface hover:text-emerald-700 hover:underline block truncate"
                                       title="{{ $record->name }}">
                                        {{ $record->name }}
                                    </a>
                                    <span class="font-mono text-xs text-on-surface-variant block">{{ $record->product_code }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Cột 3: Trạng thái tồn --}}
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            <x-ui.status-pill :variant="$stockBadge[1]" :label="$stockBadge[0]" />
                        </td>

                        {{-- Cột 4: Quyết định xử lý --}}
                        <td class="py-4 px-3 text-center whitespace-nowrap">
                            <x-ui.status-pill :variant="$statusBadge[1]" :label="$statusBadge[0]" />
                        </td>

                        {{-- Cột 5: Số lượng (Gốc · Duyệt · Đã nhận) --}}
                        <td class="py-4 px-4 text-right font-mono">
                            <div class="text-xs">
                                <span class="text-on-surface-variant">Đề xuất:</span>
                                <strong class="text-on-surface font-bold ml-1">{{ $record->suggested_quantity }}</strong>
                            </div>
                            <div class="text-xs mt-0.5">
                                <span class="text-on-surface-variant">Duyệt:</span>
                                <strong class="{{ $record->approved_quantity > 0 ? 'text-emerald-800' : 'text-on-surface' }} font-bold ml-1">
                                    {{ $record->approved_quantity ?? '—' }}
                                </strong>
                            </div>
                            @if($record->received_quantity > 0)
                                <div class="text-[11px] text-secondary mt-0.5">
                                    <span>Đã nhận:</span>
                                    <strong class="font-bold ml-1">{{ $record->received_quantity }}</strong>
                                </div>
                            @endif
                        </td>

                        {{-- Cột 6: Người duyệt & Lý do --}}
                        <td class="py-4 px-4 text-xs">
                            @if($record->reviewer)
                                <div class="font-bold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px] text-secondary">admin_panel_settings</span>
                                    <span>{{ $record->reviewer }}</span>
                                </div>
                                <span class="text-[11px] text-on-surface-variant font-mono block mt-0.5">
                                    {{ \Illuminate\Support\Carbon::parse($record->reviewed_at)->format('H:i d/m/Y') }}
                                </span>
                            @else
                                <span class="text-on-surface-variant italic">Chưa duyệt / Tự động</span>
                            @endif

                            @if($record->decision_note)
                                <p class="mt-1 text-[11px] text-on-surface bg-surface-container-low px-2 py-1 rounded-lg border border-surface-container-high/60 line-clamp-2"
                                   title="{{ $record->decision_note }}">
                                    <strong>Lý do:</strong> {{ $record->decision_note }}
                                </p>
                            @elseif($record->explanation)
                                <p class="mt-1 text-[11px] text-on-surface-variant line-clamp-1" title="{{ $record->explanation }}">
                                    {{ $record->explanation }}
                                </p>
                            @endif
                        </td>

                        {{-- Cột 7: Nút xem snapshot chi tiết --}}
                        <td class="py-4 pr-6 pl-3 text-center">
                            <button type="button"
                                    onclick="document.getElementById('modal-snapshot-{{ $record->id }}').showModal()"
                                    class="h-8 px-2.5 rounded-lg bg-surface-container-low text-on-surface hover:bg-surface-container text-xs font-semibold inline-flex items-center gap-1 border border-surface-container-high/70 transition-all hover:scale-105 active:scale-95 shadow-2xs">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                <span>Căn cứ</span>
                            </button>

                            {{-- MODAL NATIVE XEM SNAPSHOT THUẬT TOÁN DSS --}}
                            <dialog id="modal-snapshot-{{ $record->id }}"
                                    class="rounded-2xl p-0 backdrop:bg-black/50 shadow-2xl border border-surface-container-high/80 w-full max-w-lg bg-white overflow-hidden text-left">
                                <div class="p-5 border-b border-surface-container-high/60 flex items-center justify-between bg-surface-container-low/40">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-600/10 text-emerald-800 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[18px]">functions</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-on-surface">Căn cứ tính toán đợt #{{ $record->id }}</h4>
                                            <p class="text-[11px] text-on-surface-variant font-mono">{{ $record->product_code }} · {{ $record->name }}</p>
                                        </div>
                                    </div>
                                    <form method="dialog">
                                        <button class="w-8 h-8 rounded-lg hover:bg-surface-container-high/60 flex items-center justify-center text-on-surface-variant">
                                            <span class="material-symbols-outlined text-[18px]">close</span>
                                        </button>
                                    </form>
                                </div>

                                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar text-xs">
                                    {{-- Giải thích nghiệp vụ --}}
                                    <div class="p-3.5 rounded-xl bg-surface-container-low/60 border border-surface-container-high/60 space-y-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant font-mono block">Giải thích thuật toán</span>
                                        <p class="text-on-surface leading-relaxed">{{ $record->explanation ?: 'Không có giải thích chi tiết.' }}</p>
                                    </div>

                                    {{-- Lưới thông số snapshot --}}
                                    <div class="grid grid-cols-2 gap-2.5 font-mono">
                                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                                            <span class="text-[10px] text-on-surface-variant block uppercase">Nhu cầu TB (d)</span>
                                            <strong class="text-sm text-on-surface">{{ $snapshot['average_daily_demand'] ?? $record->average_daily_demand ?? '—' }} SP/ngày</strong>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                                            <span class="text-[10px] text-on-surface-variant block uppercase">Tồn an toàn (SS)</span>
                                            <strong class="text-sm text-on-surface">{{ $snapshot['safety_stock'] ?? $record->safety_stock ?? '—' }} SP</strong>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                                            <span class="text-[10px] text-on-surface-variant block uppercase">Điểm đặt hàng (ROP)</span>
                                            <strong class="text-sm text-secondary">{{ $snapshot['reorder_point'] ?? $record->reorder_point ?? '—' }} SP</strong>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                                            <span class="text-[10px] text-on-surface-variant block uppercase">Lượng đề xuất (Q)</span>
                                            <strong class="text-sm text-primary">{{ $record->suggested_quantity }} SP</strong>
                                        </div>
                                    </div>

                                    @if($record->decision_note)
                                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-950 space-y-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider font-mono">Ghi chú quyết định của Admin:</span>
                                            <p class="font-medium">{{ $record->decision_note }}</p>
                                            <span class="text-[11px] text-amber-900/70 block mt-1">Duyệt bởi: {{ $record->reviewer }} lúc {{ $record->reviewed_at }}</span>
                                        </div>
                                    @endif

                                    {{-- Dữ liệu JSON gốc --}}
                                    <details class="group pt-2 border-t border-surface-container-high/60">
                                        <summary class="cursor-pointer text-[11px] font-mono font-bold text-on-surface-variant flex items-center justify-between">
                                            <span>Xem toàn bộ JSON Snapshot</span>
                                            <span class="material-symbols-outlined text-[16px] group-open:rotate-180 transition-transform">expand_more</span>
                                        </summary>
                                        <pre class="mt-2 p-3 rounded-xl bg-neutral-900 text-neutral-100 font-mono text-[11px] overflow-x-auto custom-scrollbar leading-relaxed">{{ json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                </div>

                                <div class="p-4 border-t border-surface-container-high/60 bg-surface-container-low/40 flex justify-end">
                                    <form method="dialog">
                                        <button class="h-9 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors text-xs font-bold shadow-2xs">
                                            Đóng
                                        </button>
                                    </form>
                                </div>
                            </dialog>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-0 border-0">
                            <x-ui.empty-state icon="history"
                                              title="Chưa có lịch sử đề xuất"
                                              message="Chưa có bản ghi phân tích DSS nào được lưu lại trong hệ thống." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            {{-- Phân trang chuẩn N07 --}}
            <div class="pt-2">
                {{ $records->links('pagination.n07') }}
            </div>
        </div>

    </x-ui.section-card>

</div>
@endsection
