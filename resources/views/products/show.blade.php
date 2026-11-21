{{--
    Đường dẫn: resources/views/products/show.blade.php
    Mô tả: Giao diện Chi tiết sản phẩm & Phân tích tồn kho DSS chuẩn Monochrome N07, đồng bộ 100% với layout của nhóm.
    Phụ thuộc: ProductController@show, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Chi tiết ' . $product->name)
@section('page-title', 'Chi tiết sản phẩm & Tồn kho DSS')
@section('page-subtitle', 'Theo dõi hồ sơ mặt hàng, các chỉ số phân tích điểm đặt hàng lại và lịch sử giao dịch kho.')

@section('content')
<div class="space-y-6">

    {{-- KHUNG 1: HEADER TRANG --}}
    <x-ui.page-header :title="$product->name"
                      :chip="$product->category_name ?? 'Chưa phân loại'"
                      :subtitle="$product->product_code . ' · ' . ($product->description ?: 'Chưa có mô tả cho mặt hàng này.')">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('products.index')" icon="arrow_back">
                Danh sách sản phẩm
            </x-ui.button>
            @if(auth()->user()?->role === 'admin')
                <x-ui.button variant="secondary" :href="route('products.edit', $product->id)" icon="edit">
                    Chỉnh sửa
                </x-ui.button>
                <form action="{{ route('products.status', $product->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('{{ $product->status == 1 ? 'Ngừng kinh doanh sản phẩm này? Lịch sử kho vẫn được giữ lại.' : 'Mở kinh doanh lại sản phẩm này?' }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $product->status == 1 ? 0 : 1 }}">
                    <x-ui.button :variant="$product->status == 1 ? 'danger' : 'secondary'" type="submit" :icon="$product->status == 1 ? 'block' : 'check_circle'">
                        {{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }}
                    </x-ui.button>
                </form>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- LƯỚI 2 CỘT: BÊN TRÁI THÔNG TIN SẢN PHẨM, BÊN PHẢI CHỈ SỐ DSS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- CỘT TRÁI: HỒ SƠ SẢN PHẨM --}}
            <div class="lg:col-span-2 bg-surface-container-low/40 rounded-2xl p-6 border border-surface-container-high/60 flex flex-col md:flex-row gap-6 shadow-2xs">
                {{-- Hình ảnh sản phẩm --}}
                <div class="w-36 h-36 rounded-2xl bg-white border border-surface-container-high/60 overflow-hidden flex items-center justify-center shrink-0 shadow-2xs relative">
                    @if(!empty($product->image))
                        <img class="w-full h-full object-cover"
                             src="{{ asset('storage/' . $product->image) }}"
                             alt="{{ $product->name }}">
                    @else
                        <div class="flex flex-col items-center justify-center text-on-surface-variant/60">
                            <span class="material-symbols-outlined text-[48px] text-surface-container-highest">inventory_2</span>
                            <span class="font-mono font-bold text-xs mt-1 text-on-surface-variant">{{ $product->product_code }}</span>
                        </div>
                    @endif
                </div>

                {{-- Thông tin chi tiết --}}
                <div class="flex-1 space-y-4 min-w-0">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <x-ui.status-pill :variant="$product->status == 1 ? 'success' : 'neutral'"
                                              :label="$product->status == 1 ? 'Đang kinh doanh' : 'Ngừng kinh doanh'" />
                            <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-surface-container-high/60 text-on-surface font-semibold">
                                SKU: {{ $product->product_code }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-on-surface tracking-tight">{{ $product->name }}</h2>
                        <p class="text-xs text-on-surface-variant leading-relaxed">{{ $product->description ?: 'Mặt hàng chưa có ghi chú mô tả bổ sung.' }}</p>
                    </div>

                    {{-- Thông số định giá & quy cách --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3 border-t border-surface-container-high/60 text-xs">
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Danh mục</span>
                            <strong class="text-sm text-on-surface mt-0.5 block">{{ $product->category_name ?? '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Đơn vị tính</span>
                            <strong class="text-sm text-on-surface mt-0.5 block">{{ $product->unit }}</strong>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Quy cách lô đặt</span>
                            <strong class="text-sm text-on-surface mt-0.5 block font-mono">{{ $product->pack_size ?? 1 }} {{ $product->unit }}/kiện</strong>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Giá nhập tham khảo</span>
                            <strong class="text-sm text-on-surface mt-0.5 block font-mono">{{ number_format($product->purchase_price, 0, ',', '.') }} đ</strong>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Giá bán niêm yết</span>
                            <strong class="text-sm text-emerald-800 mt-0.5 block font-mono font-bold">{{ number_format($product->selling_price, 0, ',', '.') }} đ</strong>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block text-[11px] font-mono">Ước tính giá trị tồn</span>
                            <strong class="text-sm text-on-surface mt-0.5 block font-mono">
                                {{ number_format($product->on_hand * $product->purchase_price, 0, ',', '.') }} đ
                            </strong>
                        </div>
                    </div>

                    {{-- Nút thao tác nhanh nhập / xuất riêng món này --}}
                    <div class="pt-2 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('inventory.create', ['type' => 'import', 'product_id' => $product->id]) }}"
                           class="h-8 px-3 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-white border border-surface-container-high/70 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[15px] text-emerald-700">archive</span>
                            <span>Lập phiếu nhập</span>
                        </a>
                        <a href="{{ route('inventory.create', ['type' => 'export', 'product_id' => $product->id]) }}"
                           class="h-8 px-3 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-white border border-surface-container-high/70 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[15px] text-amber-700">unarchive</span>
                            <span>Lập phiếu xuất</span>
                        </a>
                        <a href="{{ route('reports.bc05', ['product_id' => $product->id]) }}"
                           class="h-8 px-3 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-white border border-surface-container-high/70 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[15px] text-secondary">trending_up</span>
                            <span>Xem phân tích nhu cầu BC05</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: CHỈ SỐ DSS & TÌNH TRẠNG TỒN KHO --}}
            @php
                $isReorder = $recommendation && ($recommendation->suggested_quantity ?? 0) > 0;
            @endphp
            <div class="bg-surface-container-low/40 rounded-2xl p-6 border {{ $isReorder ? 'border-amber-400/80 ring-1 ring-amber-400/20' : 'border-surface-container-high/60' }} space-y-4 flex flex-col justify-between shadow-2xs">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface flex items-center gap-2 font-mono">
                            <span class="material-symbols-outlined text-[18px] text-secondary">analytics</span>
                            <span>Chỉ số hỗ trợ quyết định (DSS)</span>
                        </h3>
                        @if($isReorder)
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-900 border border-amber-500/30 text-[11px] font-bold font-mono">
                                Cần nhập hàng
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-900 border border-emerald-500/30 text-[11px] font-bold font-mono">
                                Tồn kho an toàn
                            </span>
                        @endif
                    </div>

                    {{-- 4 ô chỉ số DSS cốt lõi --}}
                    <div class="grid grid-cols-2 gap-2.5 text-xs font-mono">
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[10px] uppercase font-bold tracking-wider">Tồn thực tế hiện có</span>
                            <strong class="text-base {{ $product->on_hand <= 0 ? 'text-red-600' : 'text-on-surface' }} mt-0.5 block">
                                {{ $product->on_hand }} <span class="text-xs font-normal text-on-surface-variant">{{ $product->unit }}</span>
                            </strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[10px] uppercase font-bold tracking-wider">Tồn an toàn (SS)</span>
                            <strong class="text-base text-on-surface mt-0.5 block">
                                {{ $recommendation->safety_stock ?? '—' }} <span class="text-xs font-normal text-on-surface-variant">{{ $recommendation ? $product->unit : '' }}</span>
                            </strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[10px] uppercase font-bold tracking-wider">Điểm đặt hàng (ROP)</span>
                            <strong class="text-base text-secondary mt-0.5 block font-bold">
                                {{ $recommendation->reorder_point ?? '—' }} <span class="text-xs font-normal text-on-surface-variant">{{ $recommendation ? $product->unit : '' }}</span>
                            </strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[10px] uppercase font-bold tracking-wider">Nhu cầu TB (d)</span>
                            <strong class="text-base text-on-surface mt-0.5 block">
                                {{ $recommendation->average_daily_demand ?? '—' }} <span class="text-[11px] font-normal text-on-surface-variant">/ngày</span>
                            </strong>
                        </div>
                    </div>

                    {{-- Hộp thông báo đề xuất số lượng nhập Q --}}
                    @if($isReorder)
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs space-y-1.5 shadow-2xs">
                            <div class="flex items-center gap-2 font-bold text-amber-900">
                                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                                <span>Khuyến nghị nhập: {{ $recommendation->suggested_quantity }} {{ $product->unit }}</span>
                            </div>
                            <p class="text-[11px] text-amber-900/80 leading-relaxed">
                                {{ $recommendation->explanation ?: 'Tồn kho khả dụng (IP) đã chạm hoặc thấp hơn ngưỡng đặt lại ROP.' }}
                            </p>
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-surface-container-high/60 text-xs space-y-1">
                            <span class="font-bold text-on-surface flex items-center gap-1.5 text-secondary">
                                <span class="material-symbols-outlined text-[16px]">verified</span>
                                <span>Chưa cần đặt hàng thêm</span>
                            </span>
                            <p class="text-[11px] text-on-surface-variant leading-relaxed">
                                Tồn kho khả dụng hiện tại vẫn cao hơn điểm đặt hàng lại (ROP). Hệ thống sẽ tự động đề xuất khi số dư chạm ngưỡng.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Nối đường dẫn xem chi tiết thuật toán --}}
                <div class="pt-2 border-t border-surface-container-high/60 flex items-center justify-between">
                    <a href="{{ route('dss.index') }}" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 hover:underline inline-flex items-center gap-1">
                        <span>Đến màn hình đề xuất DSS</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </a>
                    <a href="{{ route('dss.history') }}" class="text-xs font-mono text-on-surface-variant hover:text-on-surface">
                        Xem lịch sử DSS
                    </a>
                </div>
            </div>

        </div>

        {{-- BẢNG LỊCH SỬ GIAO DỊCH GẦN NHẤT CỦA SẢN PHẨM --}}
        <div class="pt-4 border-t border-surface-container-high/60 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="font-headline-sm text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-secondary">receipt_long</span>
                        <span>Lịch sử nhập - xuất gần nhất</span>
                    </h3>
                    <p class="font-body-sm text-xs text-on-surface-variant">Hiển thị tối đa 10 giao dịch kho phát sinh gần nhất của mặt hàng này</p>
                </div>
                <a href="{{ route('inventory.history', ['search' => $product->product_code]) }}"
                   class="text-xs font-bold text-on-surface hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                    <span>Xem toàn bộ lịch sử trong sổ kho</span>
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                </a>
            </div>

            <x-ui.data-table minWidth="min-w-[850px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-4 w-36">Mã chứng từ</th>
                    <th class="py-3.5 px-4 text-center w-32">Loại phiếu</th>
                    <th class="py-3.5 px-4 w-36">Ngày giao dịch</th>
                    <th class="py-3.5 px-4 text-right w-36">Số lượng</th>
                    <th class="py-3.5 px-4 text-right w-36">Đơn giá</th>
                    <th class="py-3.5 pl-4 pr-6 w-48">Nhân viên thực hiện</th>
                </x-slot:header>

                @forelse($movements as $movement)
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        <x-ui.cell-code :code="$movement->transaction_code ?? '—'" class="pl-6" />
                        <td class="py-5 px-4 text-center whitespace-nowrap">
                            <x-ui.status-pill :variant="$movement->type === 'import' ? 'success' : 'info'"
                                              :label="$movement->type === 'import' ? 'Nhập kho' : 'Xuất kho'" />
                        </td>
                        <x-ui.cell-date :date="$movement->transaction_date" class="px-4" />
                        <x-ui.cell-number :value="$movement->quantity" :unit="$product->unit" class="px-4" />
                        <x-ui.cell-money :amount="$movement->unit_price" class="px-4" />
                        <td class="py-5 pl-4 pr-6 whitespace-nowrap font-medium text-xs text-on-surface">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-on-surface-variant">person</span>
                                <span>{{ $movement->staff_name ?? 'Tài khoản đã xóa' }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <x-ui.empty-state icon="receipt_long"
                                              title="Chưa có giao dịch"
                                              message="Chưa có giao dịch nhập hoặc xuất kho nào cho sản phẩm này." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>
        </div>

    </x-ui.section-card>

</div>
@endsection
