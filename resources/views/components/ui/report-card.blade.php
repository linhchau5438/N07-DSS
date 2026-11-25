{{--
    Đường dẫn: resources/views/components/ui/report-card.blade.php
    Mô tả: Component Card Báo cáo theo chuẩn Monochrome N07.
    Công dụng: Hiển thị tóm tắt từng báo cáo (mã BC, icon, tiêu đề, mô tả, chỉ số tóm tắt và nút mở).
--}}
@props([
    'code' => 'BC01',
    'title' => '',
    'description' => '',
    'icon' => 'analytics',
    'route' => '#',
    'highlight' => false,
    'badge' => null,
    'metric' => null,
])

{{-- Biến thể Tiêu chuẩn & Điểm nhấn nhẹ theo ngữ nghĩa --}}
<article class="relative flex flex-col justify-between p-6 rounded-2xl bg-surface-container-lowest border {{ $highlight ? 'border-emerald-600/50 shadow-sm ring-1 ring-emerald-500/15' : 'border-surface-container-high/60 shadow-sm' }} hover:border-surface-container-highest hover:shadow-md transition-all group">
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl {{ $highlight ? 'bg-emerald-100 text-emerald-800 border border-emerald-300/50' : 'bg-surface-container-low text-on-surface border border-surface-container-high/60' }} font-mono font-bold text-xs tracking-wider flex items-center justify-center">
                    {{ $code }}
                </span>
                @if($badge)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $highlight ? 'bg-emerald-100/70 text-emerald-900 border border-emerald-300/50' : 'bg-surface-container-low text-on-surface-variant border border-surface-container-high/70' }} whitespace-nowrap">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            <div class="w-10 h-10 rounded-xl {{ $highlight ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-container-low text-on-surface-variant' }} flex items-center justify-center group-hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
            </div>
        </div>

        <h3 class="text-base font-bold text-on-surface tracking-tight leading-snug mb-2 group-hover:text-primary transition-colors">
            {{ $title }}
        </h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-4">
            {{ $description }}
        </p>
    </div>

    <div class="pt-4 border-t border-surface-container-high/60 flex items-center justify-between mt-auto">
        @if($metric)
            <span class="text-xs font-medium {{ $highlight ? 'text-emerald-800' : 'text-on-surface-variant' }} flex items-center gap-1.5 whitespace-nowrap">
                <span class="w-1.5 h-1.5 rounded-full {{ $highlight ? 'bg-emerald-600' : 'bg-secondary' }}"></span>
                {{ $metric }}
            </span>
        @else
            <span></span>
        @endif

        <a href="{{ $route }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl {{ $highlight ? 'bg-primary text-white hover:bg-neutral-800' : 'bg-surface-container-low text-on-surface hover:bg-surface-container border border-surface-container-high/60' }} font-label-lg text-xs font-semibold transition-all">
            <span class="text-inherit">Mở báo cáo</span>
            <span class="material-symbols-outlined text-[16px] text-inherit group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
        </a>
    </div>
</article>
