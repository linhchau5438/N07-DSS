{{-- Component: cell-index (Cột số thứ tự/Index chuẩn 14px tabular-nums căn giữa, w-14) --}}
@props(['index'])
<td {{ $attributes->merge(['class' => 'py-5 px-3 text-center text-sm font-medium font-mono tabular-nums text-on-surface-variant/70 w-14 select-none']) }}>
    {{ $index }}
</td>
