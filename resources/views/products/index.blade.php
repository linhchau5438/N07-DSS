{{-- File: resources/views/products/index.blade.php --}}
{{-- Mô tả: Giao diện Quản lý Danh sách sản phẩm chuẩn Design System N07 Monochrome, đồng bộ cấu trúc 2 khung với Dashboard (Khung 1: Header chào; Khung 2: Card lớn chứa KPI, thanh công cụ tìm kiếm debounce, dải lọc tức thì, chế độ Bảng & Lưới sản phẩm, phân trang chuẩn). --}}
{{-- Phụ thuộc: resources/views/layouts/app.blade.php, ProductController@index, categories, products, n07 pagination. --}}

@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')
@section('page-title', 'Danh sách sản phẩm')

@section('content')
{{-- Nối dữ liệu: Nhận các biến KPI đã được tính toán và cache từ ProductController@index --}}
@php
    $totalProductsCount = $totalProductsCount ?? 0;
    $activeProductsCount = $activeProductsCount ?? 0;
    $inactiveProductsCount = $inactiveProductsCount ?? 0;
    $lowStockCount = $lowStockCount ?? 0;
    $outOfStockCount = $outOfStockCount ?? 0;

    if ($totalProductsCount === 0 && isset($products)) {
        $totalProductsCount = $products->total();
        $activeProductsCount = $products->total();
    }
    $activePercent = $totalProductsCount > 0 ? round(($activeProductsCount / $totalProductsCount) * 100, 1) : 0;

    // Số bộ lọc đang kích hoạt
    $activeFilterCount = 0;
    if (!empty($filters['category_id'])) $activeFilterCount++;
    if (!empty($filters['status']) && $filters['status'] !== 'all') $activeFilterCount++;

    // Chế độ xem: Bảng (table) hoặc Lưới (grid)
    $viewMode = request('view', 'table');
@endphp

<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- ========================================================================= --}}
    {{-- Khung 1: Header trang dùng x-ui.page-header (Card trắng rounded-2xl)       --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Danh sách sản phẩm"
                      chip="Kho Tổng Miền Bắc">
        <x-slot:subtitle>
            Tổng cộng <strong class="text-on-surface font-semibold">{{ number_format($products->total()) }}</strong> mặt hàng •
            <strong class="text-on-surface font-semibold">{{ $categories->count() }}</strong> danh mục đang quản lý •
            <span class="inline-flex items-center gap-1 text-secondary font-medium">
                <span class="material-symbols-outlined text-[15px]">sync</span>
                Đồng bộ vừa xong
            </span>
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Nút Quản lý danh mục (Chỉ Admin) --}}
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('categories.index') }}"
                   class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[19px] text-on-surface-variant">category</span>
                    <span>Quản lý danh mục</span>
                </a>
            @endif

            {{-- Nhóm nút liền Nhập / Xuất Excel trong khung bo tròn --}}
            <div class="flex items-center p-0.5 rounded-xl bg-surface-container-low border border-surface-container-high/60">
                <button type="button" onclick="alert('Tính năng nhập Excel theo mẫu chuẩn đang được đồng bộ.')"
                        class="flex items-center gap-1.5 h-9 px-3 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-lowest font-label-md text-xs transition-colors"
                        title="Nhập danh sách từ Excel">
                    <span class="material-symbols-outlined text-[17px]">upload_file</span>
                    <span>Nhập</span>
                </button>
                <div class="h-4 w-px bg-surface-container-high mx-0.5"></div>
                <button type="button" onclick="window.print()"
                        class="flex items-center gap-1.5 h-9 px-3 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-lowest font-label-md text-xs transition-colors"
                        title="Xuất danh sách sản phẩm">
                    <span class="material-symbols-outlined text-[17px]">download</span>
                    <span>Xuất</span>
                </button>
            </div>

            {{-- Nút chính: Thêm sản phẩm (Chỉ Admin) --}}
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('products.create') }}"
                   class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-primary text-white shadow-sm hover:bg-neutral-800 transition-all font-label-md text-label-md active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[19px] text-white">add</span>
                    <span class="text-white">Thêm sản phẩm</span>
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- Khung 2: Card Lớn Duy Nhất dùng x-ui.section-card                         --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-8">

        {{-- ===================================================================== --}}
        {{-- Hàng 1: Hệ thống 4 thẻ KPI đơn sắc Monochrome Design System gọn 140px  --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Thẻ 1: Tích cực (Đang kinh doanh) --}}
            <x-ui.kpi-card label="Đang kinh doanh"
                           :value="number_format($activeProductsCount)"
                           unit="mã"
                           icon="check_circle"
                           tone="sage"
                           pill="Đang mở bán"
                           status="success">
                <span class="truncate">{{ $activePercent }}% tổng mặt hàng đang lưu kho</span>
            </x-ui.kpi-card>

            {{-- Thẻ 2: Cần chú ý (Sắp hết hàng) --}}
            <x-ui.kpi-card label="Sắp hết hàng"
                           :value="number_format($lowStockCount)"
                           unit="mã"
                           tone="grey"
                           pill="Cảnh báo"
                           icon="warning"
                           status="warning">
                <span class="truncate">Tồn kho dưới ngưỡng 10 kiện</span>
            </x-ui.kpi-card>

            {{-- Thẻ 3: Nguy cấp (Hết hàng cần nhập) --}}
            <x-ui.kpi-card label="Hết hàng cần nhập"
                           :value="number_format($outOfStockCount)"
                           unit="mã"
                           tone="sage"
                           pill="Hết hàng"
                           icon="production_quantity_limits"
                           status="danger">
                <span class="truncate">Tồn thực tế chạm 0 cần bổ sung</span>
            </x-ui.kpi-card>

            {{-- Thẻ 4: Trung tính (Tạm ngừng bán) --}}
            <x-ui.kpi-card label="Tạm ngừng bán"
                           :value="number_format($inactiveProductsCount)"
                           unit="mã"
                           tone="grey"
                           pill="Khóa xuất"
                           icon="block"
                           status="neutral">
                <span class="truncate">Đang ngưng xuất nhập lưu kho</span>
            </x-ui.kpi-card>
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 2: Toolbar Tìm kiếm & Bộ lọc chuẩn hóa duy nhất một hàng h-11     --}}
        {{-- SỬA: Dùng x-ui.toolbar một hàng, không dải xám phụ, không dòng chữ thừa --}}
        {{-- ===================================================================== --}}
        <div>
            @php
                $hasActiveFilters = $search || !empty($filters['category_id']) || (($filters['status'] ?? 'all') !== 'all');
            @endphp
            <x-ui.toolbar action="{{ route('products.index') }}"
                          method="GET"
                          :hasFilter="$hasActiveFilters"
                          :resetUrl="route('products.index', ['view' => $viewMode])">
                {{-- Giữ nguyên chế độ xem view=table|grid khi tìm kiếm hoặc lọc --}}
                <input type="hidden" name="view" id="viewModeInput" value="{{ $viewMode }}">

                {{-- Ô tìm kiếm dùng chung x-ui.search-input (flex-1) --}}
                <x-ui.search-input id="searchInput"
                                   name="search"
                                   :value="$search"
                                   placeholder="Tìm theo mã, tên hoặc SKU (⌘K)..."
                                   :autoSubmit="true"
                                   class="flex-1" />

                {{-- Select 1: Danh mục hàng hóa (tối đa 3 inline) --}}
                <x-ui.filter-select name="category_id" width="w-full sm:w-56">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </x-ui.filter-select>

                {{-- Select 2: Trạng thái kinh doanh --}}
                <x-ui.filter-select name="status" width="w-full sm:w-48">
                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Đang kinh doanh</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Ngừng kinh doanh</option>
                </x-ui.filter-select>

                {{-- Nút chuyển đổi chế độ xem Bảng / Lưới dùng x-ui.view-toggle --}}
                <x-ui.view-toggle :current="$viewMode" />
            </x-ui.toolbar>
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 3A: Chế độ xem BẢNG (Table View) dùng x-ui.data-table            --}}
        {{-- ===================================================================== --}}
        <div id="tableViewContainer" class="{{ $viewMode === 'table' ? 'block' : 'hidden' }}">
            <x-ui.data-table minWidth="min-w-[1100px]">
                <x-slot:head>
                    <th class="py-3.5 pl-6 pr-2 w-14 text-center">
                        <input type="checkbox" id="checkAllTable" class="rounded border-surface-container-high text-primary focus:ring-primary w-[18px] h-[18px] cursor-pointer" title="Chọn tất cả">
                    </th>
                    <th class="py-3.5 px-5 min-w-[260px]">Sản phẩm & Mã SKU</th>
                    <th class="py-3.5 px-5">Danh mục</th>
                    <th class="py-3.5 px-5 text-center w-28">Đơn vị</th>
                    <th class="py-3.5 px-5 min-w-[180px]">Tồn kho hiện tại</th>
                    <th class="py-3.5 px-5 text-right w-36">Giá nhập</th>
                    <th class="py-3.5 px-5 text-right w-36">Giá bán</th>
                    <th class="py-3.5 px-5 text-center w-36">Trạng thái</th>
                    <th class="py-3.5 pr-6 pl-2 text-right w-24 min-w-[96px] whitespace-nowrap">Thao tác</th>
                </x-slot:head>

                @forelse($products as $product)
                    @php
                        $onHand = (int) ($product->on_hand ?? 0);
                    @endphp
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px] group">
                        {{-- Checkbox dòng 18px --}}
                        <td class="py-5 pl-6 pr-2 text-center">
                            <input type="checkbox" class="product-checkbox rounded border-surface-container-high text-primary focus:ring-primary w-[18px] h-[18px] cursor-pointer" value="{{ $product->id }}">
                        </td>

                        {{-- Sản phẩm & Mã SKU dùng x-ui.product-cell --}}
                        <td class="py-5 px-5">
                            <x-ui.product-cell :name="$product->name"
                                               :sku="$product->product_code"
                                               :image="$product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg')"
                                               :url="route('products.show', $product->id)" />
                        </td>

                        {{-- Danh mục --}}
                        <td class="py-5 px-5">
                            <span class="text-[15px] text-on-surface font-medium whitespace-nowrap">{{ $product->category_name }}</span>
                        </td>

                        {{-- Đơn vị tính --}}
                        <td class="py-5 px-5 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant text-xs font-semibold whitespace-nowrap">
                                {{ $product->unit }}
                            </span>
                        </td>

                        {{-- Tồn kho hiện tại dùng x-ui.stock-progress --}}
                        <td class="py-5 px-5">
                            <x-ui.stock-progress :onHand="$onHand" :unit="$product->unit" />
                        </td>

                        {{-- Giá nhập chống gãy dòng dùng x-ui.cell-money --}}
                        <x-ui.cell-money :amount="$product->purchase_price" class="text-on-surface-variant" />

                        {{-- Giá bán chống gãy dòng dùng x-ui.cell-money --}}
                        <x-ui.cell-money :amount="$product->selling_price" />

                        {{-- Trạng thái kinh doanh dùng x-ui.status-pill --}}
                        <td class="py-5 px-5 text-center">
                            <x-ui.status-pill :variant="$product->status == 1 ? 'success' : 'neutral'"
                                              :label="$product->status == 1 ? 'Đang kinh doanh' : 'Ngừng bán'" />
                        </td>

                        {{-- Cột Thao tác: Menu ba chấm x-ui.row-actions-menu dùng popup fixed chung --}}
                        <td class="py-5 pr-6 pl-2 text-right">
                            <x-ui.row-actions-menu>
                                <a href="{{ route('products.show', $product->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                    <span class="material-symbols-outlined text-[17px] text-on-surface-variant">visibility</span>
                                    <span>Xem chi tiết</span>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('products.edit', $product->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                        <span class="material-symbols-outlined text-[17px] text-on-surface-variant">edit</span>
                                        <span>Chỉnh sửa</span>
                                    </a>
                                    <form action="{{ route('products.status', $product->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                            <span class="material-symbols-outlined text-[17px] text-on-surface-variant">toggle_on</span>
                                            <span>{{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }}</span>
                                        </button>
                                    </form>
                                    <div class="border-t border-surface-container-high/60 my-1"></div>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm {{ $product->name }}?');" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-error hover:bg-error-container/20 transition-colors font-semibold">
                                            <span class="material-symbols-outlined text-[17px]">delete</span>
                                            <span>Xóa sản phẩm</span>
                                        </button>
                                    </form>
                                @endif
                            </x-ui.row-actions-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-0 border-0">
                            <x-ui.empty-state icon="inventory_2"
                                              title="Không tìm thấy sản phẩm"
                                              message="Chưa có sản phẩm nào phù hợp với từ khóa hoặc bộ lọc đã chọn."
                                              :actionUrl="route('products.index', ['view' => $viewMode])"
                                              actionText="Đặt lại bộ lọc"
                                              actionIcon="restart_alt" />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 3B: Chế độ xem LƯỚI (Grid View) theo đúng mẫu                     --}}
        {{-- MỚI: Card sản phẩm đa năng, thanh tiến độ tồn kho, chọn nhiều, 3 chấm  --}}
        {{-- ===================================================================== --}}
        <div id="gridViewContainer" class="{{ $viewMode === 'grid' ? 'block' : 'hidden' }}">
            @if($products->isNotEmpty())
                {{-- Thanh chọn tất cả ở chế độ lưới --}}
                <div class="mb-4 px-2 flex items-center justify-between text-xs text-on-surface-variant select-none">
                    <label class="flex items-center gap-2 cursor-pointer font-medium hover:text-on-surface">
                        <input type="checkbox" id="checkAllGrid" class="rounded border-surface-container-high text-[#1A4D38] focus:ring-[#1A4D38] w-4 h-4 cursor-pointer">
                        <span>Chọn tất cả sản phẩm trên trang này</span>
                    </label>
                    <span class="text-xs text-on-surface-variant/80">Hiển thị dạng thẻ lưới</span>
                </div>

                {{-- Lưới thẻ sản phẩm (Grid 1 -> 2 -> 3 -> 4 cột) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($products as $product)
                        @php
                            $onHand = (int) ($product->on_hand ?? 0);
                            $maxDisplay = 50;
                            $stockWidth = $onHand <= 0 ? 0 : min(100, max(8, round(($onHand / $maxDisplay) * 100)));
                        @endphp
                        <div class="relative bg-surface-container-lowest border border-surface-container-high/80 hover:border-surface-container-highest rounded-2xl p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
                            {{-- Header thẻ: Checkbox + Chip Danh mục + Nút ba chấm --}}
                            <div>
                                <div class="flex items-center justify-between gap-2 pb-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" class="product-checkbox rounded border-surface-container-high text-[#1A4D38] focus:ring-[#1A4D38] w-4 h-4 cursor-pointer" value="{{ $product->id }}">
                                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-surface-container-low text-on-surface-variant font-label-sm text-[11px] font-semibold truncate max-w-[130px]" title="{{ $product->category_name }}">
                                            {{ $product->category_name }}
                                        </span>
                                    </div>

                                    {{-- Nút ba chấm x-ui.row-actions-menu dùng popup fixed chung --}}
                                    <x-ui.row-actions-menu>
                                        <a href="{{ route('products.show', $product->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                            <span class="material-symbols-outlined text-[17px] text-on-surface-variant">visibility</span>
                                            <span>Xem chi tiết</span>
                                        </a>
                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('products.edit', $product->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                                <span class="material-symbols-outlined text-[17px] text-on-surface-variant">edit</span>
                                                <span>Chỉnh sửa</span>
                                            </a>
                                            <form action="{{ route('products.status', $product->id) }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                                    <span class="material-symbols-outlined text-[17px] text-on-surface-variant">toggle_on</span>
                                                    <span>{{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }}</span>
                                                </button>
                                            </form>
                                            <div class="border-t border-surface-container-high/60 my-1"></div>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm {{ $product->name }}?');" class="m-0 p-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-error hover:bg-error-container/20 transition-colors font-semibold">
                                                    <span class="material-symbols-outlined text-[17px]">delete</span>
                                                    <span>Xóa sản phẩm</span>
                                                </button>
                                            </form>
                                        @endif
                                    </x-ui.row-actions-menu>
                                </div>

                                {{-- Khung ảnh sản phẩm + Pill trạng thái nổi --}}
                                <div class="relative w-full h-36 rounded-xl bg-surface-container-low/60 border border-surface-container-high/40 overflow-hidden flex items-center justify-center my-2">
                                    <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                                    
                                    <div class="absolute bottom-2 left-2">
                                        @if($product->status == 1)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/95 text-emerald-800 border border-emerald-200/80 shadow-xs backdrop-blur-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Kinh doanh
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/95 text-slate-700 border border-slate-200 shadow-xs backdrop-blur-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Ngừng bán
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Tên sản phẩm, Mã SKU & Đơn vị tính --}}
                                <div class="pt-1">
                                    <a href="{{ route('products.show', $product->id) }}"
                                       class="font-bold text-on-surface hover:text-[#1A4D38] text-sm line-clamp-2 leading-snug transition-colors min-h-[2.5rem]"
                                       title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </a>
                                    <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1.5">
                                        <span class="font-mono uppercase tracking-wider font-semibold">{{ $product->product_code }}</span>
                                        <span class="px-2 py-0.5 rounded bg-surface-container-high/70 text-on-surface font-semibold text-[10px]">{{ $product->unit }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer thẻ: Thanh tiến độ tồn kho & Giá nhập/bán --}}
                            <div>
                                {{-- Thanh tiến độ tồn kho (Progress Bar theo mức an toàn/cảnh báo) --}}
                                <div class="mt-3 pt-3 border-t border-surface-container-high/60 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-on-surface-variant">Tồn kho: <strong class="text-on-surface tabular-nums">{{ number_format($onHand) }}</strong></span>
                                        @if($onHand <= 0)
                                            <span class="text-[10px] font-bold text-error flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Hết hàng
                                            </span>
                                        @elseif($onHand <= 10)
                                            <span class="text-[10px] font-bold text-amber-700 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dưới mức
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-secondary flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> An toàn
                                            </span>
                                        @endif
                                    </div>
                                    <div class="w-full h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                                        @if($onHand <= 0)
                                            <div class="h-full bg-error rounded-full" style="width: 0%"></div>
                                        @elseif($onHand <= 10)
                                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ $stockWidth }}%"></div>
                                        @else
                                            <div class="h-full bg-[#1A4D38] rounded-full" style="width: {{ $stockWidth }}%"></div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Giá nhập & Giá bán --}}
                                <div class="mt-3 pt-2.5 border-t border-surface-container-high/60 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider text-on-surface-variant block font-medium">Giá nhập</span>
                                        <span class="text-xs text-on-surface-variant tabular-nums">{{ number_format($product->purchase_price, 0, ',', '.') }} ₫</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] uppercase tracking-wider text-on-surface-variant block font-medium">Giá bán</span>
                                        <span class="text-sm font-bold text-on-surface tabular-nums">{{ number_format($product->selling_price, 0, ',', '.') }} ₫</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Trạng thái rỗng ở chế độ lưới dùng x-ui.empty-state --}}
                <div class="py-12 border border-surface-container-high/60 rounded-xl bg-surface-container-low/20">
                    <x-ui.empty-state icon="inventory_2"
                                      title="Không tìm thấy sản phẩm"
                                      message="Chưa có sản phẩm nào phù hợp với từ khóa hoặc bộ lọc đã chọn."
                                      :actionUrl="route('products.index', ['view' => $viewMode])"
                                      actionText="Đặt lại bộ lọc"
                                      actionIcon="restart_alt" />
                </div>
            @endif
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 4: Thanh phân trang cuối Canvas (Nối view chuẩn pagination.n07)   --}}
        {{-- ===================================================================== --}}
        {{ $products->links('pagination.n07') }}

    </x-ui.section-card>
</div>

@push('scripts')
<script>
    /**
     * MỚI: Quản lý chuyển đổi chế độ xem BẢNG / LƯỚI hoạt động thật
     * Tự động lưu lựa chọn vào localStorage và đồng bộ URL query parameter không làm mất bộ lọc.
     */
    function switchViewMode(mode) {
        const tableView = document.getElementById('tableViewContainer');
        const gridView = document.getElementById('gridViewContainer');
        const btnTable = document.getElementById('btnViewTable');
        const btnGrid = document.getElementById('btnViewGrid');
        const viewInput = document.getElementById('viewModeInput');

        if (!tableView || !gridView) return;

        if (mode === 'grid') {
            tableView.classList.add('hidden');
            tableView.classList.remove('block');
            gridView.classList.remove('hidden');
            gridView.classList.add('block');

            btnGrid?.classList.add('bg-surface-container-lowest', 'text-primary', 'shadow-xs', 'font-bold');
            btnGrid?.classList.remove('text-on-surface-variant');
            btnTable?.classList.remove('bg-surface-container-lowest', 'text-primary', 'shadow-xs', 'font-bold');
            btnTable?.classList.add('text-on-surface-variant');
        } else {
            gridView.classList.add('hidden');
            gridView.classList.remove('block');
            tableView.classList.remove('hidden');
            tableView.classList.add('block');

            btnTable?.classList.add('bg-surface-container-lowest', 'text-primary', 'shadow-xs', 'font-bold');
            btnTable?.classList.remove('text-on-surface-variant');
            btnGrid?.classList.remove('bg-surface-container-lowest', 'text-primary', 'shadow-xs', 'font-bold');
            btnGrid?.classList.add('text-on-surface-variant');
        }

        if (viewInput) {
            viewInput.value = mode;
        }

        try {
            localStorage.setItem('n07_product_view', mode);
        } catch(e) {}

        const url = new URL(window.location);
        url.searchParams.set('view', mode);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('view')) {
            const savedMode = localStorage.getItem('n07_product_view');
            if (savedMode === 'grid') {
                switchViewMode('grid');
            }
        }
    });

    /**
     * Checkbox chọn tất cả (Đồng bộ cho cả Bảng và Lưới)
     */
    const checkAllTable = document.getElementById('checkAllTable');
    const checkAllGrid = document.getElementById('checkAllGrid');

    function syncCheckboxes(checked) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => cb.checked = checked);
        if (checkAllTable) checkAllTable.checked = checked;
        if (checkAllGrid) checkAllGrid.checked = checked;
    }

    if (checkAllTable) {
        checkAllTable.addEventListener('change', function() {
            syncCheckboxes(checkAllTable.checked);
        });
    }
    if (checkAllGrid) {
        checkAllGrid.addEventListener('change', function() {
            syncCheckboxes(checkAllGrid.checked);
        });
    }

    /**
     * Đóng/Mở dải bộ lọc
     */
    function toggleFilterBar() {
        const filterBar = document.getElementById('filterBar');
        if (filterBar) {
            filterBar.classList.toggle('hidden');
        }
    }
</script>
@endpush
@endsection
