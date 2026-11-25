{{-- Component: cell-code (Mã định danh/SKU/Mã phiếu chuẩn font-mono 14px) --}}
@props([
    'code',
    'href' => null,
    'prefix' => null,
])
<td {{ $attributes->merge(['class' => 'py-5 px-5 whitespace-nowrap']) }}>
    @if($href)
        <a href="{{ $href }}" class="font-mono text-sm font-semibold text-on-surface hover:text-black hover:underline transition-colors inline-flex items-center gap-1">
            @if($prefix)<span class="text-on-surface-variant text-xs">{{ $prefix }}</span>@endif
            <span>{{ $code }}</span>
        </a>
    @else
        <span class="font-mono text-sm font-semibold text-on-surface inline-flex items-center gap-1">
            @if($prefix)<span class="text-on-surface-variant text-xs">{{ $prefix }}</span>@endif
            <span>{{ $code }}</span>
        </span>
    @endif
</td>
