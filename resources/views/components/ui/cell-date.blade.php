{{-- Component: cell-date (Ngày tháng định dạng dd/MM/yyyy căn trái 14-15px) --}}
@props([
    'date' => null,
    'value' => null,
    'format' => 'd/m/Y',
    'time' => null,
])
@php
    $rawDate = $date ?? $value;
    $formattedDate = $rawDate ? (\Illuminate\Support\Carbon::parse($rawDate)->format($format)) : '—';
    $formattedTime = ($time && $rawDate) ? (\Illuminate\Support\Carbon::parse($rawDate)->format('H:i')) : null;
@endphp
<td {{ $attributes->merge(['class' => 'py-5 px-5 text-left whitespace-nowrap']) }}>
    <span class="text-[14px] text-on-surface font-medium block">
        {{ $formattedDate }}
    </span>
    @if($formattedTime)
        <span class="text-[12px] text-on-surface-variant block font-mono mt-0.5">
            {{ $formattedTime }}
        </span>
    @endif
</td>
