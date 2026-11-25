{{--
    Đường dẫn: resources/views/components/ui/kpi-card.blade.php
    Mô tả: Thẻ hiển thị chỉ số KPI đơn sắc xanh - xám - đen theo chuẩn thiết kế N07.
    Quy tắc:
      - Kích thước: min-h-[140px], padding p-5, bo rounded-2xl, gap-4 giữa các thẻ.
      - Bố cục 3 phần:
        + Hàng trên: Nhãn 12px uppercase font-semibold tracking-wider + Ô icon 40px rounded-xl.
        + Giữa: SỐ CHÍNH 32px font-bold tabular-nums (28px nếu chuỗi dài) + đơn vị 14px + pill nhỏ nowrap nếu có.
        + Chân: Một dòng 13px màu on-surface-variant/xám truncate, không kẻ phân cách, không gãy dòng.
      - Hệ màu đơn sắc:
        + Thẻ nhấn mạnh (highlight=true): nền #1A4D38, chữ trắng, nhãn rgba trắng 80%, ô icon nền trắng 15% icon trắng.
        + Thẻ thông thường: luân phiên 2 tông xanh-xám nhạt (#DDE8E2 / #C5D6CC / số #12382A) và xám trung tính (#E4E7EA / #D0D5DA / số #1F2933). Ô icon nền trắng, nhãn #3F4A45, chân #4B5563.
        + Cảnh báo: Chấm 8px + chữ pill nhỏ (đỏ trầm #B42318 cho nguy cấp, nâu #92600A cho chú ý).
--}}
@props([
    'label',
    'value',
    'unit' => null,
    'icon' => null,
    'pill' => null,
    'footer' => null,
    'status' => null,
    'tone' => null,
    'variant' => null,
    'highlight' => false,
    'index' => null,
])

@php
    $resolvedTone = $status ?? $tone ?? $variant ?? 'default';

    // Thẻ nhấn mạnh duy nhất (nếu có)
    $isHighlighted = $highlight || $resolvedTone === 'highlight' || $resolvedTone === 'solid-green';

    // Định dạng độ lớn số chính (trên 12 ký tự hoặc số tiền dài thì 28px, bình thường 32px)
    $cleanValueStr = strip_tags((string) $value);
    $isLongValue = mb_strlen($cleanValueStr) >= 11;
    $valueSizeClass = $isLongValue ? 'text-[28px]' : 'text-[32px]';

    // Xử lý cảnh báo (chấm 8px + pill text)
    $dotClass = null;
    $pillColorClass = null;
    $pillBgClass = null;

    if (in_array($resolvedTone, ['danger', 'critical', 'rose', 'error'])) {
        $dotClass = 'bg-[#B42318]';
        $pillColorClass = 'text-[#B42318]';
        $pillBgClass = 'bg-white/80 border border-[#B42318]/30';
    } elseif (in_array($resolvedTone, ['warning', 'attention', 'amber'])) {
        $dotClass = 'bg-[#92600A]';
        $pillColorClass = 'text-[#92600A]';
        $pillBgClass = 'bg-white/80 border border-[#92600A]/30';
    } elseif (in_array($resolvedTone, ['success', 'positive', 'mint'])) {
        $dotClass = 'bg-[#1A4D38]';
        $pillColorClass = 'text-[#1A4D38]';
        $pillBgClass = 'bg-white/80 border border-[#1A4D38]/30';
    } else {
        $pillColorClass = 'text-[#4B5563]';
        $pillBgClass = 'bg-white/80 border border-surface-container-high/70';
    }

    if ($isHighlighted) {
        // Thẻ nhấn mạnh: Xanh rừng đậm #1A4D38, chữ trắng
        $cardStyle = 'bg-[#1A4D38] border-[#1A4D38] hover:border-[#12382A] text-white';
        $labelColor = 'text-white/80';
        $iconBoxStyle = 'bg-white/15 text-white';
        $numberColor = 'text-white';
        $unitColor = 'text-white/80';
        $footerColor = 'text-white/80';
        $pillFinalClass = 'bg-white/20 text-white border border-white/20';
        $dotClass = $dotClass ?? 'bg-white';
    } else {
        // Thẻ thông thường: Luân phiên 2 tông xanh-xám (#DDE8E2) và xám trung tính (#E4E7EA)
        $isGreyTone = ($tone === 'grey' || $tone === 'neutral' || (is_numeric($index) && $index % 2 === 1));

        if ($isGreyTone) {
            // Tông 2: Xám trung tính #E4E7EA (viền #D0D5DA) chữ số #1F2933
            $cardStyle = 'bg-[#E4E7EA] border-[#D0D5DA] hover:border-[#BFC5CB] text-on-surface';
            $iconBoxStyle = 'bg-white text-[#1F2933] shadow-2xs';
            $numberColor = 'text-[#1F2933]';
        } else {
            // Tông 1: Xanh-xám nhạt #DDE8E2 (viền #C5D6CC) chữ số #12382A
            $cardStyle = 'bg-[#DDE8E2] border-[#C5D6CC] hover:border-[#A8C2B3] text-on-surface';
            $iconBoxStyle = 'bg-white text-[#1A4D38] shadow-2xs';
            $numberColor = 'text-[#12382A]';
        }

        $labelColor = 'text-[#3F4A45]';
        $unitColor = 'text-[#4B5563]';
        $footerColor = 'text-[#4B5563]';
        $pillFinalClass = "$pillBgClass $pillColorClass";
    }
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl p-5 min-h-[140px] flex flex-col justify-between border shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xs select-none " . $cardStyle]) }}>
    {{-- Hàng 1: Nhãn (12px uppercase font-semibold tracking-wider) + Ô icon 40px rounded-xl --}}
    <div class="flex items-center justify-between gap-3 min-w-0">
        <span class="text-xs font-semibold uppercase tracking-wider {{ $labelColor }} truncate">
            {{ $label }}
        </span>
        @if($icon)
            <div class="w-10 h-10 rounded-xl {{ $iconBoxStyle }} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    {{-- Hàng 2: SỐ CHÍNH 32px (hoặc 28px nếu dài) tabular-nums font-bold + đơn vị 14px + pill nhỏ nowrap nếu có --}}
    <div class="my-auto py-1 flex items-baseline gap-2 flex-wrap min-w-0">
        <span class="{{ $valueSizeClass }} font-bold tabular-nums tracking-tight leading-none {{ $numberColor }} font-sans">
            {{ $value }}
        </span>
        @if($unit)
            <span class="text-sm font-medium {{ $unitColor }} font-sans">{{ $unit }}</span>
        @endif
        @if($pill)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $pillFinalClass }}">
                @if($dotClass)
                    <span class="w-2 h-2 rounded-full {{ $dotClass }} shrink-0"></span>
                @endif
                <span>{{ $pill }}</span>
            </span>
        @endif
    </div>

    {{-- Hàng 3: Dòng chân 13px màu on-surface-variant truncate, không đường kẻ phân cách, không gãy dòng --}}
    @if($footer || !empty(trim($slot)))
        <div class="text-[13px] {{ $footerColor }} truncate whitespace-nowrap font-medium min-w-0">
            {{ $footer ?? $slot }}
        </div>
    @endif
</div>
