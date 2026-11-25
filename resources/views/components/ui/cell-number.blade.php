{{-- Component: cell-number (Số lượng, tỉ lệ, điểm chuẩn 15px tabular-nums căn phải) --}}
@props([
    'value',
    'unit' => null,
    'highlight' => false,
])
<td {{ $attributes->merge(['class' => 'py-5 px-5 text-right whitespace-nowrap']) }}>
    <span class="text-[15px] tabular-nums font-sans {{ $highlight ? 'font-bold text-primary' : 'font-medium text-on-surface' }}">
        {{ is_numeric($value) ? number_format($value) : $value }}
    </span>
    @if($unit)
        <span class="text-[13px] text-on-surface-variant font-normal ml-1">{{ $unit }}</span>
    @endif
</td>
