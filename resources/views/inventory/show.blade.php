{{--
    Đường dẫn: resources/views/inventory/show.blade.php
    Mô tả: Giao diện Chi tiết phiếu nhập/xuất kho chuẩn Monochrome N07, đồng bộ 100% với hệ thống.
    Phụ thuộc: InventoryController@show, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Chi tiết phiếu ' . $transaction->transaction_code)
@section('page-title', $transaction->type === 'import' ? 'Chi tiết phiếu nhập kho' : 'Chi tiết phiếu xuất kho')
@section('page-subtitle', 'Xem chi tiết các mặt hàng, giá trị và thông tin chứng từ kho đã xác nhận.')

@section('content')
<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- KHUNG 1: HEADER TRANG DÙNG x-ui.page-header --}}
    <x-ui.page-header :title="$transaction->transaction_code"
                      :chip="$transaction->type === 'import' ? 'Phiếu nhập kho' : 'Phiếu xuất kho'">
        <x-slot:subtitle>
            Ngày lập: <strong>{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</strong> •
            Nhân viên thực hiện: <strong>{{ $transaction->staff_name ?? 'Tài khoản đã xóa' }}</strong>
        </x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('inventory.history') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-xs font-semibold active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">arrow_back</span>
                <span>Quay lại sổ kho</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-all font-label-md text-xs font-semibold active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px] text-white">print</span>
                <span class="text-white">In phiếu</span>
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- RÀ SOÁT MỤC ĐÍCH XUẤT (DÀNH CHO ADMIN) --}}
    @if($transaction->type === 'export' && auth()->user()->role === 'admin')
        <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm border border-surface-container-high/60">
            <details class="group">
                <summary class="list-none cursor-pointer flex items-center justify-between text-xs font-bold text-on-surface">
                    <span class="flex items-center gap-2 text-secondary">
                        <span class="material-symbols-outlined text-[18px]">verified_user</span>
                        <span>Rà soát mục đích xuất kho (Lưu vết kiểm toán DSS)</span>
                    </span>
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-open:rotate-180 transition-transform">expand_more</span>
                </summary>
                <form action="{{ route('inventory.purpose', $transaction->id) }}" method="POST" class="mt-4 pt-3 border-t border-surface-container-high/60 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    @csrf
                    @method('PUT')
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-on-surface">Mục đích xuất kho</label>
                        <select name="issue_purpose" class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs font-medium focus:outline-none focus:ring-1 focus:ring-primary">
                            @foreach(['sale'=>'Bán / tiêu thụ (Tính vào nhu cầu)','damage'=>'Hàng hỏng (Không tính nhu cầu)','transfer'=>'Điều chuyển','other'=>'Khác'] as $key => $label)
                                <option value="{{ $key }}" @selected($transaction->issue_purpose === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-on-surface">Lý do xác nhận</label>
                        <input name="reason" required maxlength="1000" placeholder="Nhập căn cứ điều chỉnh mục đích..." class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs font-medium focus:outline-none focus:ring-1 focus:ring-primary"/>
                    </div>
                    <div>
                        <button type="submit" class="h-10 px-4 rounded-xl bg-primary text-white text-xs font-bold hover:bg-neutral-800 transition-colors shadow-2xs">
                            Lưu xác nhận
                        </button>
                    </div>
                </form>
            </details>
        </div>
    @endif

    {{-- KHUNG 2: CHI TIẾT THÔNG SỐ & BẢNG SẢN PHẨM --}}
    <x-ui.section-card spacing="space-y-6">
        {{-- Thông số phiếu dạng lưới --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 p-4 rounded-xl bg-surface-container-low/40 border border-surface-container-high/60 text-xs">
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Mục đích phiếu</span>
                <strong class="text-on-surface font-bold mt-0.5 block">
                    {{ ['sale'=>'Bán / tiêu thụ','damage'=>'Hàng hỏng','transfer'=>'Điều chuyển','other'=>'Khác','receipt'=>'Nhập kho','unknown'=>'Chưa xác định'][$transaction->issue_purpose] ?? $transaction->issue_purpose }}
                </strong>
            </div>
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Chứng từ tham chiếu</span>
                <strong class="text-on-surface font-bold mt-0.5 block font-mono">{{ $transaction->reference_no ?? '—' }}</strong>
            </div>
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Ngày giao dịch</span>
                <strong class="text-on-surface font-bold mt-0.5 block font-mono">{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</strong>
            </div>
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Người thực hiện</span>
                <strong class="text-on-surface font-bold mt-0.5 block">{{ $transaction->staff_name ?? 'Tài khoản đã xóa' }}</strong>
            </div>
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Tổng giá trị phiếu</span>
                <strong class="text-emerald-800 font-bold text-sm mt-0.5 block whitespace-nowrap tabular-nums font-mono">{{ number_format($transaction->total_value, 0, ',', '.') }} đ</strong>
            </div>
            <div>
                <span class="text-on-surface-variant block text-[11px] font-mono">Ghi chú</span>
                <strong class="text-on-surface font-bold mt-0.5 block truncate" title="{{ $transaction->note ?: 'Không có ghi chú' }}">{{ $transaction->note ?: 'Không có' }}</strong>
            </div>
        </div>

        {{-- Bảng chi tiết mặt hàng dùng x-ui.data-table --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-headline-sm text-sm font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Danh mục các mặt hàng trong phiếu ({{ $items->count() }} mặt hàng)</span>
                </h3>
            </div>

            <x-ui.data-table minWidth="min-w-[850px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-2 w-36">Mã SKU</th>
                    <th class="py-3.5 px-5 min-w-[240px]">Tên mặt hàng</th>
                    <th class="py-3.5 px-5 text-right w-36">Số lượng</th>
                    <th class="py-3.5 px-5 text-right w-36">Đơn giá</th>
                    <th class="py-3.5 px-5 text-right w-40">Thành tiền</th>
                    <th class="py-3.5 pr-6 pl-2 text-center w-36">Đề xuất DSS</th>
                </x-slot:header>

                @foreach($items as $item)
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        <x-ui.cell-code :code="$item->product_code" class="pl-6" />
                        <td class="py-5 px-5">
                            <span class="font-semibold text-sm text-on-surface block">{{ $item->name }}</span>
                            <span class="text-[11px] text-on-surface-variant font-mono">Đơn vị: {{ $item->unit }}</span>
                        </td>
                        <x-ui.cell-number :value="$item->quantity" :unit="$item->unit" />
                        <x-ui.cell-money :amount="$item->unit_price" />
                        <x-ui.cell-money :amount="$item->line_total" />
                        <td class="py-5 pr-6 pl-2 text-center font-mono">
                            @if($item->reorder_recommendation_id)
                                <a href="{{ route('dss.history') }}"
                                   class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300/80 text-xs font-semibold whitespace-nowrap hover:bg-amber-200 transition-colors inline-block"
                                   title="Liên kết đợt đề xuất #{{ $item->reorder_recommendation_id }}">
                                    #{{ $item->reorder_recommendation_id }}
                                </a>
                            @else
                                <span class="text-on-surface-variant/50 text-xs font-mono">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        </div>
    </x-ui.section-card>
</div>
@endsection
