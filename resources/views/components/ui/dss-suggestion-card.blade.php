{{--
    Đường dẫn: resources/views/components/ui/dss-suggestion-card.blade.php
    Mô tả: Component Card Đề xuất nhập hàng DSS theo chuẩn Monochrome N07.
    Công dụng: Hiển thị tóm tắt căn cứ tính toán, lượng đề xuất, form duyệt/từ chối và cấu hình phân tích.
--}}
@props([
    'product',
    'isAdmin' => false,
])

@php
    $labels = [
        'pending' => ['Chờ duyệt', 'warning'],
        'approved' => ['Đã duyệt', 'success'],
        'rejected' => ['Từ chối', 'danger'],
        'no_action' => ['Chưa cần nhập', 'neutral'],
        'missing_config' => ['Chưa cấu hình', 'amber'],
        'insufficient_data' => ['Thiếu dữ liệu', 'neutral'],
        'invalid_data' => ['Dữ liệu bất thường', 'danger'],
        'fulfilled' => ['Đã nhận đủ', 'success'],
    ];
    $statusKey = $product->recommendation_status ?? 'no_action';
    $statusInfo = $labels[$statusKey] ?? [$statusKey, 'neutral'];
    $isPending = $statusKey === 'pending' && ($product->suggested_quantity ?? 0) > 0;

    $snapshot = json_decode($product->calculation_snapshot ?? '{}', true) ?: [];
    $stockout = $snapshot['stockout_analysis'] ?? [];
    $actualLt = $snapshot['actual_lead_time'] ?? [];
    $fallbackApplied = !empty($snapshot['safety_fallback_applied']);
@endphp

<article class="p-6 rounded-2xl bg-surface-container-lowest border {{ $isPending ? 'border-amber-400/80 shadow-md ring-1 ring-amber-400/20' : 'border-surface-container-high/60 shadow-sm' }} space-y-4 hover:border-surface-container-highest transition-all">

    {{-- HEADER CARD: ICON + TÊN SẢN PHẨM + SKU + BADGE TRẠNG THÁI --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-surface-container-low border border-surface-container-high/60 flex items-center justify-center font-mono font-bold text-xs text-on-surface-variant">
                {{ mb_substr($product->product_code, 0, 2) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm font-bold text-on-surface tracking-tight">
                        {{ $product->product_code }} · {{ $product->name }}
                    </h3>
                    <span class="text-[11px] text-on-surface-variant font-medium">({{ $product->category_name ?? 'Chưa phân loại' }})</span>
                </div>
                <div class="text-[11px] font-mono text-on-surface-variant flex items-center gap-2 mt-0.5">
                    <span>Đơn vị: <strong>{{ $product->unit }}</strong></span>
                    <span>·</span>
                    <span>Quy cách: <strong>{{ $product->pack_size ?? 1 }} {{ $product->unit }}/thùng</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($fallbackApplied)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Kích hoạt đệm an toàn cơ sở do phương sai xấp xỉ 0">
                    🛡️ Fallback SS
                </span>
            @endif
            @if(!empty($stockout['is_censored']))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-orange-100 text-orange-800 border border-orange-300" title="Phát hiện giai đoạn đứt hàng làm méo nhu cầu">
                    ⚠️ Censored Demand
                </span>
            @endif
            <x-ui.status-pill :variant="$statusInfo[1]" :label="$statusInfo[0]" />
        </div>
    </div>

    {{-- CẢNH BÁO MÉO NHU CẦU NẾU CÓ --}}
    @if(!empty($stockout['is_censored']))
        <div class="p-4 rounded-xl bg-orange-50/80 border border-orange-200 border-l-4 border-l-orange-500 text-orange-950 text-xs space-y-1.5">
            <div class="font-bold flex items-center gap-1.5 text-orange-900 text-[13px]">
                <span class="material-symbols-outlined text-[18px] text-orange-600">warning</span>
                <span>Cảnh báo méo nhu cầu (Censored Demand)</span>
            </div>
            <div class="text-orange-900">
                Sản phẩm hết hàng <strong>{{ $stockout['stockout_days'] ?? 0 }}/{{ $stockout['total_days'] ?? 0 }} ngày</strong> ({{ round(($stockout['stockout_ratio'] ?? 0) * 100, 1) }}%).
                Nhu cầu thực tế khi có hàng là <strong>{{ $stockout['adjusted_demand'] ?? 0 }} {{ $product->unit }}/ngày</strong> (cao hơn mức ghi nhận {{ $product->average_daily_demand }} {{ $product->unit }}/ngày).
            </div>
            @if(!empty($snapshot['adjusted_calculation']['quantity']) && (int)$snapshot['adjusted_calculation']['quantity'] > 0)
                <div class="mt-2 pt-2 border-t border-orange-200/80 flex items-center justify-between flex-wrap gap-2">
                    <span>💡 Đề xuất hiệu chỉnh bù đắp: <strong>{{ $snapshot['adjusted_calculation']['quantity'] }} {{ $product->unit }}</strong></span>
                </div>
            @endif
        </div>
    @endif

    {{-- THANH CHỈ SỐ CĂN CỨ TÍNH TOÁN NỀN PASTEL --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-2 p-3.5 rounded-xl bg-surface-container-low/70 border border-surface-container-high/60">
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">Tồn thực tế</span>
            <span class="text-xs font-bold text-on-surface font-mono mt-0.5">{{ $product->current_on_hand }} {{ $product->unit }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">Đang về</span>
            <span class="text-xs font-bold text-on-surface font-mono mt-0.5">{{ $product->on_order ?? 0 }} {{ $product->unit }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">Vị trí tồn (IP)</span>
            <span class="text-xs font-bold text-secondary font-mono mt-0.5">{{ $product->inventory_position ?? '—' }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">Lead Time:</span>
            <span class="text-xs font-semibold text-on-surface font-mono mt-0.5">{{ $product->lead_time_days }} ngày</span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">An toàn (SS)</span>
            <span class="text-xs font-semibold text-on-surface font-mono mt-0.5">
                {{ $product->safety_stock ?? '—' }}
                @if($fallbackApplied)
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1 py-0.5 rounded ml-0.5">Fallback SS</span>
                @endif
            </span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant font-mono">Điểm đặt (ROP)</span>
            <span class="text-xs font-semibold text-on-surface font-mono mt-0.5">{{ $product->reorder_point ?? '—' }}</span>
        </div>
        <div class="flex flex-col bg-white p-2 rounded-lg border border-surface-container-high/60 shadow-2xs col-span-2 sm:col-span-1">
            <span class="text-[10px] uppercase font-bold tracking-wider text-[#1A4D38] font-mono">Đề xuất nhập</span>
            <strong class="text-sm font-bold text-[#1A4D38] font-mono mt-0.5">
                {{ $product->suggested_quantity ?? 0 }} {{ $product->unit }}
            </strong>
        </div>
    </div>

    {{-- KHỐI DIỄN GIẢI LOGIC CÁCH TÍNH --}}
    @if($product->explanation)
        <div class="p-3 rounded-xl bg-white border border-surface-container-high/60 text-xs text-on-surface-variant leading-relaxed flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[18px] text-secondary shrink-0 mt-0.5">info</span>
            <div>
                <p class="font-medium text-on-surface">{{ $product->explanation }}</p>
                @if($product->decision_note)
                    <p class="mt-1 text-[11px] text-on-surface-variant font-mono">Ghi chú quyết định: {{ $product->decision_note }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- FORM DUYỆT / TỪ CHỐI ĐỀ XUẤT (DÀNH CHO ADMIN KHI CÓ ĐỀ XUẤT CHỜ DUYỆT) --}}
    @if($isAdmin && $product->recommendation_status === 'pending' && $product->recommendation_id)
        <form action="{{ route('dss.decide', $product->recommendation_id) }}" method="POST"
              class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            @csrf
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <div class="w-36">
                    <label class="block text-[10px] font-bold uppercase tracking-wider font-mono text-amber-900 mb-1">Số lượng duyệt</label>
                    <input name="approved_quantity" type="number" min="1" max="1000000"
                           value="{{ $product->suggested_quantity }}" required
                           class="w-full h-10 px-3 rounded-xl bg-white border border-amber-300 font-mono text-xs font-bold text-on-surface focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold uppercase tracking-wider font-mono text-amber-900 mb-1">Lý do điều chỉnh / từ chối</label>
                    <input name="decision_note" maxlength="1000" value="{{ old('decision_note') }}"
                           placeholder="Nhập ghi chú hoặc lý do phê duyệt / từ chối..."
                           class="w-full h-10 px-3 rounded-xl bg-white border border-amber-300 text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
            </div>

            <div class="flex items-center gap-2 self-end md:self-end pt-1">
                <button type="submit" name="decision" value="approved"
                        class="h-10 px-4 rounded-xl bg-[#1A4D38] text-white hover:bg-[#143d2c] transition-colors text-xs font-bold inline-flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-[17px]">check_circle</span>
                    <span>Duyệt đề xuất</span>
                </button>
                <button type="submit" name="decision" value="rejected"
                        class="h-10 px-4 rounded-xl bg-surface-container-low text-error hover:bg-error-container/30 transition-colors text-xs font-bold inline-flex items-center gap-1.5 border border-error/30 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[17px]">cancel</span>
                    <span>Từ chối</span>
                </button>
            </div>
        </form>
    @endif

    {{-- ACCORDION CẤU HÌNH PHÂN TÍCH CHO SẢN PHẨM --}}
    @if($isAdmin)
        <details class="group border-t border-surface-container-high/60 pt-3">
            <summary class="text-xs font-semibold text-secondary hover:text-primary cursor-pointer select-none flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] group-open:rotate-90 transition-transform">tune</span>
                    <span>Cấu hình phân tích cho sản phẩm</span>
                </span>
                <span class="text-[11px] font-normal text-on-surface-variant font-mono">Lead Time {{ $product->lead_time_days }}d · Target {{ $product->target_days }}d</span>
            </summary>

            <form action="{{ route('dss.settings', $product->id) }}" method="POST"
                  class="mt-3 p-4 rounded-xl bg-surface-container-low/50 border border-surface-container-high/60 space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Lead Time (ngày)</label>
                        <input name="lead_time_days" type="number" min="0" max="365" value="{{ $product->lead_time_days }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Số ngày dự trữ mục tiêu</label>
                        <input name="target_days" type="number" min="0" max="365" value="{{ $product->target_days }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Cửa sổ MA</label>
                        <select name="ma_days" class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                            @foreach([7,14,30] as $days)
                                <option @selected(($product->ma_days ?? 30) == $days)>{{ $days }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Quy cách đóng gói</label>
                        <input name="pack_size" type="number" min="1" max="1000000" value="{{ $product->pack_size ?? 1 }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Cách tính tồn an toàn</label>
                        <select name="safety_mode" class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs">
                            <option value="manual" @selected($product->safety_mode==='manual')>Nhập thủ công</option>
                            <option value="statistical" @selected($product->safety_mode==='statistical')>Hệ số × độ lệch chuẩn × căn Lead Time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Tồn an toàn thủ công</label>
                        <input name="manual_safety_stock" type="number" min="0" max="1000000" value="{{ $product->manual_safety_stock ?? 0 }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Hệ số an toàn (z)</label>
                        <input name="safety_factor" type="number" min="0" max="3" step="0.01" value="{{ $product->safety_factor }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Xác nhận dữ liệu từ</label>
                        <input name="observation_start" type="date" max="{{ now()->toDateString() }}" value="{{ $product->observation_start }}" required
                               class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs font-mono">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Phương pháp nhu cầu</label>
                        <select name="forecast_method" class="w-full h-9 px-3 rounded-lg bg-white border border-surface-container-high text-xs">
                            <option value="ma">Moving Average</option>
                            <option value="auto" @selected($product->forecast_method==='auto')>ML nếu đánh giá tốt hơn MA; nếu chưa có dùng MA</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            class="h-9 px-4 rounded-lg bg-secondary text-white hover:bg-neutral-800 transition-colors text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                        <span class="material-symbols-outlined text-[16px]">sync</span>
                        <span>Lưu và tính lại</span>
                    </button>
                </div>
            </form>
        </details>
    @endif

</article>
