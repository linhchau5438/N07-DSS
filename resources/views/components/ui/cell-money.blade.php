{{-- Component: cell-money (Số tiền chuẩn 15px tabular-nums căn phải font-semibold) --}}
@props([
    'amount',
    'currency' => 'đ',
])
<td {{ $attributes->merge(['class' => 'py-5 px-5 text-right whitespace-nowrap']) }}>
    <span class="text-[15px] tabular-nums font-mono font-semibold text-on-surface">
        {{ is_numeric($amount) ? number_format($amount, 0, ',', '.') : $amount }}
    </span>
    <span class="text-[13px] text-on-surface-variant font-normal ml-0.5">{{ $currency }}</span>
</td>
