{{--
    Đường dẫn: resources/views/reports/audit.blade.php
    Mô tả: Giao diện Nhật ký kiểm toán nghiệp vụ (Audit Trail) chuẩn Monochrome N07.
    Phụ thuộc: AuditController@index, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Nhật ký kiểm toán nghiệp vụ')
@section('page-title', 'Nhật ký kiểm toán nghiệp vụ')
@section('page-subtitle', 'Truy vết toàn bộ các thao tác thay đổi cấu hình, phân quyền và dữ liệu quan trọng trong hệ thống.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER TRANG --}}
    <x-ui.page-header title="Nhật ký kiểm toán nghiệp vụ"
                      chip="Audit Trail · Lưu vết hệ thống"
                      subtitle="Theo dõi nhật ký các hoạt động điều chỉnh cấu hình DSS, rà soát mục đích xuất kho và thao tác của người dùng.">
        <x-slot:actions>
            <a href="{{ route('reports.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Tất cả báo cáo</span>
            </a>
            <a href="{{ route('dss.history') }}"
               class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px] text-white">history</span>
                <span class="text-white">Lịch sử quyết định DSS</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU DÙNG x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- 1. BANNER KIỂM TOÁN DỮ LIỆU --}}
        <x-ui.banner-hint icon="security"
                          title="Quy tắc ghi nhật ký an toàn"
                          message="Mọi thao tác quản trị như đổi role tài khoản, khóa người dùng, chỉnh sửa tham số Lead Time / Safety Stock, hoặc thay đổi mục đích xuất kho đều được tự động lưu lại thời gian, người thực hiện cùng ảnh chụp dữ liệu trước và sau thay đổi.">
            <x-slot:action>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-900 border border-emerald-500/30 font-mono text-[11px] font-bold">
                    Truy vết tự động
                </span>
            </x-slot:action>
        </x-ui.banner-hint>

        {{-- 2. DẢI KPI TỔNG HỢP --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-ui.kpi-card label="Tổng số lượt ghi nhận"
                           :value="$logs->total()"
                           unit="lần"
                           icon="receipt_long"
                           tone="grey"
                           status="neutral">
                <span class="truncate">Nhật ký toàn hệ thống</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Bản ghi trang hiện tại"
                           :value="$logs->count()"
                           unit="mục"
                           icon="format_list_bulleted"
                           tone="sage"
                           status="neutral">
                <span class="truncate">Tối đa 30 mục mỗi trang</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Ghi nhận gần nhất"
                           :value="$logs->first() ? \Illuminate\Support\Carbon::parse($logs->first()->created_at)->format('H:i d/m') : 'Chưa có'"
                           icon="schedule"
                           tone="grey">
                <span class="truncate">Tự động đồng bộ theo thời gian</span>
            </x-ui.kpi-card>
        </div>

        {{-- 3. BẢNG DỮ LIỆU NHẬT KÝ AUDIT --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Sổ nhật ký thao tác hệ thống</span>
                </h3>
                <span class="text-xs text-on-surface-variant font-mono">Hiển thị {{ $logs->count() }} / {{ $logs->total() }} nhật ký</span>
            </div>

            <x-ui.data-table minWidth="min-w-[1000px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-3 w-28">Mã log</th>
                    <th class="py-3.5 px-4 w-52">Hành động</th>
                    <th class="py-3.5 px-4 w-48">Đối tượng tác động</th>
                    <th class="py-3.5 px-4 min-w-[200px]">Người thực hiện</th>
                    <th class="py-3.5 px-4 w-44">Thời điểm</th>
                    <th class="py-3.5 pr-6 pl-3 text-center w-32">Dữ liệu biến động</th>
                </x-slot:header>

                @forelse($logs as $log)
                    @php
                        $actionName = $log->action ?? 'ACTION';
                        $actionColor = 'neutral';
                        if (str_contains(strtolower($actionName), 'create') || str_contains(strtolower($actionName), 'approve')) {
                            $actionColor = 'success';
                        } elseif (str_contains(strtolower($actionName), 'update') || str_contains(strtolower($actionName), 'setting') || str_contains(strtolower($actionName), 'review')) {
                            $actionColor = 'warning';
                        } elseif (str_contains(strtolower($actionName), 'delete') || str_contains(strtolower($actionName), 'reject') || str_contains(strtolower($actionName), 'lock')) {
                            $actionColor = 'danger';
                        }

                        $before = json_decode($log->before_data ?? '{}', true) ?: [];
                        $after = json_decode($log->after_data ?? '{}', true) ?: [];
                    @endphp
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        {{-- Cột 1: Mã log --}}
                        <td class="py-4 pl-6 pr-3 font-mono font-bold text-xs text-primary">
                            #{{ $log->id }}
                        </td>

                        {{-- Cột 2: Hành động --}}
                        <td class="py-4 px-4 whitespace-nowrap">
                            <x-ui.status-pill :variant="$actionColor" :label="$actionName" />
                        </td>

                        {{-- Cột 3: Đối tượng tác động --}}
                        <td class="py-4 px-4 text-xs font-mono">
                            <span class="font-bold text-on-surface">{{ $log->entity_type }}</span>
                            @if($log->entity_id)
                                <span class="text-on-surface-variant ml-1 font-semibold">#{{ $log->entity_id }}</span>
                            @endif
                        </td>

                        {{-- Cột 4: Người thực hiện --}}
                        <td class="py-4 px-4 text-xs">
                            <div class="font-bold text-on-surface flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-secondary">person</span>
                                <span>{{ $log->name ?? 'Hệ thống tự động' }}</span>
                            </div>
                            @if($log->user_id)
                                <span class="text-[11px] text-on-surface-variant font-mono block mt-0.5">ID tài khoản: {{ $log->user_id }}</span>
                            @endif
                        </td>

                        {{-- Cột 5: Thời điểm --}}
                        <td class="py-4 px-4 text-xs font-mono text-on-surface-variant whitespace-nowrap">
                            <span class="text-on-surface font-semibold block">{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('H:i:s') }}</span>
                            <span class="text-[11px] block text-on-surface-variant/80">{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y') }}</span>
                        </td>

                        {{-- Cột 6: Nút xem dữ liệu trước/sau --}}
                        <td class="py-4 pr-6 pl-3 text-center">
                            @if(!empty($before) || !empty($after))
                                <button type="button"
                                        onclick="document.getElementById('modal-audit-{{ $log->id }}').showModal()"
                                        class="h-8 px-2.5 rounded-lg bg-surface-container-low text-on-surface hover:bg-surface-container text-xs font-semibold inline-flex items-center gap-1 border border-surface-container-high/70 transition-all hover:scale-105 active:scale-95 shadow-2xs">
                                    <span class="material-symbols-outlined text-[16px]">compare_arrows</span>
                                    <span>Đối chiếu</span>
                                </button>

                                {{-- MODAL SO SÁNH TRƯỚC / SAU --}}
                                <dialog id="modal-audit-{{ $log->id }}"
                                        class="rounded-2xl p-0 backdrop:bg-black/50 shadow-2xl border border-surface-container-high/80 w-full max-w-2xl bg-white overflow-hidden text-left">
                                    <div class="p-5 border-b border-surface-container-high/60 flex items-center justify-between bg-surface-container-low/40">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[18px]">history</span>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-sm text-on-surface">Chi tiết thay đổi nhật ký #{{ $log->id }}</h4>
                                                <p class="text-[11px] text-on-surface-variant font-mono">{{ $log->action }} trên {{ $log->entity_type }} #{{ $log->entity_id }}</p>
                                            </div>
                                        </div>
                                        <form method="dialog">
                                            <button class="w-8 h-8 rounded-lg hover:bg-surface-container-high/60 flex items-center justify-center text-on-surface-variant">
                                                <span class="material-symbols-outlined text-[18px]">close</span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar text-xs">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {{-- Dữ liệu trước thay đổi --}}
                                            <div class="space-y-2">
                                                <span class="font-mono text-xs font-bold text-on-surface-variant uppercase flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    <span>Trước khi thay đổi (Before)</span>
                                                </span>
                                                <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container-high/60 font-mono text-[11px] overflow-x-auto custom-scrollbar min-h-[140px]">
                                                    @if(!empty($before))
                                                        <pre class="text-on-surface leading-relaxed">{{ json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @else
                                                        <span class="text-on-surface-variant italic">Không có dữ liệu cũ (Tạo mới).</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Dữ liệu sau thay đổi --}}
                                            <div class="space-y-2">
                                                <span class="font-mono text-xs font-bold text-emerald-800 uppercase flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                    <span>Sau khi thay đổi (After)</span>
                                                </span>
                                                <div class="p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-200/60 font-mono text-[11px] overflow-x-auto custom-scrollbar min-h-[140px]">
                                                    @if(!empty($after))
                                                        <pre class="text-emerald-950 leading-relaxed">{{ json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @else
                                                        <span class="text-emerald-800/70 italic">Không có dữ liệu sau (Xóa bỏ).</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 border-t border-surface-container-high/60 bg-surface-container-low/40 flex justify-end">
                                        <form method="dialog">
                                            <button class="h-9 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors text-xs font-bold shadow-2xs">
                                                Đóng
                                            </button>
                                        </form>
                                    </div>
                                </dialog>
                            @else
                                <span class="text-on-surface-variant/50 text-xs font-mono">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <x-ui.empty-state icon="policy"
                                              title="Chưa có nhật ký kiểm toán"
                                              message="Chưa có thao tác nghiệp vụ nào được ghi nhận vào nhật ký kiểm toán." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            {{-- Phân trang chuẩn N07 --}}
            <div class="pt-2">
                {{ $logs->links('pagination.n07') }}
            </div>
        </div>

    </x-ui.section-card>

</div>
@endsection
