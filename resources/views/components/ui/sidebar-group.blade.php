{{--
    Đường dẫn: resources/views/components/ui/sidebar-group.blade.php
    Mô tả: Component Nhóm Menu Sidebar đóng/mở dạng Accordion tái sử dụng được.
    Props nhận vào:
      - $id (string, bắt buộc): ID định danh nhóm (dùng cho aria-controls, localStorage)
      - $title (string, bắt buộc): Tên nhóm menu cha
      - $icon (string, bắt buộc): Material Symbols icon
      - $active (bool, mặc định false): Nhóm có đang chứa trang hiện tại hay không
--}}
@props([
    'id',
    'title',
    'icon',
    'active' => false,
])

<div class="sidebar-accordion-group" data-group-id="{{ $id }}">
    {{-- Nút tiêu đề nhóm cha --}}
    <button type="button"
            id="btn-{{ $id }}"
            aria-expanded="{{ $active ? 'true' : 'false' }}"
            aria-controls="panel-{{ $id }}"
            onclick="toggleSidebarGroup('{{ $id }}')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-xl transition-all font-body-md text-body-md select-none {{ $active ? 'bg-surface-container-low text-on-surface font-semibold' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
        <div class="flex items-center gap-3 min-w-0">
            <span class="material-symbols-outlined text-[20px] shrink-0">{{ $icon }}</span>
            <span class="truncate text-left">{{ $title }}</span>
        </div>
        <span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform duration-200 chevron-icon {{ $active ? 'rotate-180' : '' }}">
            expand_more
        </span>
    </button>

    {{-- Danh sách menu con (thụt lề, có đường kẻ dọc mảnh bên trái) --}}
    <div id="panel-{{ $id }}"
         class="sidebar-accordion-panel {{ $active ? '' : 'hidden' }} border-l border-surface-container-high/80 pl-3 ml-5 space-y-1 mt-1 transition-all">
        {{ $slot }}
    </div>
</div>
