@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm')
@section('page-title', 'Chi tiết sản phẩm')
@section('page-subtitle', 'Thông tin hàng hóa, tình trạng kinh doanh và lịch sử nhập xuất gần nhất.')

@section('content')
<div class="space-y-6">

    {{-- Khung 1: Header dùng x-ui.page-header --}}
    <x-ui.page-header :title="$product->name"
                      :chip="$product->category_name"
                      :subtitle="$product->product_code . ' · ' . ($product->description ?: 'Chưa có mô tả cho sản phẩm này.')">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('products.index')" icon="arrow_back">
                Danh sách sản phẩm
            </x-ui.button>
            @if(auth()->user()->role === 'admin')
                <x-ui.button variant="primary" :href="route('products.edit', $product->id)" icon="edit">
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

    {{-- Khung 2: Main Content Card dùng x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Cột trái: Thông tin sản phẩm --}}
            <div class="lg:col-span-2 bg-surface-container-low/40 rounded-2xl p-6 border border-surface-container-high/60 flex flex-col md:flex-row gap-6">
                <div class="w-36 h-36 rounded-2xl bg-white border border-surface-container-high/60 overflow-hidden flex items-center justify-center shrink-0">
                    @if($product->image && file_exists(public_path('storage/'.$product->image)))
                        <img class="w-full h-full object-cover"
                             src="{{ asset('storage/'.$product->image) }}"
                             alt="{{ $product->name }}">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-surface-container-low text-on-surface-variant gap-1 p-2 text-center">
                            <span class="material-symbols-outlined text-[40px] text-neutral-400">inventory_2</span>
                            <span class="text-[10px] text-neutral-400 font-medium">Chưa có ảnh</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 space-y-4 min-w-0">
                    <div class="space-y-1">
                        <x-ui.status-pill :variant="$product->status == 1 ? 'success' : 'neutral'"
                                          :label="$product->status == 1 ? 'Đang kinh doanh' : 'Ngừng kinh doanh'" />
                        <h2 class="text-xl font-bold text-on-surface mt-2">{{ $product->name }}</h2>
                        <p class="text-sm text-on-surface-variant">{{ $product->description ?: 'Chưa có mô tả cho sản phẩm này.' }}</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3 border-t border-surface-container-high/60 text-xs">
                        <div><span class="text-on-surface-variant block">Mã sản phẩm</span><strong class="font-mono text-sm text-on-surface">{{ $product->product_code }}</strong></div>
                        <div><span class="text-on-surface-variant block">Danh mục</span><strong class="text-sm text-on-surface">{{ $product->category_name }}</strong></div>
                        <div><span class="text-on-surface-variant block">Đơn vị tính</span><strong class="text-sm text-on-surface">{{ $product->unit }}</strong></div>
                        <div><span class="text-on-surface-variant block">Số lượng tồn</span><strong class="text-sm text-on-surface">{{ $product->on_hand }} {{ $product->unit }}</strong></div>
                        <div><span class="text-on-surface-variant block">Giá nhập</span><strong class="text-sm text-on-surface">{{ number_format($product->purchase_price, 0, ',', '.') }} đ</strong></div>
                        <div><span class="text-on-surface-variant block">Giá bán</span><strong class="text-sm text-on-surface">{{ number_format($product->selling_price, 0, ',', '.') }} đ</strong></div>
                    </div>
                </div>
            </div>

            {{-- Cột phải: Tình trạng tồn kho --}}
            <div class="bg-surface-container-low/40 rounded-2xl p-6 border border-surface-container-high/60 space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-on-surface mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-secondary">warehouse</span>
                        <span>Tình trạng tồn kho</span>
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[11px]">Tồn hiện tại</span>
                            <strong class="text-base {{ $product->on_hand <= 0 ? 'text-error' : 'text-on-surface' }}">{{ (float)$product->on_hand }} {{ $product->unit }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[11px]">Tồn an toàn</span>
                            <strong class="text-base text-on-surface">{{ isset($recommendation->safety_stock) ? (float)$recommendation->safety_stock : '—' }} {{ $recommendation ? $product->unit : '' }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[11px]">Điểm đặt lại</span>
                            <strong class="text-base text-on-surface">{{ isset($recommendation->reorder_point) ? (float)$recommendation->reorder_point : '—' }} {{ $recommendation ? $product->unit : '' }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60">
                            <span class="text-on-surface-variant block text-[11px]">Gợi ý bổ sung</span>
                            <strong class="text-base text-on-surface">{{ isset($recommendation->suggested_quantity) ? (float)$recommendation->suggested_quantity : '—' }} {{ $recommendation ? $product->unit : '' }}</strong>
                        </div>
                    </div>
                </div>
                <div>
                    @if($recommendation && $recommendation->suggested_quantity > 0)
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium">
                            Nên xem xét nhập thêm {{ $recommendation->suggested_quantity }} {{ $product->unit }}.
                        </div>
                    @else
                        <p class="text-xs text-on-surface-variant">Chưa có gợi ý mới. Số tồn thay đổi sau khi ghi nhận phiếu nhập hoặc xuất.</p>
                    @endif
                    <a href="{{ route('dss.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline mt-2 inline-flex items-center gap-1">
                        <span>Xem phân tích và đề xuất</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Lịch sử nhập xuất gần nhất --}}
        <div class="pt-4 border-t border-surface-container-high/60 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-headline-sm text-base font-bold text-on-surface">Lịch sử nhập xuất gần nhất</h3>
                    <p class="font-body-sm text-xs text-on-surface-variant">10 giao dịch gần nhất của mặt hàng này trong kho</p>
                </div>
            </div>

            <x-ui.data-table minWidth="min-w-[850px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-4 w-36">Mã phiếu</th>
                    <th class="py-3.5 px-4 text-center w-32">Phân loại</th>
                    <th class="py-3.5 px-4 w-36">Ngày</th>
                    <th class="py-3.5 px-4 text-right w-36">Số lượng</th>
                    <th class="py-3.5 px-4 text-right w-36">Đơn giá</th>
                    <th class="py-3.5 pl-4 pr-6 w-44">Người thực hiện</th>
                </x-slot:header>

                @forelse($movements as $movement)
                    <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                        <x-ui.cell-code :code="$movement->transaction_code" :href="route('inventory.show', $movement->id)" class="pl-6" />
                        <td class="py-5 px-4 text-center whitespace-nowrap">
                            <x-ui.status-pill :variant="$movement->type === 'import' ? 'success' : 'info'"
                                              :label="$movement->type === 'import' ? 'Nhập kho' : 'Xuất kho'" />
                        </td>
                        <x-ui.cell-date :date="$movement->transaction_date" class="px-4" />
                        <x-ui.cell-number :value="$movement->quantity" :unit="$product->unit" class="px-4" />
                        <x-ui.cell-money :amount="$movement->unit_price" class="px-4" />
                        <td class="py-5 pl-4 pr-6 whitespace-nowrap font-medium text-[15px] text-on-surface">
                            {{ $movement->staff_name ?? 'Tài khoản đã xóa' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <x-ui.empty-state icon="receipt_long"
                                              title="Chưa có giao dịch"
                                              message="Chưa có giao dịch nhập xuất nào cho sản phẩm này." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>
        </div>
    </x-ui.section-card>

</div>
@endsection
