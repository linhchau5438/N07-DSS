{{--
    Đường dẫn: resources/views/inventory/index.blade.php
    Mô tả: Giao diện Tồn kho & Sổ kho giao dịch theo Monochrome Design System, sử dụng các component dùng chung x-ui.*.
    Phụ thuộc: InventoryController@index, layouts/app.blade.php, pagination/n07.blade.php.
--}}
@extends('layouts.app')

@php
    $productsCount = $productsCount ?? (isset($products) ? $products->total() : 0);
    $transactionsCount = $transactionsCount ?? (isset($transactions) ? (method_exists($transactions, 'total') ? $transactions->total() : $transactions->count()) : 0);
    $currentTab = request('tab', 'stock');
@endphp

@section('title', $currentTab === 'history' ? 'Lịch sử nhập xuất kho' : 'Tồn kho hiện tại')
@section('page-title', 'Quản lý tồn kho')
@section('page-subtitle', 'Theo dõi lượng hàng, mức an toàn và lịch sử giao dịch kho vận.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER DÙNG PARTIAL CHUNG --}}
    @include('inventory.partials.header', ['productsCount' => $productsCount])

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- Thanh chuyển Tab độc lập (Tồn kho / Lịch sử) --}}
        @include('inventory.partials.tabs', [
            'currentTab' => $currentTab,
            'productsCount' => $productsCount,
            'transactionsCount' => $transactionsCount,
        ])

        @if($currentTab === 'history')
            {{-- ===================================================================== --}}
            {{-- TAB 2: NỘI DUNG LỊCH SỬ NHẬP XUẤT                                     --}}
            {{-- ===================================================================== --}}
            <div class="space-y-6">
                {{-- TOOLBAR TÌM KIẾM & BỘ LỌC --}}
                <x-ui.toolbar :action="route('inventory.index')"
                              :resetUrl="route('inventory.index', ['tab' => 'history'])"
                              :hasFilter="$search || (($filters['stock'] ?? 'all') !== 'all')">
                    <input type="hidden" name="tab" value="history">
                    <x-ui.search-input id="historySearchInput"
                                       name="search"
                                       :value="$search"
                                       placeholder="Tìm mã phiếu, người lập, số chứng từ, ghi chú..."
                                       :autoSubmit="true"
                                       class="flex-1" />
                </x-ui.toolbar>

                {{-- BẢNG DỮ LIỆU LỊCH SỬ GIAO DỊCH --}}
                <x-ui.data-table minWidth="min-w-[1050px]">
                    <x-slot:head>
                        <th class="py-3.5 pl-6 pr-2 min-w-[160px]">Mã phiếu</th>
                        <th class="py-3.5 px-5 min-w-[140px] text-center">Phân loại</th>
                        <th class="py-3.5 px-5 min-w-[150px]">Ngày thực hiện</th>
                        <th class="py-3.5 px-5 min-w-[180px]">Người phụ trách</th>
                        <th class="py-3.5 px-5 text-right min-w-[160px]">Tổng giá trị</th>
                        <th class="py-3.5 px-5 min-w-[220px]">Ghi chú</th>
                        <th class="py-3.5 pr-6 pl-2 text-right w-24 min-w-[96px] whitespace-nowrap">Thao tác</th>
                    </x-slot:head>

                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                            <x-ui.cell-code :code="$transaction->transaction_code"
                                            :href="route('inventory.show', $transaction->id)"
                                            class="pl-6" />

                            <td class="py-5 px-5 text-center whitespace-nowrap">
                                <x-ui.status-pill :variant="$transaction->type === 'import' ? 'success' : 'info'"
                                                  :label="$transaction->type === 'import' ? 'Nhập kho' : 'Xuất kho'" />
                            </td>

                            <x-ui.cell-date :date="$transaction->transaction_date" />

                            <td class="py-5 px-5 text-on-surface font-medium text-[15px] whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center text-xs font-bold text-on-surface-variant shrink-0 shadow-2xs">
                                        {{ mb_strtoupper(mb_substr($transaction->staff_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span>{{ $transaction->staff_name ?? 'Admin N07' }}</span>
                                </div>
                            </td>

                            <x-ui.cell-money :amount="$transaction->total_value" />

                            <td class="py-5 px-5 text-on-surface-variant text-[15px]">
                                <span class="line-clamp-1 max-w-xs">{{ $transaction->note ?: ($transaction->reference_no ? 'Số CT: ' . $transaction->reference_no : '—') }}</span>
                            </td>

                            <td class="py-5 pr-6 pl-2 text-right">
                                <x-ui.row-actions-menu>
                                    <a href="{{ route('inventory.show', $transaction->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-on-surface-variant">description</span>
                                        <span>Xem chi tiết phiếu</span>
                                    </a>
                                    <button type="button" onclick="window.open('{{ route('inventory.show', $transaction->id) }}', '_blank')" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-on-surface-variant">print</span>
                                        <span>In phiếu giao dịch</span>
                                    </button>
                                </x-ui.row-actions-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <x-ui.empty-state icon="history"
                                                  title="Chưa có giao dịch nhập xuất nào"
                                                  message="Khi lập phiếu nhập kho hoặc xuất kho, lịch sử giao dịch sẽ được tự động lưu trữ tại đây."
                                                  :actionUrl="route('inventory.index', ['tab' => 'history'])"
                                                  actionText="Xóa bộ lọc"
                                                  actionIcon="restart_alt" />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.data-table>
            </div>
        @else
            {{-- ===================================================================== --}}
            {{-- TAB 1: NỘI DUNG TỒN KHO HIỆN TẠI                                       --}}
            {{-- ===================================================================== --}}
            <div class="space-y-6">

                {{-- A. 4 THẺ CHỈ SỐ KPI TỒN KHO GỌN 140PX ĐƠN SẮC MONOCHROME --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-ui.kpi-card label="Sản phẩm đang kinh doanh"
                                   :value="number_format($metrics->active_product_count ?? 0)"
                                   unit="mã"
                                   icon="inventory_2"
                                   tone="sage"
                                   pill="Hoạt động"
                                   status="success">
                        <span class="truncate">Mặt hàng hoạt động trong kho</span>
                    </x-ui.kpi-card>

                    <x-ui.kpi-card label="Tổng giá trị tồn kho"
                                   :value="number_format($metrics->inventory_value ?? 0, 0, ',', '.')"
                                   unit="đ"
                                   icon="payments"
                                   :highlight="true">
                        <span class="truncate">Ước tính theo giá nhập hiện tại</span>
                    </x-ui.kpi-card>

                    <x-ui.kpi-card label="Sắp hết / Hết hàng"
                                   :value="number_format($metrics->low_stock_count ?? 0)"
                                   unit="mã"
                                   icon="warning"
                                   tone="sage"
                                   :pill="($metrics->low_stock_count ?? 0) > 0 ? 'Cảnh báo' : 'Đầy đủ'"
                                   :status="($metrics->low_stock_count ?? 0) > 0 ? 'danger' : 'success'">
                        <span class="truncate">Theo ngưỡng cảnh báo tồn tối thiểu</span>
                    </x-ui.kpi-card>

                    <x-ui.kpi-card label="Đề xuất cần xem xét"
                                   :value="number_format($metrics->reorder_count ?? 0)"
                                   unit="mặt hàng"
                                   icon="playlist_add_check"
                                   tone="grey"
                                   pill="DSS"
                                   status="warning">
                        <span class="truncate">Không tự động tạo phiếu nhập</span>
                    </x-ui.kpi-card>
                </div>

                {{-- B. BANNER GỢI Ý BỔ SUNG DSS --}}
                @if(($metrics->reorder_count ?? 0) > 0)
                    <x-ui.banner-hint icon="tips_and_updates"
                                      :title="number_format($metrics->reorder_count) . ' mặt hàng có gợi ý bổ sung'"
                                      message="Kiểm tra lượng tồn và đề xuất tự động trước khi lập phiếu nhập."
                                      actionText="Xem đề xuất"
                                      :actionUrl="route('dss.index')" />
                @endif

                {{-- C. TOOLBAR TÌM KIẾM & BỘ LỌC --}}
                <x-ui.toolbar :action="route('inventory.index')"
                              :resetUrl="route('inventory.index')"
                              :hasFilter="$search || !empty($filters['category_id']) || (($filters['stock'] ?? 'all') !== 'all')">
                    <x-ui.search-input id="inventorySearchInput"
                                       name="search"
                                       :value="$search"
                                       placeholder="Tìm theo mã SKU, tên mặt hàng (⌘K)..."
                                       :autoSubmit="true"
                                       class="flex-1" />

                    <x-ui.filter-select name="category_id" width="w-full sm:w-56">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-ui.filter-select>

                    <x-ui.filter-select name="stock" width="w-full sm:w-48">
                        <option value="all" @selected(($filters['stock'] ?? 'all') === 'all')>Tất cả mức tồn</option>
                        <option value="attention" @selected(($filters['stock'] ?? '') === 'attention')>⚠️ Cần chú ý (ROP / Hết)</option>
                        <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Tồn thấp (≤ 10)</option>
                        <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Hết hàng (= 0)</option>
                    </x-ui.filter-select>
                </x-ui.toolbar>

                {{-- D. BẢNG DỮ LIỆU TỒN KHO SẢN PHẨM --}}
                <x-ui.data-table minWidth="min-w-[1150px]">
                    <x-slot:head>
                        <th class="py-3.5 pl-6 pr-2 min-w-[280px]">Mặt hàng</th>
                        <th class="py-3.5 px-5 min-w-[140px]">Danh mục</th>
                        <th class="py-3.5 px-5 text-right min-w-[120px]">Tồn thực tế</th>
                        <th class="py-3.5 px-5 text-right min-w-[120px]">Hàng đang về</th>
                        <th class="py-3.5 px-5 text-right min-w-[130px]">Vị thế tồn (IP)</th>
                        <th class="py-3.5 px-5 text-right min-w-[120px]">Điểm ROP</th>
                        <th class="py-3.5 px-5 text-center min-w-[130px]">Trạng thái</th>
                        <th class="py-3.5 pr-6 pl-2 text-right w-24 min-w-[96px] whitespace-nowrap">Thao tác</th>
                    </x-slot:head>

                    @forelse($products as $product)
                        <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                            <td class="py-5 pl-6 pr-2">
                                <x-ui.product-cell :title="$product->name"
                                                   :subtitle="'SKU: ' . $product->product_code . ' · ĐVT: ' . $product->unit"
                                                   :url="route('products.show', $product->id)" />
                            </td>

                            <td class="py-5 px-5 whitespace-nowrap text-on-surface-variant font-medium text-[15px]">
                                {{ $product->category_name }}
                            </td>

                            <td class="py-5 px-5 text-right whitespace-nowrap font-mono font-bold text-[15px] {{ $product->on_hand <= 0 ? 'text-error' : 'text-on-surface' }}">
                                {{ number_format($product->on_hand) }} {{ $product->unit }}
                            </td>

                            <td class="py-5 px-5 text-right whitespace-nowrap font-mono text-[15px]">
                                @if(($product->on_order ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1 text-secondary font-bold" title="Hàng nhà cung cấp đang giao">
                                        <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                        +{{ number_format($product->on_order) }}
                                    </span>
                                @else
                                    <span class="text-on-surface-variant/60 font-medium">0</span>
                                @endif
                            </td>

                            <td class="py-5 px-5 text-right whitespace-nowrap font-mono font-bold text-[15px] text-secondary">
                                {{ number_format($product->inventory_position ?? $product->on_hand) }}
                            </td>

                            <td class="py-5 px-5 text-right whitespace-nowrap font-mono font-medium text-[15px] text-on-surface">
                                {{ $product->reorder_point !== null ? number_format($product->reorder_point, 1) : '—' }}
                            </td>

                            <td class="py-5 px-5 text-center whitespace-nowrap">
                                @if($product->on_hand <= 0)
                                    <x-ui.status-pill variant="danger" label="Hết hàng" />
                                @elseif($product->reorder_point !== null && ($product->inventory_position ?? $product->on_hand) <= $product->reorder_point)
                                    <x-ui.status-pill variant="amber" label="Cần nhập" />
                                @elseif($product->on_hand <= 10)
                                    <x-ui.status-pill variant="warning" label="Tồn thấp" />
                                @else
                                    <x-ui.status-pill variant="success" label="Đủ hàng" />
                                @endif
                            </td>

                            <td class="py-5 pr-6 pl-2 text-right">
                                <x-ui.row-actions-menu>
                                    <a href="{{ route('products.show', $product->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-on-surface-variant">visibility</span>
                                        <span>Xem chi tiết</span>
                                    </a>
                                    <a href="{{ route('inventory.create', ['type' => 'import', 'product_id' => $product->id]) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-secondary">archive</span>
                                        <span>Lập phiếu nhập</span>
                                    </a>
                                    <a href="{{ route('inventory.create', ['type' => 'export', 'product_id' => $product->id]) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-primary">unarchive</span>
                                        <span>Lập phiếu xuất</span>
                                    </a>
                                </x-ui.row-actions-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-0 border-0">
                                <x-ui.empty-state icon="inventory_2"
                                                  title="Không tìm thấy sản phẩm tồn kho"
                                                  message="Không có mặt hàng nào phù hợp với từ khóa hoặc điều kiện lọc hiện tại."
                                                  :actionUrl="route('inventory.index')"
                                                  actionText="Xóa bộ lọc"
                                                  actionIcon="restart_alt" />
                            </td>
                        </tr>
                    @endforelse
                </x-ui.data-table>

                {{-- Phân trang chuẩn N07 --}}
                <div class="pt-2">
                    {{ $products->links('pagination.n07') }}
                </div>
            </div>
        @endif

    </x-ui.section-card>
</div>
@endsection
