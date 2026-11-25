{{-- File: resources/views/inventory/create.blade.php --}}
{{-- Mô tả: Giao diện Lập phiếu nhập kho & xuất kho chuẩn Design System N07 Monochrome (Khung 1: Header chào; Khung 2: Bố cục 8:4 cân đối, bảng chọn hàng hóa tự tính thành tiền tức thì, kiểm tra tồn kho trực tiếp, tổng kết giá trị phiếu). --}}
{{-- Phụ thuộc: resources/views/layouts/app.blade.php, InventoryController@create, InventoryController@store. --}}

@extends('layouts.app')

@section('title', $type === 'import' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho')
@section('page-title', $type === 'import' ? 'Nhập kho' : 'Xuất kho')

@section('content')
<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- ========================================================================= --}}
    {{-- Khung 1: Header trang dùng x-ui.page-header (Card trắng rounded-2xl)       --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header :title="$type === 'import' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho'"
                      :chip="$type === 'import' ? 'Giao dịch Nhập hàng' : 'Giao dịch Xuất hàng'">
        <x-slot:subtitle>
            Người lập phiếu: <strong class="text-on-surface font-semibold">{{ auth()->user()->name }}</strong> •
            Kho Tổng Miền Bắc •
            {{ $type === 'import' ? 'Ghi nhận thực nhập và giá vốn' : 'Kiểm tra tồn kho trước khi xuất' }}
        </x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('inventory.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md active:scale-[0.98]">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">arrow_back</span>
                <span>Quay lại sổ kho</span>
            </a>
            <a href="{{ route('inventory.index', ['tab' => 'history']) }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md active:scale-[0.98]">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">history</span>
                <span>Nhật ký giao dịch</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Banner đề xuất DSS đang được gắn vào phiếu nhập (nếu có) dùng x-ui.banner-hint --}}
    @if($type === 'import' && $selectedRecommendation)
        <x-ui.banner-hint icon="assignment_turned_in"
                          :title="'Liên kết đề xuất DSS #' . $selectedRecommendation->id . ' — ' . $selectedRecommendation->name">
            Đề xuất được duyệt {{ $selectedRecommendation->ordered_quantity ?? $selectedRecommendation->approved_quantity }} {{ $selectedRecommendation->unit }}; còn phải nhận thực tế 
            <strong>{{ ($selectedRecommendation->ordered_quantity ?? $selectedRecommendation->approved_quantity) - $selectedRecommendation->received_quantity }} {{ $selectedRecommendation->unit }}</strong>.
        </x-ui.banner-hint>
    @endif

    {{-- ========================================================================= --}}
    {{-- Khung 2: Main Content Card lớn 8:4 (Grid 12 cột, hai cột ngang đáy nhau)  --}}
    {{-- ========================================================================= --}}
    <form action="{{ route('inventory.store', $type) }}" method="POST" id="inventoryForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- ================================================================= --}}
            {{-- CỘT TRÁI (Khoảng 67% - lg:col-span-8): Form chứng từ & Bảng hàng  --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Khối 1: Thông tin chứng từ & giao dịch --}}
                <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm border border-surface-container-high/60 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[19px]">receipt_long</span>
                            </span>
                            <h2 class="font-headline-sm text-sm font-bold text-on-surface">Thông tin chứng từ</h2>
                        </div>
                        <span class="text-xs text-on-surface-variant">Chứng từ ghi sổ tự động</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Ngày giao dịch --}}
                        <div class="space-y-1.5">
                            <label for="transaction_date" class="block font-label-md text-xs font-semibold text-on-surface">
                                Ngày giao dịch <span class="text-error">*</span>
                            </label>
                            <input id="transaction_date" type="date" name="transaction_date"
                                   max="{{ now()->toDateString() }}"
                                   value="{{ old('transaction_date', now()->toDateString()) }}" required
                                   class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-on-surface text-xs font-medium focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none"/>
                        </div>

                        {{-- Số chứng từ tham chiếu --}}
                        <div class="space-y-1.5">
                            <label for="reference_no" class="block font-label-md text-xs font-semibold text-on-surface">
                                Số hóa đơn / Số PO tham chiếu
                            </label>
                            <input id="reference_no" name="reference_no" maxlength="100"
                                   value="{{ old('reference_no') }}"
                                   placeholder="Ví dụ: HĐ-2024-001, PO-9982..."
                                   class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-on-surface text-xs font-medium focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none"/>
                        </div>

                        {{-- Nếu là Xuất kho: Mục đích xuất --}}
                        @if($type === 'export')
                            <div class="space-y-1.5">
                                <label for="issue_purpose" class="block font-label-md text-xs font-semibold text-on-surface">
                                    Mục đích xuất kho <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <select id="issue_purpose" name="issue_purpose" required
                                            class="w-full h-10 pl-3 pr-8 rounded-xl bg-surface-container-low border border-surface-container-high text-on-surface text-xs font-medium focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none appearance-none cursor-pointer">
                                        @foreach(['consumption' => 'Xuất tiêu thụ / Sử dụng nội bộ / Cấp phát', 'sale' => 'Xuất bán hàng thương mại', 'damage' => 'Xuất hàng hỏng, lỗi, hết hạn, hủy bỏ', 'return_vendor' => 'Xuất trả nhà cung cấp', 'transfer' => 'Xuất điều chuyển sang kho khác', 'adjustment' => 'Xuất điều chỉnh kiểm kê định kỳ', 'other' => 'Mục đích khác'] as $key => $label)
                                            <option value="{{ $key }}" @selected(old('issue_purpose') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                                </div>
                                <p class="text-[11px] text-on-surface-variant/80">Chỉ xuất bán / tiêu thụ được dùng để phân tích dự báo DSS.</p>
                            </div>
                        @endif

                        {{-- Nhà cung cấp hoặc Lý do / Ghi chú --}}
                        <div class="space-y-1.5 {{ $type === 'export' ? '' : 'sm:col-span-2' }}">
                            <label for="note" class="block font-label-md text-xs font-semibold text-on-surface">
                                {{ $type === 'import' ? 'Nhà cung cấp / Ghi chú giao hàng' : 'Đơn vị nhận / Ghi chú xuất' }}
                            </label>
                            <input id="note" name="note" maxlength="2000" value="{{ old('note') }}"
                                   placeholder="Nhập thông tin đối tác hoặc lưu ý nghiệp vụ..."
                                   class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-on-surface text-xs font-medium focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none"/>
                        </div>
                    </div>
                </div>

                {{-- Khối 2: Bảng sản phẩm trên phiếu (Có nút thêm và xóa dòng riêng theo mẫu) --}}
                <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm border border-surface-container-high/60 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60 flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[19px]">list_alt</span>
                            </span>
                            <div>
                                <h2 class="font-headline-sm text-sm font-bold text-on-surface">Danh mục mặt hàng trên phiếu</h2>
                                <p class="text-xs text-on-surface-variant">Không thêm trùng một mặt hàng nhiều lần trong cùng một phiếu.</p>
                            </div>
                        </div>

                        {{-- Nút Thêm mặt hàng nhanh --}}
                        <button type="button" onclick="addInventoryRow()"
                                class="h-9 px-3.5 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container text-xs font-bold transition-colors flex items-center gap-1.5 border border-surface-container-high/60">
                            <span class="material-symbols-outlined text-[17px]">add</span>
                            <span>Thêm dòng</span>
                        </button>
                    </div>

                    {{-- Bảng chi tiết từng dòng mặt hàng dùng component x-ui.data-table --}}
                    <x-ui.data-table minWidth="min-w-[750px]" id="items">
                        <x-slot:header>
                            <th class="py-3 px-4 w-64">Sản phẩm &amp; Đề xuất</th>
                            <th class="py-3 px-3 text-center w-20">ĐVT</th>
                            <th class="py-3 px-3 text-center w-28">Tồn hiện tại</th>
                            <th class="py-3 px-3 text-right w-32">Số lượng *</th>
                            <th class="py-3 px-3 text-right w-40">Đơn giá áp dụng *</th>
                            <th class="py-3 px-4 text-right w-36">Thành tiền</th>
                            <th class="py-3 px-3 text-center w-14">Xóa</th>
                        </x-slot:header>

                        {{-- Dòng mặt hàng đầu tiên mặc định --}}
                        <tr class="item-row hover:bg-surface-container-low/70 transition-colors h-[72px]">
                            <td class="py-4 px-4">
                                <div class="space-y-1.5">
                                    <div class="relative">
                                        <select name="items[0][product_id]" class="product-select w-full h-10 pl-3 pr-7 rounded-xl bg-surface-container-lowest border border-surface-container-high text-xs font-semibold focus:ring-1 focus:ring-primary focus:outline-none appearance-none cursor-pointer" required>
                                            <option value="">Chọn sản phẩm</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                                                        data-unit="{{ $product->unit }}"
                                                        data-import-price="{{ $product->purchase_price }}"
                                                        data-export-price="{{ $product->selling_price }}"
                                                        {{ old('items.0.product_id', $selectedProductId ?? '') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                                    </div>

                                    {{-- Nếu là phiếu nhập: Chọn liên kết đề xuất DSS --}}
                                    @if($type === 'import')
                                        <div class="relative">
                                            <select name="items[0][reorder_recommendation_id]" class="order-select w-full h-8 pl-2 pr-6 rounded-md bg-amber-50/60 border border-amber-200/80 text-[11px] text-amber-900 focus:outline-none appearance-none cursor-pointer">
                                                <option value="">Nhập thường — không liên kết DSS</option>
                                                @foreach($openRecommendations as $recommendation)
                                                    <option value="{{ $recommendation->id }}" data-product-id="{{ $recommendation->product_id }}"
                                                            {{ old('items.0.reorder_recommendation_id', $selectedRecommendation->id ?? '') == $recommendation->id ? 'selected' : '' }}>
                                                        Đề xuất #{{ $recommendation->id }} · còn {{ ($recommendation->ordered_quantity ?? $recommendation->approved_quantity) - $recommendation->received_quantity }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-outlined absolute right-1.5 top-1/2 -translate-y-1/2 text-amber-700 pointer-events-none text-[15px]">expand_more</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Đơn vị tính --}}
                            <td class="py-4 px-3 text-center">
                                <span class="unit-cell px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold text-xs whitespace-nowrap">—</span>
                            </td>

                            {{-- Tồn kho hiện tại --}}
                            <td class="py-4 px-3 text-center font-mono">
                                <span class="stock-hint font-bold tabular-nums text-on-surface text-sm">—</span>
                            </td>

                            {{-- Số lượng --}}
                            <td class="py-4 px-3 text-right">
                                <input class="quantity-input w-full h-10 px-3 rounded-xl bg-surface-container-lowest border border-surface-container-high text-sm font-bold text-right font-mono tabular-nums focus:ring-1 focus:ring-primary focus:outline-none"
                                       type="number" name="items[0][quantity]" min="1" max="1000000"
                                       value="{{ old('items.0.quantity', $selectedRecommendation ? ($selectedRecommendation->ordered_quantity ?? $selectedRecommendation->approved_quantity) - $selectedRecommendation->received_quantity : '') }}"
                                       placeholder="SL" required>
                            </td>

                            {{-- Đơn giá --}}
                            <td class="py-4 px-3 text-right">
                                <input class="unit-price-input w-full h-10 px-3 rounded-xl bg-surface-container-lowest border border-surface-container-high text-sm text-right font-mono tabular-nums focus:ring-1 focus:ring-primary focus:outline-none"
                                       type="number" name="items[0][unit_price]" min="0" max="9999999999999.99" step="0.01"
                                       placeholder="Đơn giá">
                            </td>

                            {{-- Thành tiền từng dòng --}}
                            <td class="py-4 px-4 text-right">
                                <strong class="line-total text-[15px] font-bold text-on-surface font-mono tabular-nums">0 đ</strong>
                            </td>

                            {{-- Nút xóa dòng riêng biệt --}}
                            <td class="py-4 px-3 text-center">
                                <button type="button" class="w-9 h-9 rounded-xl inline-flex items-center justify-center text-error hover:bg-error-container/30 transition-colors"
                                        title="Xóa dòng này" onclick="removeInventoryRow(this)">
                                    <span class="material-symbols-outlined text-[19px]">delete</span>
                                </button>
                            </td>
                        </tr>
                    </x-ui.data-table>
                </div>

            </div>

            {{-- ================================================================= --}}
            {{-- CỘT PHẢI (Khoảng 33% - lg:col-span-4): Tổng kết & Thao tác        --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Card 1: Tổng kết phiếu nhập / xuất --}}
                <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm border border-surface-container-high/60 space-y-5" aria-live="polite">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
                        <h2 class="font-headline-sm text-sm font-bold text-on-surface">
                            {{ $type === 'import' ? 'Tổng kết phiếu nhập' : 'Kiểm tra trước khi xuất' }}
                        </h2>
                        <span class="w-2.5 h-2.5 rounded-full {{ $type === 'import' ? 'bg-[#1A4D38]' : 'bg-sky-600' }}"></span>
                    </div>

                    {{-- Các chỉ số tổng hợp --}}
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-surface-container-high/40">
                            <span class="text-on-surface-variant font-medium">Số loại mặt hàng</span>
                            <strong id="summary-items" class="text-on-surface font-bold text-sm tabular-nums">0</strong>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-surface-container-high/40">
                            <span class="text-on-surface-variant font-medium">Tổng số lượng</span>
                            <strong id="summary-quantity" class="text-on-surface font-bold text-sm tabular-nums">0</strong>
                        </div>
                        @if($type === 'export')
                            <div class="flex items-center justify-between py-1 border-b border-surface-container-high/40">
                                <span class="text-on-surface-variant font-medium">Dòng đủ tồn hợp lệ</span>
                                <strong id="summary-valid" class="text-emerald-700 font-bold text-sm tabular-nums">0 / 0</strong>
                            </div>
                        @endif
                    </div>

                    {{-- Tổng giá trị nổi bật --}}
                    <div class="p-4 rounded-xl {{ $type === 'import' ? 'bg-[#1A4D38] text-white' : 'bg-surface-container-low text-on-surface' }} border border-surface-container-high/70 space-y-1">
                        <span class="text-[11px] {{ $type === 'import' ? 'text-emerald-100/80' : 'text-on-surface-variant' }} font-semibold uppercase tracking-wider block">
                            {{ $type === 'import' ? 'Tổng giá trị nhập kho' : 'Tổng giá trị xuất hàng' }}
                        </span>
                        <div id="summary-value" class="text-2xl font-bold tracking-tight tabular-nums">0 đ</div>
                        <p class="text-[10px] {{ $type === 'import' ? 'text-emerald-100/70' : 'text-on-surface-variant/70' }}">
                            Tự động nhân từ số lượng × đơn giá từng dòng.
                        </p>
                    </div>

                    {{-- Cảnh báo an toàn kho --}}
                    @if($type === 'export')
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-amber-700 shrink-0 mt-0.5">warning</span>
                            <p class="text-[11px] leading-relaxed">
                                Tồn kho được kiểm tra lại khi xác nhận. Không thể xuất số lượng âm hoặc vượt tồn hiện tại trong kho.
                            </p>
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-emerald-700 shrink-0 mt-0.5">verified</span>
                            <p class="text-[11px] leading-relaxed">
                                Phiếu nhập sẽ cập nhật tăng tồn kho thực tế và ghi sổ nhật ký ngay khi lưu thành công.
                            </p>
                        </div>
                    @endif

                    {{-- Cụm nút hành động chính --}}
                    <div class="pt-2 space-y-2.5">
                        <button type="submit"
                                class="w-full h-11 rounded-xl bg-primary text-white font-label-md text-xs font-bold hover:bg-neutral-800 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            <span>{{ $type === 'import' ? 'Xác nhận lưu phiếu nhập' : 'Xác nhận lưu phiếu xuất' }}</span>
                        </button>

                        <button type="button" onclick="addInventoryRow()"
                                class="w-full h-10 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container font-label-md text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 border border-surface-container-high/60">
                            <span class="material-symbols-outlined text-[17px]">add</span>
                            <span>Thêm mặt hàng khác</span>
                        </button>

                        <a href="{{ route('inventory.index') }}"
                           class="w-full h-10 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low font-label-md text-xs font-medium transition-colors flex items-center justify-center">
                            Hủy bỏ phiếu này
                        </a>
                    </div>
                </div>

                {{-- Card 2: Quy chuẩn nghiệp vụ kho --}}
                <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm border border-surface-container-high/60 space-y-3">
                    <h3 class="font-headline-sm text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-[17px] text-[#1A4D38]">info</span>
                        <span>{{ $type === 'import' ? 'Quy chuẩn nhập kho' : 'Quy chuẩn xuất kho' }}</span>
                    </h3>
                    <ul class="text-[11px] text-on-surface-variant space-y-2 pl-4 list-disc">
                        <li>Kiểm tra ngoại quan bao bì và hạn sử dụng trước khi ghi nhận.</li>
                        <li>Chứng từ tham chiếu giúp đối soát sổ sách kế toán nhanh chóng.</li>
                        <li>Mọi thao tác đều được hệ thống lưu vết theo tài khoản người lập.</li>
                    </ul>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Template mẫu cho dòng sản phẩm mới khi nhấn Thêm dòng --}}
<template id="item-template">
    <tr class="item-row hover:bg-surface-container-low/70 transition-colors h-[72px]">
        <td class="py-4 px-4">
            <div class="space-y-1.5">
                <div class="relative">
                    <select name="items[__INDEX__][product_id]" class="product-select w-full h-10 pl-3 pr-7 rounded-xl bg-surface-container-lowest border border-surface-container-high text-xs font-semibold focus:ring-1 focus:ring-primary focus:outline-none appearance-none cursor-pointer" required>
                        <option value="">Chọn sản phẩm</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                                    data-unit="{{ $product->unit }}"
                                    data-import-price="{{ $product->purchase_price }}"
                                    data-export-price="{{ $product->selling_price }}">
                                {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                </div>

                @if($type === 'import')
                    <div class="relative">
                        <select name="items[__INDEX__][reorder_recommendation_id]" class="order-select w-full h-8 pl-2 pr-6 rounded-md bg-amber-50/60 border border-amber-200/80 text-[11px] text-amber-900 focus:outline-none appearance-none cursor-pointer">
                            <option value="">Nhập thường — không liên kết DSS</option>
                            @foreach($openRecommendations as $recommendation)
                                <option value="{{ $recommendation->id }}" data-product-id="{{ $recommendation->product_id }}">
                                    Đề xuất #{{ $recommendation->id }} · còn {{ $recommendation->approved_quantity - $recommendation->received_quantity }}
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-1.5 top-1/2 -translate-y-1/2 text-amber-700 pointer-events-none text-[15px]">expand_more</span>
                    </div>
                @endif
            </div>
        </td>
        <td class="py-4 px-3 text-center">
            <span class="unit-cell px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold text-xs whitespace-nowrap">—</span>
        </td>
        <td class="py-4 px-3 text-center font-mono">
            <span class="stock-hint font-bold tabular-nums text-on-surface text-sm">—</span>
        </td>
        <td class="py-4 px-3 text-right">
            <input class="quantity-input w-full h-10 px-3 rounded-xl bg-surface-container-lowest border border-surface-container-high text-sm font-bold text-right font-mono tabular-nums focus:ring-1 focus:ring-primary focus:outline-none"
                   type="number" name="items[__INDEX__][quantity]" min="1" max="1000000" placeholder="SL" required>
        </td>
        <td class="py-4 px-3 text-right">
            <input class="unit-price-input w-full h-10 px-3 rounded-xl bg-surface-container-lowest border border-surface-container-high text-sm text-right font-mono tabular-nums focus:ring-1 focus:ring-primary focus:outline-none"
                   type="number" name="items[__INDEX__][unit_price]" min="0" max="9999999999999.99" step="0.01" placeholder="Đơn giá">
        </td>
        <td class="py-4 px-4 text-right">
            <strong class="line-total text-[15px] font-bold text-on-surface font-mono tabular-nums">0 đ</strong>
        </td>
        <td class="py-4 px-3 text-center">
            <button type="button" class="w-9 h-9 rounded-xl inline-flex items-center justify-center text-error hover:bg-error-container/30 transition-colors"
                    title="Xóa dòng này" onclick="removeInventoryRow(this)">
                <span class="material-symbols-outlined text-[19px]">delete</span>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    /**
     * GIỮ NGUYÊN toàn bộ logic nghiệp vụ tính toán dòng sản phẩm, tự tính tiền, kiểm tra tồn
     */
    let nextInventoryIndex = 1;
    const transactionType = @json($type);

    function addInventoryRow() {
        const html = document.getElementById('item-template').innerHTML.replaceAll('__INDEX__', nextInventoryIndex++);
        document.getElementById('items').insertAdjacentHTML('beforeend', html);
        updateInventoryTotals();
    }

    function removeInventoryRow(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 1) {
            rows[0].querySelector('.product-select').value = '';
            rows[0].querySelector('[name$="[quantity]"]').value = '';
            rows[0].querySelector('[name$="[unit_price]"]').value = '';
            const orderSelect = rows[0].querySelector('.order-select');
            if (orderSelect) orderSelect.value = '';
            updateInventoryRow(rows[0]);
            updateInventoryTotals();
            return;
        }
        button.closest('tr').remove();
        updateInventoryTotals();
    }

    function updateInventoryRow(row) {
        const productSelect = row.querySelector('.product-select');
        const option = productSelect.options[productSelect.selectedIndex];
        const priceInput = row.querySelector('[name$="[unit_price]"]');
        const stockHint = row.querySelector('.stock-hint');
        const orderSelect = row.querySelector('.order-select');
        const unitCell = row.querySelector('.unit-cell');

        if (option?.value) {
            priceInput.value = transactionType === 'import' ? option.dataset.importPrice : option.dataset.exportPrice;
            if (unitCell) unitCell.textContent = option.dataset.unit;
            if (stockHint) stockHint.textContent = `${option.dataset.stock}`;
        } else {
            if (unitCell) unitCell.textContent = '—';
            if (stockHint) stockHint.textContent = '—';
            priceInput.value = '';
        }

        if (orderSelect) {
            Array.from(orderSelect.options).forEach(item => {
                if (item.value) item.disabled = item.dataset.productId !== productSelect.value;
            });
            if (orderSelect.selectedOptions[0]?.disabled) orderSelect.value = '';
        }
        updateInventoryTotals();
        updateStockWarning(row);
    }

    function updateStockWarning(row) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const hint = row.querySelector('.stock-hint');
        const option = select.options[select.selectedIndex];
        const stock = Number(option?.dataset.stock ?? 0);
        const quantity = Number(quantityInput.value);
        const exceedsStock = transactionType === 'export' && option?.value && quantity > stock;

        quantityInput.setCustomValidity(exceedsStock ? `Số lượng xuất vượt tồn hiện tại (${stock}).` : '');
        if (hint) {
            if (exceedsStock) {
                hint.classList.add('text-error');
                hint.classList.remove('text-on-surface');
                hint.textContent = `Vượt tồn: chỉ còn ${stock}`;
            } else {
                hint.classList.remove('text-error');
                hint.classList.add('text-on-surface');
                if (option?.value) {
                    hint.textContent = `${stock}`;
                }
            }
        }
    }

    function updateInventoryTotals() {
        const rows = Array.from(document.querySelectorAll('.item-row'));
        const itemCount = rows.filter(row => row.querySelector('.product-select').value).length;
        const totalQuantity = rows.reduce((sum, row) => sum + (Number(row.querySelector('.quantity-input').value) || 0), 0);
        const totalValue = rows.reduce((sum, row) => {
            const quantity = Number(row.querySelector('.quantity-input').value) || 0;
            const price = Number(row.querySelector('.unit-price-input').value) || 0;
            const line = row.querySelector('.line-total');
            if (line) {
                line.textContent = `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(quantity * price)} đ`;
            }
            return sum + quantity * price;
        }, 0);
        const currency = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 });
        const validRows = rows.filter(row => {
            const select = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const stock = Number(select.selectedOptions[0]?.dataset.stock ?? 0);
            const quantity = Number(quantityInput.value);
            return select.value && quantity > 0 && (transactionType !== 'export' || quantity <= stock);
        }).length;

        const summaryItems = document.getElementById('summary-items');
        const summaryQuantity = document.getElementById('summary-quantity');
        const summaryValue = document.getElementById('summary-value');
        const summaryValid = document.getElementById('summary-valid');

        if (summaryItems) summaryItems.textContent = currency.format(itemCount);
        if (summaryQuantity) summaryQuantity.textContent = currency.format(totalQuantity);
        if (summaryValue) summaryValue.textContent = `${currency.format(totalValue)} đ`;
        if (summaryValid) summaryValid.textContent = `${validRows} / ${itemCount}`;
    }

    document.addEventListener('change', event => {
        if (event.target.matches('.product-select')) updateInventoryRow(event.target.closest('tr'));
        if (event.target.matches('.order-select') && event.target.value) {
            const row = event.target.closest('tr');
            row.querySelector('.product-select').value = event.target.selectedOptions[0].dataset.productId;
            updateInventoryRow(row);
        }
        if (event.target.matches('.quantity-input, .unit-price-input')) {
            const row = event.target.closest('tr');
            updateStockWarning(row);
            updateInventoryTotals();
        }
    });

    document.addEventListener('input', event => {
        if (event.target.matches('.quantity-input, .unit-price-input')) {
            const row = event.target.closest('tr');
            updateStockWarning(row);
            updateInventoryTotals();
        }
    });

    document.querySelectorAll('.item-row').forEach(updateInventoryRow);
    updateInventoryTotals();
</script>
@endpush
@endsection
