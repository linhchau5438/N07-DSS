{{--
    Đường dẫn: resources/views/categories/index.blade.php
    Mô tả: Giao diện Quản lý Danh mục hàng hóa chuẩn Design System N07 Monochrome, sử dụng các component dùng chung x-ui.*.
    Phụ thuộc: CategoryController@index, layouts/app.blade.php, các biến: $categories.
--}}

@extends('layouts.app')

@section('title', 'Danh mục hàng hóa')
@section('page-title', 'Danh mục hàng hóa')

@section('content')
{{-- Nối dữ liệu: Tính toán nhanh 4 chỉ số KPI từ danh sách danh mục hiện có --}}
@php
    $totalCategories = $categories->count();
    $activeCategories = $categories->where('products_count', '>', 0)->count();
    $maxCategory = $categories->sortByDesc('products_count')->first();
    $emptyCategories = $categories->where('products_count', 0)->count();
@endphp

<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- Khung 1: Header trang dùng x-ui.page-header (Card trắng rounded-2xl)       --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Danh mục hàng hóa"
                      chip="Kho Tổng Miền Bắc">
        <x-slot:subtitle>
            Tổng cộng <strong class="text-on-surface font-semibold">{{ $totalCategories }}</strong> nhóm hàng đang được phân loại •
            <strong class="text-on-surface font-semibold">{{ $activeCategories }}</strong> nhóm đang hoạt động •
            <span class="inline-flex items-center gap-1 text-secondary font-medium">
                <span class="material-symbols-outlined text-[15px]">sync</span>
                Đồng bộ vừa xong
            </span>
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Nút Quay lại Sản phẩm --}}
            <a href="{{ route('products.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md active:scale-[0.98]">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">arrow_back</span>
                <span>Danh sách sản phẩm</span>
            </a>

            {{-- Nút Thêm danh mục mới --}}
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('categories.create') }}"
                   class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-primary text-white shadow-sm hover:bg-neutral-800 transition-all font-label-md text-label-md active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[19px] text-white">add</span>
                    <span class="text-white">Thêm danh mục</span>
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- Khung 2: Main Content Card lớn dùng x-ui.section-card                     --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-8">

        {{-- ===================================================================== --}}
        {{-- Hàng 1: Hệ thống 4 thẻ KPI đơn sắc Monochrome Design System gọn 140px  --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Thẻ 1: Tổng danh mục --}}
            <x-ui.kpi-card label="Tổng danh mục"
                           :value="number_format($totalCategories)"
                           unit="nhóm"
                           icon="category"
                           tone="sage">
                <span class="truncate">Toàn bộ danh mục phân loại hệ thống</span>
            </x-ui.kpi-card>

            {{-- Thẻ 2: Đang hoạt động --}}
            <x-ui.kpi-card label="Đang hoạt động"
                           :value="number_format($activeCategories)"
                           unit="nhóm"
                           tone="grey"
                           pill="Có sản phẩm"
                           icon="verified"
                           status="success">
                <span class="truncate">{{ $totalCategories > 0 ? round(($activeCategories / $totalCategories) * 100) : 0 }}% danh mục có phát sinh lưu kho</span>
            </x-ui.kpi-card>

            {{-- Thẻ 3: Nhóm lớn nhất --}}
            <x-ui.kpi-card label="Nhóm nhiều hàng nhất"
                           :value="$maxCategory ? $maxCategory->products_count : 0"
                           unit="mặt hàng"
                           icon="leaderboard"
                           tone="sage">
                <span class="truncate">{{ $maxCategory ? $maxCategory->name : 'Chưa có dữ liệu' }}</span>
            </x-ui.kpi-card>

            {{-- Thẻ 4: Danh mục chưa có hàng --}}
            <x-ui.kpi-card label="Danh mục chưa có hàng"
                           :value="number_format($emptyCategories)"
                           unit="nhóm"
                           tone="grey"
                           pill="Cần bổ sung"
                           icon="folder_off"
                           status="warning">
                <span class="truncate">Đang chờ phân loại sản phẩm mới</span>
            </x-ui.kpi-card>
        </div>

        {{-- ===================================================================== --}}
        {{-- Hàng 2: Toolbar Tìm kiếm & Lọc client-side dùng component x-ui.toolbar --}}
        {{-- ===================================================================== --}}
        <x-ui.toolbar>
            <x-ui.search-input id="categorySearchInput"
                               placeholder="Tìm theo tên danh mục hoặc mô tả (⌘K)..."
                               class="flex-1" />

            <x-ui.filter-select id="categoryStatusFilter"
                                name="status"
                                :autoSubmit="false"
                                onchange="filterCategories()"
                                width="w-full sm:w-56">
                <option value="all">Tất cả trạng thái</option>
                <option value="has_products">Đang hoạt động</option>
                <option value="empty">Chưa có sản phẩm</option>
            </x-ui.filter-select>

            <button type="button" id="resetCategoryFilterBtn" onclick="resetCategoryFilters()"
                    class="hidden h-11 px-3.5 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low text-xs font-semibold items-center gap-1.5 transition-colors border border-surface-container-high/60 whitespace-nowrap cursor-pointer select-none"
                    title="Đặt lại bộ lọc">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span>Đặt lại</span>
            </button>
        </x-ui.toolbar>

        {{-- ===================================================================== --}}
        {{-- Hàng 3: BẢNG DỮ LIỆU DANH MỤC HÀNG HÓA DÙNG x-ui.data-table CHUẨN     --}}
        {{-- ===================================================================== --}}
        <x-ui.data-table minWidth="min-w-[950px]">
            <x-slot:head>
                <th class="py-3.5 pl-6 pr-2 w-16 text-center">#</th>
                <th class="py-3.5 px-5 min-w-[240px]">Tên danh mục</th>
                <th class="py-3.5 px-5 min-w-[280px]">Mô tả & Ghi chú</th>
                <th class="py-3.5 px-5 text-right w-36">Số mặt hàng</th>
                <th class="py-3.5 px-5 text-center w-36">Trạng thái</th>
                <th class="py-3.5 pr-6 pl-2 text-right w-24 min-w-[96px] whitespace-nowrap">Thao tác</th>
            </x-slot:head>

            @forelse($categories as $index => $category)
                <tr class="category-row hover:bg-surface-container-low/70 transition-colors h-[72px] group"
                    data-name="{{ mb_strtolower($category->name) }}"
                    data-desc="{{ mb_strtolower($category->description ?? '') }}"
                    data-count="{{ (int) $category->products_count }}">
                    
                    {{-- Số thứ tự dùng x-ui.cell-index --}}
                    <x-ui.cell-index :index="str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)" class="pl-6" />

                    {{-- Tên danh mục dùng x-ui.cell-primary --}}
                    <x-ui.cell-primary :title="$category->name"
                                       :subtitle="'Ngày tạo: ' . \Illuminate\Support\Carbon::parse($category->created_at)->format('d/m/Y')"
                                       icon="category"
                                       iconBg="bg-sky-100/80 text-sky-800" />

                    {{-- Mô tả --}}
                    <td class="py-5 px-5 text-[15px] leading-6 text-on-surface-variant">
                        <span class="line-clamp-2 max-w-sm">{{ $category->description ?: 'Chưa có mô tả' }}</span>
                    </td>

                    {{-- Số lượng sản phẩm căn phải --}}
                    <td class="py-5 px-5 text-right whitespace-nowrap">
                        <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $category->products_count > 0 ? 'bg-emerald-100 text-emerald-900 border border-emerald-200 hover:bg-emerald-200' : 'bg-surface-container text-on-surface-variant' }}"
                           title="Xem các sản phẩm thuộc danh mục này">
                            <span class="tabular-nums font-bold font-mono">{{ $category->products_count }}</span>
                            <span>mặt hàng</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </td>

                    {{-- Trạng thái dùng x-ui.status-pill căn giữa --}}
                    <td class="py-5 px-5 text-center whitespace-nowrap">
                        <x-ui.status-pill :variant="$category->products_count > 0 ? 'success' : 'neutral'"
                                          :label="$category->products_count > 0 ? 'Đang hoạt động' : 'Chưa có hàng'" />
                    </td>

                    {{-- Cột Thao tác: Menu ba chấm x-ui.row-actions-menu --}}
                    <td class="py-5 pr-6 pl-2 text-right">
                        <x-ui.row-actions-menu>
                            <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                <span class="material-symbols-outlined text-[17px] text-on-surface-variant">inventory_2</span>
                                <span>Xem sản phẩm</span>
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('categories.edit', $category->id) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                    <span class="material-symbols-outlined text-[17px] text-on-surface-variant">edit</span>
                                    <span>Chỉnh sửa</span>
                                </a>
                                <div class="border-t border-surface-container-high/60 my-1"></div>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                      onsubmit="@if($category->products_count > 0) alert('Không thể xóa danh mục {{ $category->name }} vì vẫn còn {{ $category->products_count }} sản phẩm.'); return false; @else return confirm('Bạn có chắc chắn muốn xóa danh mục {{ $category->name }}?'); @endif" class="m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-error hover:bg-error-container/20 transition-colors font-semibold">
                                        <span class="material-symbols-outlined text-[17px]">delete</span>
                                        <span>Xóa danh mục</span>
                                    </button>
                                </form>
                            @endif
                        </x-ui.row-actions-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-0 border-0">
                        <x-ui.empty-state icon="category"
                                          title="Chưa có danh mục nào"
                                          message="Hãy tạo danh mục đầu tiên để phân loại và quản lý sản phẩm kho hàng."
                                          :actionUrl="auth()->user()->role === 'admin' ? route('categories.create') : null"
                                          actionText="Thêm danh mục mới"
                                          actionIcon="add" />
                    </td>
                </tr>
            @endforelse
        </x-ui.data-table>

    </x-ui.section-card>
</div>

@push('scripts')
<script>
    /**
     * Lọc danh mục tức thì client-side theo từ khóa và trạng thái
     */
    function filterCategories() {
        const query = (document.getElementById('categorySearchInput')?.value || '').trim().toLowerCase();
        const status = document.getElementById('categoryStatusFilter')?.value || 'all';
        const rows = document.querySelectorAll('.category-row');
        const resetBtn = document.getElementById('resetCategoryFilterBtn');

        let isFiltering = (query !== '' || status !== 'all');
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', !isFiltering);
            resetBtn.classList.toggle('inline-flex', isFiltering);
        }

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const desc = row.dataset.desc || '';
            const count = Number(row.dataset.count || 0);

            const matchQuery = (query === '' || name.includes(query) || desc.includes(query));
            let matchStatus = true;
            if (status === 'has_products') {
                matchStatus = (count > 0);
            } else if (status === 'empty') {
                matchStatus = (count === 0);
            }

            row.style.display = (matchQuery && matchStatus) ? '' : 'none';
        });
    }

    const catSearch = document.getElementById('categorySearchInput');
    if (catSearch) {
        catSearch.addEventListener('input', filterCategories);
    }

    function resetCategoryFilters() {
        const searchInput = document.getElementById('categorySearchInput');
        const statusFilter = document.getElementById('categoryStatusFilter');
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterCategories();
    }
</script>
@endpush
@endsection
