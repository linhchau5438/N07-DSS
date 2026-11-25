{{-- Component: cell-primary (Tên/Giá trị chính 16px font-semibold + dòng phụ 13px) --}}
@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'icon' => null,
    'iconBg' => 'bg-surface-container-low text-on-surface',
    'titleClass' => '',
])
<td {{ $attributes->merge(['class' => 'py-5 px-5']) }}>
    <div class="flex items-center gap-3">
        @if($icon)
            <div class="w-11 h-11 rounded-xl {{ $iconBg }} border border-surface-container-high/60 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
            </div>
        @endif
        <div class="min-w-0 max-w-md">
            @if($href)
                <a href="{{ $href }}" class="font-semibold text-base text-on-surface hover:text-primary hover:underline transition-colors block truncate {{ $titleClass }}" title="{{ $title }}">
                    {{ $title }}
                </a>
            @else
                <span class="font-semibold text-base text-on-surface block truncate {{ $titleClass }}" title="{{ $title }}">
                    {{ $title }}
                </span>
            @endif

            @if($subtitle)
                <span class="text-[13px] text-on-surface-variant block truncate mt-0.5" title="{{ $subtitle }}">
                    {{ $subtitle }}
                </span>
            @endif
            @if(isset($slot) && !empty(trim($slot)))
                {{ $slot }}
            @endif
        </div>
    </div>
</td>
