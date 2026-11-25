{{--
    Đường dẫn: resources/views/components/ui/stock-progress.blade.php
    Mô tả: Ô hiển thị lượng tồn thực tế kèm thanh tiến độ định mức an toàn, số lượng 15px tabular-nums.
--}}
@props([
    'onHand',
    'unit' => '',
    'safetyStock' => null,
    'reorderPoint' => null,
    'percent' => null,
])

@php
    $qty = (float) $onHand;
    if ($percent === null) {
        $threshold = max(10, (float) ($reorderPoint ?? $safetyStock ?? 10) * 1.5);
        $percent = min(100, max(0, round(($qty / max(1, $threshold)) * 100)));
    }

    if ($qty <= 0) {
        $textColor = 'text-error';
        $barColor = 'bg-error';
    } elseif ($reorderPoint !== null && $qty <= (float) $reorderPoint) {
        $textColor = 'text-amber-800';
        $barColor = 'bg-amber-500';
    } elseif ($qty <= 10) {
        $textColor = 'text-amber-800';
        $barColor = 'bg-amber-500';
    } else {
        $textColor = 'text-on-surface';
        $barColor = 'bg-secondary';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1.5 min-w-[170px]']) }}>
    <div class="flex justify-between items-baseline text-[15px]">
        <span class="font-bold {{ $textColor }} tabular-nums whitespace-nowrap">
            {{ $onHand }} <span class="font-normal text-on-surface-variant text-[13px]">{{ $unit }}</span>
        </span>
        @if($safetyStock !== null)
            <span class="text-[13px] font-mono text-on-surface-variant whitespace-nowrap">An toàn: {{ $safetyStock }}</span>
        @endif
    </div>
    <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
        <div class="h-2 rounded-full {{ $barColor }} transition-all"
             style="width: {{ $percent }}%"></div>
    </div>
</div>
