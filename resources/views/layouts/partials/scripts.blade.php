{{--
    Đường dẫn: resources/views/layouts/partials/scripts.blade.php
    Mô tả: Javascript hệ thống dùng chung (Sidebar Accordion không dùng localStorage, Scroll to top khi chuyển trang, Menu 3 chấm fixed, Debounce tìm kiếm).
--}}

{{-- Fixed Floating Action Menu Container duy nhất cho toàn hệ thống --}}
<div id="globalFloatingDropdown"
     class="hidden fixed z-[9999] min-w-[200px] rounded-2xl bg-white border border-surface-container-high/60 shadow-[0_16px_40px_-4px_rgba(17,24,39,0.16),0_6px_16px_-2px_rgba(17,24,39,0.08)] py-2 text-xs select-none"
     role="menu">
    {{-- Nội dung menu sẽ được nạp động từ nút tương ứng --}}
</div>

<script>
    /**
     * 1. CUỘN TRANG VỀ ĐẦU TRANG & CUỘN MỤC SIDEBAR ACTIVE VÀO TẦM NHÌN
     * // SỬA: Tắt cơ chế khôi phục vị trí cuộn khi điều hướng bằng menu (trang mới luôn ở đầu trang)
     */
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);

    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo(0, 0);

        // Đảm bảo mục sidebar đang active luôn hiển thị trọn vẹn trong vùng cuộn
        const activeSidebarItem = document.querySelector('aside nav a.bg-surface-container-low, aside nav a.font-bold, aside nav a[class*="text-primary font-label-lg"]');
        if (activeSidebarItem) {
            activeSidebarItem.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
    });

    /**
     * 2. QUẢN LÝ NHÓM MENU SIDEBAR ACCORDION
     * // SỬA: KHÔNG lưu/đọc localStorage; trạng thái mở/đóng quyết định theo route hiện tại được server render sẵn trong Blade.
     */
    function toggleSidebarGroup(groupId) {
        const group = document.querySelector(`.sidebar-accordion-group[data-group-id="${groupId}"]`);
        if (!group) return;

        const panel = document.getElementById(`panel-${groupId}`);
        const btn = document.getElementById(`btn-${groupId}`);
        const chevron = btn ? btn.querySelector('.chevron-icon') : null;

        if (!panel || !btn) return;

        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
        const nextState = !isExpanded;

        btn.setAttribute('aria-expanded', String(nextState));

        if (nextState) {
            panel.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            panel.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    /**
     * 3. QUẢN LÝ MENU 3 CHẤM DÒNG (GLOBAL FLOATING ACTION MENU)
     * Định vị Fixed độc lập hoàn toàn, không bị che bởi overflow-x của bảng.
     */
    let activeGlobalTrigger = null;
    const globalFloatingDropdown = document.getElementById('globalFloatingDropdown');

    function closeGlobalDropdown() {
        if (!globalFloatingDropdown) return;
        globalFloatingDropdown.classList.add('hidden');
        globalFloatingDropdown.innerHTML = '';
        if (activeGlobalTrigger) {
            activeGlobalTrigger.setAttribute('aria-expanded', 'false');
            activeGlobalTrigger = null;
        }
    }

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.global-action-trigger');

        if (trigger) {
            e.stopPropagation();

            if (activeGlobalTrigger === trigger) {
                closeGlobalDropdown();
                return;
            }

            closeGlobalDropdown();
            activeGlobalTrigger = trigger;
            trigger.setAttribute('aria-expanded', 'true');

            // Tìm khối nội dung template kế bên
            const contentContainer = trigger.parentElement.querySelector('.global-action-content');
            if (!contentContainer) return;

            // Copy nội dung vào fixed container
            globalFloatingDropdown.innerHTML = contentContainer.innerHTML;

            // Tính toán vị trí fixed
            const rect = trigger.getBoundingClientRect();
            const menuWidth = 200;
            const menuHeight = globalFloatingDropdown.offsetHeight || 140;

            let left = rect.right - menuWidth;
            let top = rect.bottom + 6;

            if (left < 10) left = 10;
            if (top + menuHeight > window.innerHeight) {
                top = rect.top - menuHeight - 6;
            }

            globalFloatingDropdown.style.left = `${left}px`;
            globalFloatingDropdown.style.top = `${top}px`;
            globalFloatingDropdown.classList.remove('hidden');
            return;
        }

        // Bấm ngoài thì đóng menu
        if (!e.target.closest('#globalFloatingDropdown')) {
            closeGlobalDropdown();
        }
    });

    window.addEventListener('scroll', closeGlobalDropdown, true);
    window.addEventListener('resize', closeGlobalDropdown);

    /**
     * 4. DEBOUNCE TÌM KIẾM DÙNG CHUNG (~400ms) & PHÍM TẮT ⌘K / Ctrl+K
     */
    let globalSearchDebounceTimer = null;
    document.addEventListener('input', function(e) {
        const target = e.target;
        if (target && target.matches('input[data-auto-submit="true"]')) {
            clearTimeout(globalSearchDebounceTimer);
            globalSearchDebounceTimer = setTimeout(() => {
                if (target.form) {
                    target.form.submit();
                }
            }, 400);
        }
    });

    window.addEventListener('keydown', function(e) {
        // Phím Escape đóng mọi popup
        if (e.key === 'Escape') {
            closeGlobalDropdown();
        }

        // Phím tắt ⌘K hoặc Ctrl+K focus ô tìm kiếm
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            const searchInput = document.querySelector('input[data-auto-submit="true"], input[name="search"], #topbar-search-input');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }
    });
</script>
