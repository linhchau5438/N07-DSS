{{--
    Đường dẫn: resources/views/reports/forecast.blade.php
    Mô tả: Giao diện Báo cáo BC05 - Phân tích nhu cầu xuất hàng & Dự báo (MA vs Linear Regression) theo chuẩn Monochrome N07.
    Phụ thuộc: ForecastController@index, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'BC05 - Đánh giá dự báo')
@section('page-title', 'Phân tích nhu cầu xuất hàng')
@section('page-subtitle', 'Moving Average và Linear Regression.')

@section('content')
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header (Card trắng rounded-2xl, p-6 md:p-8) --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Phân tích nhu cầu xuất hàng"
                      chip="Báo cáo BC05 · Dự báo nhu cầu"
                      subtitle="Đánh giá và so sánh độ chính xác giữa Moving Average và Linear Regression trên cùng 7 ngày kiểm thử.">
        <x-slot:actions>
            <a href="{{ route('reports.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Tất cả báo cáo</span>
            </a>
            <a href="{{ route('dss.index') }}"
               class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-white">assignment_turned_in</span>
                <span class="text-white">Xem đề xuất DSS</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card                --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- 1. BANNER NGUYÊN TẮC THUẬT TOÁN DSS (Thu gọn được, chuẩn banner-hint) --}}
        <x-ui.banner-hint icon="psychology"
                          title="Quy tắc đánh giá: Moving Average và Linear Regression"
                          message="Mỗi sản phẩm được đánh giá riêng theo đơn vị tính của nó. Linear Regression học xu hướng theo thời gian; chỉ được chọn nếu cả MAE và RMSE thấp hơn MA trên cùng 7 ngày cuối. Dự báo có đơn vị sản phẩm/ngày, không phải tổng tuần. Chưa đủ dữ liệu: cần ít nhất cửa sổ MA + 7 ngày đã kết thúc và ngày bắt đầu quan sát được xác nhận trong cấu hình DSS. Chỉ xuất bán / tiêu thụ được sử dụng; ngày không bán được tính 0.">
            <x-slot:action>
                <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-900 border border-amber-500/30 font-mono text-[11px] font-bold">
                    Kiểm thử 7 ngày
                </span>
            </x-slot:action>
        </x-ui.banner-hint>

        {{-- 2. THANH LỌC VÀ FORM HUẤN LUYỆN --}}
        <div class="p-4 rounded-xl bg-surface-container-low/70 border border-surface-container-high/60 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            {{-- Form lọc theo sản phẩm --}}
            <form method="GET" action="{{ route('reports.bc05') }}" class="flex items-center gap-3 flex-wrap flex-1">
                <label for="filter_product_id" class="text-xs font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-1.5 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[16px] text-secondary">filter_list</span>
                    <span>Lọc lịch sử:</span>
                </label>
                <div class="min-w-[260px] flex-1 max-w-md">
                    <x-ui.filter-select name="product_id" id="filter_product_id" :autoSubmit="true">
                        <option value="">Tất cả sản phẩm</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                {{ $product->product_code }} · {{ $product->name }}
                            </option>
                        @endforeach
                    </x-ui.filter-select>
                </div>
                @if(request('product_id'))
                    <a href="{{ route('reports.bc05') }}"
                       class="h-11 px-3 rounded-xl border border-dashed border-outline-variant hover:bg-surface-container text-xs font-semibold text-on-surface-variant flex items-center gap-1.5 transition-colors whitespace-nowrap">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        <span>Đặt lại</span>
                    </a>
                @endif
            </form>

            {{-- Form Huấn luyện & Chạy đánh giá mới (Dành cho Admin) --}}
            @if(auth()->user()->role === 'admin')
                <form method="POST" action="{{ route('forecast.run') }}" class="flex items-center gap-2 pt-3 lg:pt-0 border-t lg:border-t-0 border-surface-container-high/60">
                    @csrf
                    <div class="min-w-[220px]">
                        <x-ui.filter-select name="product_id" :autoSubmit="false" :required="true" width="w-full">
                            <option value="">-- Chọn mặt hàng chạy ML/MA --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                    {{ $product->product_code }} · {{ $product->name }}
                                </option>
                            @endforeach
                        </x-ui.filter-select>
                    </div>
                    <button type="submit"
                            class="h-11 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors text-xs font-bold inline-flex items-center gap-2 shadow-sm whitespace-nowrap cursor-pointer">
                        <span class="material-symbols-outlined text-[18px] text-white">play_circle</span>
                        <span class="text-white">Huấn luyện, đánh giá và lưu</span>
                    </button>
                </form>
            @endif
        </div>

        {{-- 3. KẾT QUẢ ĐÁNH GIÁ MỚI NHẤT & BIỂU ĐỒ --}}
        @php $latestRun = $runs->first(); @endphp
        @if($latestRun)
            @php
                $latestResult = json_decode($latestRun->results, true);
                $isSelectedLinear = ($latestResult['selected'] ?? '') === 'linear';
                $selectedMethodKey = $isSelectedLinear ? 'linear' : 'ma';
                $selectedMetrics = $latestResult['metrics'][$selectedMethodKey] ?? ['mae' => 0, 'rmse' => 0, 'forecast' => 0];
                $daily = $latestResult['daily'] ?? [];
                $peak = max(1, count($daily) ? max($daily) : 1);
            @endphp

            {{-- A. DẢI 4 THẺ KPI ĐỘ CHÍNH XÁC (GỌN 140PX ĐƠN SẮC) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-ui.kpi-card label="Mô hình tối ưu"
                               :value="$isSelectedLinear ? 'Linear Regression' : 'Moving Average'"
                               icon="auto_awesome"
                               tone="sage"
                               pill="Tối ưu"
                               status="success">
                    <span class="truncate">Thắng kiểm thử sai số 7 ngày</span>
                </x-ui.kpi-card>

                <x-ui.kpi-card label="Sai số MAE"
                               :value="number_format($selectedMetrics['mae'], 4)"
                               icon="straighten"
                               tone="grey"
                               status="warning">
                    <span class="truncate">Sai số tuyệt đối trung bình</span>
                </x-ui.kpi-card>

                <x-ui.kpi-card label="Sai số RMSE"
                               :value="number_format($selectedMetrics['rmse'], 4)"
                               tone="sage"
                               icon="square_foot">
                    <span class="truncate">Căn bậc hai sai số toàn phương</span>
                </x-ui.kpi-card>

                <x-ui.kpi-card label="Nhu cầu dự báo ngày tới"
                               :value="number_format($selectedMetrics['forecast'], 2)"
                               :unit="$latestRun->unit . '/ngày'"
                               icon="trending_up"
                               :highlight="true">
                    <span class="truncate">Ước tính tiêu thụ mỗi ngày</span>
                </x-ui.kpi-card>
            </div>

            {{-- B. BIỂU ĐỒ TIÊU THỤ THỰC TẾ & VÙNG KIỂM THỬ (Chuẩn style Dashboard) --}}
            <div class="p-6 rounded-2xl bg-surface-container-lowest border border-surface-container-high/60 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-secondary"></span>
                            <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono">
                                Biến động tiêu thụ theo ngày ({{ $latestRun->product_code }} · {{ $latestRun->name }})
                            </h3>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Kỳ dữ liệu: <strong class="font-mono text-on-surface">{{ $latestRun->period_start }}</strong> → <strong class="font-mono text-on-surface">{{ $latestRun->period_end }}</strong>
                            · Cửa sổ MA: <strong class="font-mono">{{ $latestRun->ma_days }} ngày</strong>
                            · Phiên bản: <strong class="font-mono">{{ $latestRun->model_version }}</strong>
                            · Chạy lúc: <span class="font-mono text-on-surface-variant">{{ $latestRun->created_at }}</span>
                        </p>
                    </div>

                    {{-- Chú giải màu sắc --}}
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span class="flex items-center gap-1.5 text-on-surface-variant">
                            <span class="w-3 h-3 rounded bg-secondary"></span>
                            <span>Chuỗi quan sát</span>
                        </span>
                        <span class="flex items-center gap-1.5 text-on-surface-variant">
                            <span class="w-3 h-3 rounded bg-amber-500"></span>
                            <span>7 ngày kiểm thử cuối</span>
                        </span>
                    </div>
                </div>

                {{-- Khung vẽ cột trực quan CSS Flexbox --}}
                <div class="p-4 rounded-xl bg-surface-container-low/40 border border-surface-container-high/50">
                    <div class="flex items-end gap-1.5 h-36 overflow-x-auto pt-4 pb-1" role="img" aria-label="Lượng tiêu thụ từng ngày của {{ $latestRun->name }}">
                        @php $totalDays = count($daily); @endphp
                        @foreach($daily as $offset => $quantity)
                            @php
                                $isHoldout = ($totalDays - $offset) <= 7;
                                $dayDate = \Illuminate\Support\Carbon::parse($latestRun->period_start)->addDays($offset)->toDateString();
                                $heightPercent = max(4, round(100 * $quantity / $peak, 1));
                            @endphp
                            <div class="flex-1 min-w-[12px] max-w-[28px] h-full flex flex-col justify-end items-center group relative cursor-pointer">
                                {{-- Tooltip --}}
                                <div class="hidden group-hover:block absolute bottom-full mb-2 z-20 px-2 py-1 rounded bg-neutral-900 text-white text-[10px] font-mono whitespace-nowrap shadow-md pointer-events-none">
                                    {{ $dayDate }}: {{ $quantity }} {{ $latestRun->unit }}
                                    @if($isHoldout) (Kiểm thử) @endif
                                </div>
                                {{-- Cột số liệu --}}
                                <div class="w-full rounded-t transition-all {{ $isHoldout ? 'bg-amber-500 group-hover:bg-amber-600' : 'bg-secondary group-hover:bg-[#1A4D38]' }}"
                                     style="height: {{ $heightPercent }}%;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-mono text-on-surface-variant pt-2 border-t border-surface-container-high/60 mt-1">
                        <span>{{ $latestRun->period_start }} (Bắt đầu chuỗi)</span>
                        <span class="text-amber-700 font-semibold">Vùng 7 ngày kiểm thử cuối</span>
                        <span>{{ $latestRun->period_end }} (Kết thúc)</span>
                    </div>
                </div>

                {{-- Bảng so sánh trực tiếp chỉ số 2 mô hình trên lần chạy này DÙNG x-ui.data-table --}}
                <x-ui.data-table minWidth="min-w-[800px]">
                    <x-slot:header>
                        <th class="py-3.5 pl-6 pr-4">Phương pháp thuật toán</th>
                        <th class="py-3.5 px-5 text-right w-44">Sai số tuyệt đối (MAE)</th>
                        <th class="py-3.5 px-5 text-right w-44">Sai số toàn phương (RMSE)</th>
                        <th class="py-3.5 px-5 text-right w-52">Dự báo ngày kế tiếp</th>
                        <th class="py-3.5 pr-6 pl-4 text-center w-40">Kết quả lựa chọn</th>
                    </x-slot:header>

                    @foreach($latestResult['metrics'] as $method => $values)
                        @php $isMethodSelected = ($latestResult['selected'] ?? '') === $method; @endphp
                        <tr class="hover:bg-surface-container-low/70 transition-colors h-[72px] {{ $isMethodSelected ? 'bg-emerald-50/40 font-semibold' : '' }}">
                            <td class="py-5 pl-6 pr-4 flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full {{ $isMethodSelected ? 'bg-emerald-700' : 'bg-on-surface-variant/40' }}"></span>
                                <span class="font-bold text-base text-on-surface">{{ $method === 'ma' ? 'Moving Average (Trung bình trượt)' : 'Linear Regression (Hồi quy tuyến tính)' }}</span>
                            </td>
                            <td class="py-5 px-5 text-right font-mono text-[15px] tabular-nums text-on-surface">{{ number_format($values['mae'], 4) }}</td>
                            <td class="py-5 px-5 text-right font-mono text-[15px] tabular-nums text-on-surface">{{ number_format($values['rmse'], 4) }}</td>
                            <td class="py-5 px-5 text-right font-mono text-[15px] tabular-nums text-primary font-bold">
                                {{ number_format($values['forecast'], 4) }} <span class="text-xs font-normal text-on-surface-variant">{{ $latestRun->unit }}/ngày</span>
                            </td>
                            <td class="py-5 pr-6 pl-4 text-center whitespace-nowrap">
                                @if($isMethodSelected)
                                    <x-ui.status-pill variant="success" icon="check_circle" label="Mô hình tối ưu" />
                                @else
                                    <x-ui.status-pill variant="neutral" label="Dự phòng" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>

                {{-- Chi tiết dấu vết tái lập --}}
                <details class="text-xs text-on-surface-variant cursor-pointer group">
                    <summary class="font-semibold text-secondary hover:underline flex items-center gap-1.5 select-none">
                        <span class="material-symbols-outlined text-[16px] group-open:rotate-90 transition-transform">chevron_right</span>
                        <span>Chuỗi dữ liệu quan sát và mã băm dấu vết tái lập</span>
                    </summary>
                    <div class="mt-2.5 p-3 rounded-lg bg-surface-container-low font-mono text-[11px] space-y-1.5 break-all">
                        <p><strong class="text-on-surface">Dữ liệu gốc ({{ count($daily) }} ngày):</strong> {{ implode(', ', $daily) }}</p>
                        <p><strong class="text-on-surface">SHA256:</strong> <code>{{ $latestResult['series_sha256'] ?? 'N/A' }}</code></p>
                    </div>
                </details>
            </div>
        @endif

        {{-- 4. BẢNG DỮ LIỆU LỊCH SỬ CÁC LẦN ĐÁNH GIÁ (DÙNG x-ui.data-table) --}}
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-mono flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-secondary"></span>
                <span>Lịch sử các lần chạy đánh giá mô hình</span>
            </h3>

            <x-ui.data-table minWidth="min-w-[1100px]">
                <x-slot:header>
                    <th class="py-3.5 pl-6 pr-4 min-w-[260px]">Mã &amp; Mặt hàng</th>
                    <th class="py-3.5 px-5 min-w-[180px]">Kỳ quan sát</th>
                    <th class="py-3.5 px-5 text-center w-28">Cửa sổ MA</th>
                    <th class="py-3.5 px-5 text-center w-44">Mô hình tối ưu</th>
                    <th class="py-3.5 px-5 text-right w-36">Sai số (MAE)</th>
                    <th class="py-3.5 px-5 text-right w-36">Sai số (RMSE)</th>
                    <th class="py-3.5 px-5 text-right w-40">Dự báo</th>
                    <th class="py-3.5 pr-6 pl-4 text-right w-44">Thời gian chạy</th>
                </x-slot:header>

                @forelse($runs as $run)
                    @php
                        $res = json_decode($run->results, true);
                        $sel = ($res['selected'] ?? '') === 'linear';
                        $metrics = $res['metrics'][$sel ? 'linear' : 'ma'] ?? ['mae'=>0, 'rmse'=>0, 'forecast'=>0];
                    @endphp
                    <tr class="h-[72px] hover:bg-surface-container-low/70 transition-colors">
                        {{-- Sản phẩm & SKU dùng x-ui.product-cell --}}
                        <td class="py-5 pl-6 pr-4">
                            <x-ui.product-cell :name="$run->name"
                                               :sku="$run->product_code" />
                        </td>
                        <td class="py-5 px-5 font-mono text-[14px] text-on-surface-variant whitespace-nowrap">
                            {{ $run->period_start }} → {{ $run->period_end }}
                        </td>
                        <td class="py-5 px-5 text-center font-mono text-[15px] font-semibold whitespace-nowrap">
                            {{ $run->ma_days }} ngày
                        </td>
                        <td class="py-5 px-5 text-center whitespace-nowrap">
                            @if($sel)
                                <x-ui.status-pill variant="success" label="Linear Regression" />
                            @else
                                <x-ui.status-pill variant="neutral" label="Moving Average" />
                            @endif
                        </td>
                        <td class="py-5 px-5 text-right font-mono text-[15px] tabular-nums whitespace-nowrap">
                            {{ number_format($metrics['mae'], 4) }}
                        </td>
                        <td class="py-5 px-5 text-right font-mono text-[15px] tabular-nums whitespace-nowrap">
                            {{ number_format($metrics['rmse'], 4) }}
                        </td>
                        <td class="py-5 px-5 text-right font-mono text-[15px] font-bold text-primary tabular-nums whitespace-nowrap">
                            {{ number_format($metrics['forecast'], 2) }} <span class="text-xs font-normal text-on-surface-variant">{{ $run->unit }}</span>
                        </td>
                        <td class="py-5 pr-6 pl-4 text-right font-mono text-[14px] text-on-surface-variant whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($run->created_at)->format('H:i d/m/Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0 border-0">
                            <x-ui.empty-state icon="model_training"
                                              title="Chưa có dữ liệu đánh giá"
                                              message="Chưa có lần đánh giá đã lưu. Admin chọn sản phẩm và chạy đánh giá khi dữ liệu đủ dài."
                                              :actionUrl="route('reports.bc05')"
                                              actionText="Làm mới trang"
                                              actionIcon="refresh" />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            {{-- Phân trang chuẩn N07 --}}
            <div class="pt-2">
                {{ $runs->links('pagination.n07') }}
            </div>
        </div>

    </x-ui.section-card>

</div>
@endsection
