{{--
    Đường dẫn: resources/views/components/ui/modal.blade.php
    Mô tả: Hộp thoại (Modal) dùng chung cho thông báo, xác nhận thao tác hoặc xác nhận xóa.
--}}
@props([
    'id',
    'title' => 'Xác nhận thao tác',
    'maxWidth' => 'max-w-md',
])

<div id="{{ $id }}"
     class="fixed inset-0 z-50 hidden overflow-y-auto bg-neutral-900/50 backdrop-blur-xs flex items-center justify-center p-4"
     aria-modal="true" role="dialog">
    <div class="relative w-full {{ $maxWidth }} rounded-2xl bg-white border border-surface-container-high/60 shadow-xl overflow-hidden transition-all duration-300">
        {{-- Header modal --}}
        <div class="p-5 border-b border-surface-container-high/60 flex items-center justify-between">
            <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider flex items-center gap-2">
                {{ $title }}
            </h3>
            <button type="button" onclick="closeModal('{{ $id }}')"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        {{-- Body modal --}}
        <div class="p-6 text-sm text-on-surface-variant">
            {{ $slot }}
        </div>

        {{-- Footer modal --}}
        @if(isset($footer))
            <div class="p-4 bg-surface-container-low/50 border-t border-surface-container-high/60 flex items-center justify-end gap-2.5">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
