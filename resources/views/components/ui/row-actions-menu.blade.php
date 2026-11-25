{{--
    Đường dẫn: resources/views/components/ui/row-actions-menu.blade.php
    Mô tả: Nút menu 3 chấm (more_vert) chuẩn hóa 36px cho mọi hàng bảng và thẻ dữ liệu.
    Nội dung menu trong slot sẽ được quản lý bởi Floating Container dùng chung trong layout.
--}}
<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-end relative']) }}>
    <button type="button"
            class="global-action-trigger w-9 h-9 rounded-xl inline-flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors"
            aria-haspopup="true"
            aria-expanded="false"
            title="Thao tác">
        <span class="material-symbols-outlined text-[22px] pointer-events-none">more_vert</span>
    </button>

    {{-- Template nội dung menu ẩn, sẽ được JS nạp vào fixed floating container khi bấm --}}
    <div class="global-action-content hidden">
        {{ $slot }}
    </div>
</div>
