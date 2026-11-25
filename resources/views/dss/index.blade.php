{{--
    Đường dẫn: resources/views/dss/index.blade.php
    Mô tả: Giao diện Đề xuất nhập hàng DSS (Decision Support System) theo Monochrome Design System chuẩn N07, tích hợp đầy đủ quy trình phê duyệt, đặt hàng NCC và theo dõi giao hàng.
    Phụ thuộc: ReorderController@index, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Đề xuất nhập hàng (DSS)')
@section('page-title', 'Phân tích và đề xuất nhập hàng')
@section('page-subtitle', 'Kiểm tra căn cứ, quyết định lượng nhập và theo dõi hàng đang về.')

@section('content')
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header                                     --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Phân tích và đề xuất nhập hàng"
                      chip="Hệ thống hỗ trợ ra quyết định (DSS)"
                      subtitle="Kiểm tra căn cứ định lượng, phê duyệt đề xuất bổ sung kho và theo dõi tiến độ các lô hàng đang về.">
        <x-slot:actions>
            <a href="{{ route('dss.history') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">history</span>
                <span>Lịch sử quyết định DSS</span>
            </a>

            <form action="{{ route('dss.generate') }}" method="POST" class="inline-flex">
                @csrf
                <input type="hidden" name="period_days" value="30">
                <button type="submit"
                        class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[18px] text-white">sync</span>
                    <span class="text-white">Cập nhật tính toán mới</span>
                </button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card                --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- 1. BANNER CÔNG THỨC & NGUYÊN TẮC DSS --}}
        <x-ui.banner-hint icon="psychology"
                          title="Căn cứ tính toán và nguyên tắc vận hành DSS"
                          message="MA dùng các ngày đã kết thúc. Chỉ xuất bán/tiêu thụ được tính là nhu cầu. Vị thế tồn (IP) = Tồn kho thực tế + Hàng đang về. Hệ thống chỉ đề xuất bổ sung khi IP ≤ Điểm đặt lại (ROP). Đề xuất chỉ mang tính hỗ trợ ra quyết định, không tự động sinh đơn mua hàng nếu chưa được phê duyệt.">
            <x-slot:action>
                <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-900 border border-amber-500/30 font-mono text-[11px] font-bold">
                    Hỗ trợ quyết định · Không tự tạo phiếu
                </span>
            </x-slot:action>
        </x-ui.banner-hint>

        {{-- 2. DẢI 4 THẺ CHỈ SỐ KPI DSS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.kpi-card label="Đề xuất chờ duyệt"
                           :value="$pendingCount"
                           unit="mặt hàng"
                           icon="notification_important"
                           tone="sage"
                           :pill="$pendingCount > 0 ? 'Cần duyệt' : 'Đầy đủ'"
                           :status="$pendingCount > 0 ? 'warning' : 'neutral'">
                <span class="truncate">Vị thế tồn kho (IP) ≤ ROP</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Chờ đặt hàng NCC"
                           :value="$approvedOrders->count()"
                           unit="lô"
                           icon="pending_actions"
                           tone="grey"
                           :pill="$approvedOrders->count() > 0 ? 'Chờ đặt' : null"
                           :status="$approvedOrders->count() > 0 ? 'warning' : 'neutral'">
                <span class="truncate">Admin đã duyệt, chờ liên hệ NCC</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Lô hàng đang về (On Order)"
                           :value="$openOrders->count()"
                           unit="lô"
                           icon="local_shipping"
                           tone="sage"
                           :pill="$openOrders->count() > 0 ? 'Đang giao' : null"
                           status="success">
                <span class="truncate">Đã chốt đơn NCC, chờ nhập kho</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Mặt hàng theo dõi"
                           :value="$products->total()"
                           unit="mã"
                           icon="inventory_2"
                           tone="grey">
                <span class="truncate">Cập nhật: {{ $lastCalculatedAt ? \Illuminate\Support\Carbon::parse($lastCalculatedAt)->format('H:i d/m') : 'Mới nhất' }}</span>
            </x-ui.kpi-card>
        </div>

        {{-- 3. THANH TAB WORKFLOW --}}
        <div class="flex items-center gap-2 border-b border-surface-container-high/60 pb-3 flex-wrap">
            <button type="button" onclick="switchDssTab(this, 'pane-pending')"
                    class="dss-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 bg-[#1A4D38] text-white shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">bolt</span>
                <span>Cần xem xét duyệt</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-white/20 text-white">{{ $pendingCount }}</span>
            </button>

            <button type="button" onclick="switchDssTab(this, 'pane-approved')"
                    class="dss-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 bg-surface-container-low text-on-surface hover:bg-surface-container cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">fact_check</span>
                <span>Đã duyệt, chờ đặt hàng</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-white text-on-surface border border-surface-container-high/60">{{ $approvedOrders->count() }}</span>
            </button>

            <button type="button" onclick="switchDssTab(this, 'pane-ordered')"
                    class="dss-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 bg-surface-container-low text-on-surface hover:bg-surface-container cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">local_shipping</span>
                <span>Đang chờ NCC giao hàng</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-white text-on-surface border border-surface-container-high/60">{{ $openOrders->count() }}</span>
            </button>

            <button type="button" onclick="switchDssTab(this, 'pane-all')"
                    class="dss-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 bg-surface-container-low text-on-surface hover:bg-surface-container cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">grid_view</span>
                <span>Tất cả mặt hàng</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-white text-on-surface border border-surface-container-high/60">{{ $products->total() }}</span>
            </button>
        </div>

        {{-- TAB PANE 1: CẦN DUYỆT --}}
        <div id="pane-pending" class="dss-tab-pane space-y-4">
            @php
                $pendingProducts = collect($products->items())->filter(function($p) {
                    return $p->recommendation_status === 'pending' && $p->suggested_quantity > 0;
                });
            @endphp

            @if($pendingProducts->isNotEmpty())
                <div class="space-y-4">
                    @foreach($pendingProducts as $product)
                        <x-ui.dss-suggestion-card :product="$product" :isAdmin="auth()->user()->role === 'admin'" />
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="verified"
                                  title="Không có mặt hàng nào cần duyệt gấp"
                                  message="Vị thế tồn kho (IP) của toàn bộ mặt hàng đang ở mức an toàn trên ngưỡng ROP." />
            @endif
        </div>

        {{-- TAB PANE 2: ĐÃ DUYỆT, CHỜ ĐẶT HÀNG NCC --}}
        <div id="pane-approved" class="dss-tab-pane space-y-4 hidden">
            @if($approvedOrders->isNotEmpty())
                <x-ui.data-table minWidth="min-w-[950px]">
                    <x-slot:head>
                        <th class="py-3.5 pl-6 pr-2 min-w-[200px]">Sản phẩm & SKU</th>
                        <th class="py-3.5 px-4 text-right min-w-[120px]">Q Đề xuất</th>
                        <th class="py-3.5 px-4 text-right min-w-[120px]">Q Admin duyệt</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Ghi chú duyệt</th>
                        <th class="py-3.5 pr-6 pl-4 text-right min-w-[220px]">Thao tác xác nhận đặt hàng</th>
                    </x-slot:head>

                    @foreach($approvedOrders as $item)
                        <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px]">
                            <td class="py-4 pl-6 pr-2">
                                <span class="font-bold text-sm text-on-surface block">{{ $item->name }}</span>
                                <span class="font-mono text-xs text-on-surface-variant">{{ $item->product_code }} · {{ $item->unit }}</span>
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-sm text-on-surface-variant">
                                {{ number_format($item->suggested_quantity) }} {{ $item->unit }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-sm text-secondary">
                                {{ number_format($item->approved_quantity) }} {{ $item->unit }}
                            </td>
                            <td class="py-4 px-4 text-xs text-on-surface-variant">
                                {{ $item->decision_note ?: '—' }}
                            </td>
                            <td class="py-4 pr-6 pl-4 text-right">
                                <details class="inline-block text-left">
                                    <summary class="h-9 px-3.5 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs cursor-pointer select-none">
                                        <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
                                        <span>Đặt hàng NCC</span>
                                    </summary>
                                    <div class="fixed inset-0 bg-neutral-900/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this)this.closest('details').removeAttribute('open')">
                                        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl border border-surface-container-high space-y-4" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
                                                <h3 class="font-headline-sm text-sm font-bold text-on-surface">Xác nhận đặt hàng NCC: {{ $item->name }}</h3>
                                                <button type="button" onclick="this.closest('details').removeAttribute('open')" class="text-on-surface-variant hover:text-on-surface">✕</button>
                                            </div>
                                            <form action="{{ route('dss.order', $item->id) }}" method="POST" class="space-y-3 text-xs">
                                                @csrf
                                                <div>
                                                    <label class="block font-bold mb-1">Số lượng đặt thực tế với NCC *</label>
                                                    <input type="number" name="ordered_quantity" min="1" max="{{ $item->approved_quantity }}" value="{{ $item->approved_quantity }}" required
                                                           class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs font-mono font-bold">
                                                    <p class="text-[11px] text-on-surface-variant mt-0.5">Tối đa theo số lượng Admin đã duyệt: {{ $item->approved_quantity }} {{ $item->unit }}</p>
                                                </div>
                                                <div>
                                                    <label class="block font-bold mb-1">Tên nhà cung cấp</label>
                                                    <input type="text" name="supplier_name" placeholder="Ví dụ: Công ty TNHH Bia Nước giải khát..."
                                                           class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs">
                                                </div>
                                                <div>
                                                    <label class="block font-bold mb-1">Ngày dự kiến giao hàng</label>
                                                    <input type="date" name="expected_delivery_date" min="{{ now()->toDateString() }}"
                                                           class="w-full h-10 px-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs font-mono">
                                                </div>
                                                <div>
                                                    <label class="block font-bold mb-1">Ghi chú đơn đặt hàng</label>
                                                    <textarea name="order_note" rows="2" placeholder="Số đơn, liên hệ người giao hàng..."
                                                              class="w-full p-3 rounded-xl bg-surface-container-low border border-surface-container-high text-xs"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2">
                                                    <button type="button" onclick="this.closest('details').removeAttribute('open')" class="px-4 py-2 rounded-xl text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low">Hủy</button>
                                                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-neutral-800 shadow-sm">Xác nhận đơn đặt hàng</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            @else
                <x-ui.empty-state icon="fact_check"
                                  title="Không có đề xuất nào đang chờ đặt hàng"
                                  message="Các đề xuất sau khi được Admin phê duyệt sẽ hiển thị tại đây để nhân viên kho liên hệ NCC." />
            @endif
        </div>

        {{-- TAB PANE 3: ĐANG CHỜ NCC GIAO HÀNG --}}
        <div id="pane-ordered" class="dss-tab-pane space-y-4 hidden">
            @if($openOrders->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($openOrders as $order)
                        <div class="p-5 rounded-2xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm flex flex-col justify-between gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0 border border-emerald-200">
                                        {{ mb_substr($order->product_code, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-on-surface">{{ $order->name }}</h4>
                                        <p class="font-mono text-xs text-on-surface-variant">{{ $order->product_code }} · {{ $order->supplier_name ?: 'Chưa ghi NCC' }}</p>
                                    </div>
                                </div>
                                <x-ui.status-pill variant="info" :label="$order->status === 'partially_received' ? 'Đã nhận 1 phần' : 'Đang về'" />
                            </div>

                            <div class="p-3 rounded-xl bg-surface-container-low/70 border border-surface-container-high/60 grid grid-cols-3 gap-2 text-xs font-mono">
                                <div>
                                    <span class="text-[10px] text-on-surface-variant block uppercase">Đã đặt</span>
                                    <strong class="text-sm font-bold">{{ $order->ordered_quantity }} {{ $order->unit }}</strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-on-surface-variant block uppercase">Đã nhận</span>
                                    <strong class="text-sm font-bold text-emerald-700">{{ $order->received_quantity }} {{ $order->unit }}</strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-on-surface-variant block uppercase">Còn lại</span>
                                    <strong class="text-sm font-bold text-amber-700">{{ $order->ordered_quantity - $order->received_quantity }} {{ $order->unit }}</strong>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <span class="text-xs text-on-surface-variant font-mono">
                                    Dự kiến: {{ $order->expected_delivery_date ? \Illuminate\Support\Carbon::parse($order->expected_delivery_date)->format('d/m/Y') : 'Chưa định ngày' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('inventory.create', ['type' => 'import', 'recommendation_id' => $order->id]) }}"
                                       class="h-9 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="material-symbols-outlined text-[16px]">archive</span>
                                        <span>Tạo phiếu nhập</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="local_shipping"
                                  title="Không có đơn đặt hàng nào đang chờ giao"
                                  message="Khi nhân viên kho liên hệ NCC và xác nhận đơn, hàng đang về sẽ hiển thị tại đây." />
            @endif
        </div>

        {{-- TAB PANE 4: TẤT CẢ SẢN PHẨM --}}
        <div id="pane-all" class="dss-tab-pane space-y-4 hidden">
            <div class="space-y-4">
                @foreach($products as $product)
                    <x-ui.dss-suggestion-card :product="$product" :isAdmin="auth()->user()->role === 'admin'" />
                @endforeach
            </div>

            <div class="pt-2">
                {{ $products->links('pagination.n07') }}
            </div>
        </div>

    </x-ui.section-card>
</div>

<script>
    function switchDssTab(btn, paneId) {
        document.querySelectorAll('.dss-tab-btn').forEach(b => {
            b.classList.remove('bg-[#1A4D38]', 'text-white', 'shadow-sm');
            b.classList.add('bg-surface-container-low', 'text-on-surface');
            const badge = b.querySelector('span:last-child');
            if (badge) {
                badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono bg-white text-on-surface border border-surface-container-high/60';
            }
        });
        btn.classList.remove('bg-surface-container-low', 'text-on-surface');
        btn.classList.add('bg-[#1A4D38]', 'text-white', 'shadow-sm');
        const activeBadge = btn.querySelector('span:last-child');
        if (activeBadge) {
            activeBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono bg-white/20 text-white';
        }

        document.querySelectorAll('.dss-tab-pane').forEach(p => p.classList.add('hidden'));
        const activePane = document.getElementById(paneId);
        if (activePane) activePane.classList.remove('hidden');
    }
</script>
@endsection
