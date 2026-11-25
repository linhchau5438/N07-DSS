{{-- File: resources/views/layouts/app.blade.php --}}
{{-- Mô tả: Layout khung chính hệ thống N07 Inventory (Sidebar cố định w-64 chia 3 phần độc lập, Top Header thống nhất chiều cao 80px/5rem liền mạch với Sidebar Logo, Dropdown thông báo & ô tìm kiếm chống đè icon, Wrapper main cách header chuẩn 24px, tích hợp Tailwind v4 Design System). --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'N07 Inventory') | N07 Inventory</title>

    {{-- Tự host Font chữ và Icon nội bộ - Tuyệt đối không gọi CDN nước ngoài chặn render --}}
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-2.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    {{-- CSS tĩnh biên dịch sẵn (Tailwind v4) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Khai báo biến CSS duy nhất --topbar-h: 5rem (80px) để đồng bộ tuyệt đối chiều cao khung Logo và Header --}}
    <style>
        :root {
            --topbar-h: 5rem; /* 80px (h-20) thống nhất giữa Sidebar Logo và Header */
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        /* Thanh cuộn mảnh 6px màu #D1D5DB cho vùng Menu cuộn */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
            display: block !important;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #D1D5DB;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #9CA3AF;
        }
        .sidebar-toggle {
            display: none;
        }
        @media (max-width: 1023px) {
            .sidebar-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem;
                border-radius: 0.75rem;
                color: #45464c;
            }
            .sidebar-toggle:hover {
                background-color: #f3f4f6;
            }
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #F1F3F5;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Bỏ hoàn toàn mũi tên select mặc định của trình duyệt/forms plugin */
        select {
            background-image: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }
        .topbar-breadcrumbs {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8125rem;
            color: #64748B;
        }
        .topbar-breadcrumbs a {
            color: #475569;
            text-decoration: none;
            font-weight: 500;
        }
        .topbar-breadcrumbs a:hover {
            color: #1A4D38;
        }
        .topbar-breadcrumbs .sep {
            color: #94A3B8;
            user-select: none;
        }
        .topbar-breadcrumbs .current {
            color: #0F172A;
            font-weight: 600;
        }
        @media print {
            #app-sidebar, header, .sidebar-toggle, .no-print, button, #globalFloatingDropdown, #sidebar-backdrop {
                display: none !important;
            }
            .pl-64 {
                padding-left: 0 !important;
            }
            main {
                padding-top: 0 !important;
                background: white !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="bg-[#F1F3F5] font-body-md text-on-surface antialiased">

{{-- Thư viện biểu tượng SVG để tương thích với các view báo cáo & trang con --}}
<svg class="icon-library" style="display:none;" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    <symbol id="icon-dashboard" viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></symbol>
    <symbol id="icon-product" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v9"/></symbol>
    <symbol id="icon-category" viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></symbol>
    <symbol id="icon-import" viewBox="0 0 24 24"><path d="M12 15V3m0 0L7 8m5-5 5 5"/><path d="M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/></symbol>
    <symbol id="icon-export" viewBox="0 0 24 24"><path d="M12 9v12m0 0 5-5m-5 5-5-5"/><path d="M5 11V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6"/></symbol>
    <symbol id="icon-stock" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01"/></symbol>
    <symbol id="icon-history" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 2.64-6.36L3 8"/><path d="M3 3v5h5m4-1v5l3 2"/></symbol>
    <symbol id="icon-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/></symbol>
    <symbol id="icon-alert" viewBox="0 0 24 24"><path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/></symbol>
    <symbol id="icon-reorder" viewBox="0 0 24 24"><path d="M4 7h15m0 0-4-4m4 4-4 4M20 17H5m0 0 4-4m-4 4 4 4"/></symbol>
    <symbol id="icon-report" viewBox="0 0 24 24"><path d="M4 3h12l4 4v14H4z"/><path d="M16 3v5h4M8 13h8m-8 4h8"/></symbol>
    <symbol id="icon-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.9a4 4 0 0 1 0 7.8m2 4a4 4 0 0 1 2 3.5V21"/></symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.7-1.4-2.4L7.3 15a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.7 1.4 2.4-1.4 1.1a8 8 0 0 1 0 2Z"/></symbol>
    <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></symbol>
    <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4"/></symbol>
    <symbol id="icon-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
    <symbol id="icon-audit" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></symbol>
</svg>

{{-- Backdrop che mờ khi mở Sidebar trên Mobile --}}
<div id="sidebar-backdrop" onclick="toggleMenu()" class="fixed inset-0 bg-neutral-900/40 z-40 hidden lg:hidden transition-opacity duration-300"></div>

{{-- ========================================================================= --}}
{{-- SIDEBAR CỐ ĐỊNH W-64: Gồm 3 phần tách bạch theo đúng yêu cầu               --}}
{{-- ========================================================================= --}}
<aside id="app-sidebar" class="fixed left-0 top-0 h-screen w-64 flex flex-col bg-white z-50 shadow-[0_1px_8px_rgba(0,0,0,0.04)] select-none -translate-x-full lg:translate-x-0 transition-transform duration-300">
    {{-- Phần a: Khung Logo chiều cao chuẩn h-[var(--topbar-h)] (80px), căn giữa dọc items-center --}}
    <div class="shrink-0 h-[var(--topbar-h)] px-4 flex items-center justify-between border-b border-surface-container-high/60 bg-white">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#1A4D38] flex items-center justify-center text-white font-bold text-base shadow-sm shrink-0">
                N7
            </div>
            <div class="flex flex-col text-left">
                <span class="font-headline-sm text-[16px] text-on-surface tracking-tight leading-tight font-bold">N07 Inventory</span>
                <span class="font-label-sm text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold leading-tight mt-0.5">Kho vận thông minh</span>
            </div>
        </a>
        <button type="button" class="lg:hidden p-1.5 text-on-surface-variant hover:text-on-surface" onclick="toggleMenu()">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    {{-- Phần b: Menu cuộn độc lập --}}
    <nav class="flex-1 min-h-0 overflow-y-auto px-2 pt-4 pb-4 space-y-4 custom-scrollbar">
        {{-- Nhóm 1: Tổng quan --}}
        <div class="space-y-1">
            <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Tổng quan</div>
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('dashboard') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">grid_view</span>
                <span>Tổng quan kho</span>
            </a>
        </div>

        {{-- Nhóm 2: Quản lý hàng hóa --}}
        <div class="space-y-1">
            <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Quản lý hàng hóa</div>
            <a href="{{ route('products.index') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('products.*') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                <span>Danh sách sản phẩm</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('categories.*') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                    <span class="material-symbols-outlined text-[20px]">category</span>
                    <span>Danh mục hàng hóa</span>
                </a>
            @endif
        </div>

        {{-- Nhóm 3: Nghiệp vụ kho --}}
        <div class="space-y-1">
            <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Nghiệp vụ kho</div>
            <a href="{{ route('inventory.create', 'import') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('inventory.create') && request()->route('type') === 'import' ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">move_to_inbox</span>
                <span>Phiếu nhập kho</span>
            </a>
            <a href="{{ route('inventory.create', 'export') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('inventory.create') && request()->route('type') === 'export' ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">unarchive</span>
                <span>Phiếu xuất kho</span>
            </a>
            <a href="{{ route('inventory.index') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('inventory.index') && request('stock') !== 'attention' ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">warehouse</span>
                <span>Tra cứu tồn kho</span>
            </a>
        </div>

        {{-- Nhóm 4: Hỗ trợ nhập hàng --}}
        <div class="space-y-1">
            <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Hỗ trợ nhập hàng</div>
            <a href="{{ route('dss.index') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('dss.index') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px] text-secondary">assignment_turned_in</span>
                <span class="font-semibold text-secondary">Đề xuất bổ sung hàng</span>
            </a>
            <a href="{{ route('dss.history') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('dss.history') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">manage_history</span>
                <span>Lịch sử đặt & nhận hàng</span>
            </a>
        </div>

        {{-- Nhóm 5: Báo cáo & Phân tích --}}
        <div class="space-y-1">
            <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Báo cáo & Phân tích</div>
            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('reports.index') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">analytics</span>
                <span>Trung tâm báo cáo</span>
            </a>
            <a href="{{ route('reports.bc05') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('reports.bc05') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">trending_up</span>
                <span>Dự báo nhu cầu</span>
            </a>
            <a href="{{ route('reports.bc04') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('reports.bc04') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">warning</span>
                <span>Rủi ro tồn & chậm luân chuyển</span>
            </a>
            <a href="{{ route('reports.olap') }}"
               class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('reports.olap') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                <span class="material-symbols-outlined text-[20px]">insights</span>
                <span>Phân tích kho đa chiều</span>
            </a>
        </div>

        {{-- Nhóm 6: Quản trị hệ thống (Chỉ Admin) --}}
        @if(auth()->user()->role === 'admin')
            <div class="space-y-1">
                <div class="px-3 py-1 font-label-sm text-[11px] text-on-surface-variant tracking-wider uppercase font-semibold">Quản trị hệ thống</div>
                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('users.*') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                    <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                    <span>Quản lý người dùng</span>
                </a>
                <a href="{{ route('audit.index') }}"
                   class="flex items-center gap-3 px-3 py-2 transition-colors {{ request()->routeIs('audit.*') ? 'bg-surface-container-low text-on-surface font-label-lg font-bold rounded-xl' : 'rounded-xl text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-body-md text-body-md' }}">
                    <span class="material-symbols-outlined text-[20px]">security</span>
                    <span>Nhật ký hoạt động</span>
                </a>
            </div>
        @endif
    </nav>

    {{-- Phần c: Thẻ User ở đáy --}}
    <div class="shrink-0 p-3 border-t border-surface-container-high/60 bg-white">
        <details class="group relative">
            <summary class="list-none cursor-pointer flex items-center gap-3 p-2 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors">
                <div class="w-9 h-9 rounded-full bg-[#1A4D38] flex items-center justify-center shrink-0 text-white font-bold text-xs">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex flex-col min-w-0 flex-1 text-left">
                    <span class="font-label-lg text-label-lg text-on-surface truncate font-semibold">{{ auth()->user()->name }}</span>
                    <span class="font-label-sm text-[11px] text-on-surface-variant truncate">{{ auth()->user()->role === 'admin' ? 'Quản trị viên kho' : 'Thủ kho lưu trữ' }}</span>
                </div>
                <span class="material-symbols-outlined text-[20px] text-on-surface-variant group-open:rotate-180 transition-transform">expand_less</span>
            </summary>

            {{-- Dropdown đăng xuất --}}
            <div class="absolute bottom-full mb-2 left-0 right-0 bg-surface-container-lowest rounded-xl shadow-lg border border-surface-container-high p-2 z-50">
                <div class="px-3 py-2 border-b border-surface-container-high/60 text-xs">
                    <p class="font-bold text-on-surface">{{ auth()->user()->name }}</p>
                    <p class="text-on-surface-variant truncate">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-error hover:bg-error-container/20 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </details>
    </div>
</aside>

{{-- ========================================================================= --}}
{{-- KHUNG NỘI DUNG CHÍNH (Đẩy lề trái pl-64 cho Sidebar)                     --}}
{{-- ========================================================================= --}}
<div class="pl-64 flex flex-col min-h-screen min-w-0">
    {{-- Header trên cùng đồng bộ chiều cao h-[var(--topbar-h)] (80px) --}}
    <header class="fixed top-0 left-64 right-0 z-40 h-[var(--topbar-h)] bg-surface-container-lowest/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container-high/60 flex items-center">
        <div class="w-full h-full px-space-lg flex items-center justify-between gap-4">
            {{-- Ô tìm kiếm & Breadcrumbs căn giữa dọc tâm header --}}
            <div class="flex items-center gap-3 flex-1 max-w-2xl">
                <button type="button" class="icon-button sidebar-toggle" onclick="toggleMenu()" title="Mở menu">
                    <span class="material-symbols-outlined text-[22px]">menu</span>
                </button>
                <div class="hidden xl:flex items-center topbar-breadcrumbs whitespace-nowrap shrink-0 pr-2">
                    <a href="{{ route('dashboard') }}">Kho</a>
                    @yield('breadcrumbs')
                </div>
                <x-ui.search-input id="topbar-search-input"
                                   placeholder="Tìm kiếm sản phẩm, số phiếu, mã SKU..."
                                   class="w-full" />
            </div>

            {{-- Các nút trạng thái góc phải header --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container-low text-on-surface-variant text-xs font-medium">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">schedule</span>
                    <span id="live-clock">{{ now()->format('H:i:s') }}</span>
                </div>
                <a href="{{ route('reports.index') }}" class="w-10 h-10 rounded-xl flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-colors" title="Trợ giúp & Tài liệu">
                    <span class="material-symbols-outlined text-[22px]">help_outline</span>
                </a>

                {{-- Nút chuông thông báo có dropdown tương tác --}}
                <div class="relative" id="notification-wrapper">
                    <button type="button" id="notification-btn" aria-expanded="false" onclick="toggleNotificationDropdown()"
                            class="relative w-10 h-10 rounded-xl flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-colors"
                            title="Thông báo" aria-label="Xem thông báo">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error"></span>
                    </button>

                    {{-- Khung dropdown thông báo --}}
                    <div id="notification-dropdown"
                         class="hidden absolute right-0 top-full mt-2 w-80 sm:w-96 rounded-2xl bg-white border border-surface-container-high/60 shadow-[0_12px_36px_-4px_rgba(17,24,39,0.12),0_4px_12px_-2px_rgba(17,24,39,0.04)] z-50 overflow-hidden">
                        <div class="p-4 border-b border-surface-container-high/60 flex items-center justify-between bg-surface-container-lowest">
                            <div class="flex items-center gap-2">
                                <h3 class="font-headline-sm text-sm font-bold text-on-surface">Thông báo hệ thống</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-error-container text-on-error-container">Cảnh báo</span>
                            </div>
                            <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                                Xem tồn kho
                            </a>
                        </div>

                        <div class="max-h-96 overflow-y-auto divide-y divide-surface-container-high/40 text-xs">
                            <a href="{{ route('inventory.index', ['stock' => 'attention']) }}" class="p-3.5 flex items-start gap-3 hover:bg-surface-container-low/50 transition-colors block">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[17px]">warning</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-on-surface truncate">Cảnh báo tồn kho an toàn</h4>
                                        <span class="w-2 h-2 rounded-full bg-error shrink-0"></span>
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-0.5">Mặt hàng chạm hoặc dưới ngưỡng an toàn (ROP) cần bổ sung.</p>
                                </div>
                            </a>

                            <a href="{{ route('dss.index') }}" class="p-3.5 flex items-start gap-3 hover:bg-surface-container-low/50 transition-colors block">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[17px]">assignment_turned_in</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-on-surface truncate">Đề xuất bổ sung hàng (DSS)</h4>
                                        <span class="w-2 h-2 rounded-full bg-error shrink-0"></span>
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-0.5">Kiểm tra và duyệt các đề xuất đặt hàng mới.</p>
                                </div>
                            </a>

                            <a href="{{ route('inventory.index') }}" class="p-3.5 flex items-start gap-3 hover:bg-surface-container-low/50 transition-colors block">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[17px]">warehouse</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-on-surface truncate">Tra cứu kho hàng</h4>
                                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-0.5">Theo dõi số lượng hàng thực tế và hàng đang trên đường về.</p>
                                </div>
                            </a>
                        </div>

                        <div class="p-3 border-t border-surface-container-high/60 bg-surface-container-low/40 text-center">
                            <a href="{{ route('reports.bc01') }}" class="text-xs font-bold text-on-surface hover:text-[#1A4D38] inline-flex items-center gap-1 transition-colors">
                                <span>Xem báo cáo cảnh báo chi tiết</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- User avatar --}}
                <div class="flex items-center gap-2">
                    <span class="sr-only">Hi, {{ auth()->user()->name }}</span>
                    <div class="w-9 h-9 rounded-full bg-[#1A4D38] flex items-center justify-center text-white font-bold text-xs shadow-sm">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Wrapper main dùng pt-[calc(var(--topbar-h)+1.5rem)] (80px header + 24px = 104px) --}}
    <main class="w-full min-w-0 pt-[calc(var(--topbar-h)+1.5rem)] bg-[#F1F3F5] min-h-screen px-space-lg pb-space-lg flex-1">
        {{-- Flash Messages dùng x-ui.flash-alert --}}
        @if(session('success'))
            <div class="mb-4">
                <x-ui.flash-alert type="success" :message="session('success')" />
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4">
                <x-ui.flash-alert type="error" :message="session('error')" />
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="mb-4">
                <x-ui.flash-alert type="error">
                    <div class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</div>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.flash-alert>
            </div>
        @endif

        {{-- Nạp nội dung view con --}}
        @yield('content')
    </main>
</div>

{{-- ========================================================================= --}}
{{-- JAVASCRIPT HỆ THỐNG                                                       --}}
{{-- ========================================================================= --}}
<script>
    function toggleMenu() {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            backdrop?.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop?.classList.add('hidden');
        }
    }

    function toggleNotificationDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        const btn = document.getElementById('notification-btn');
        if (!dropdown || !btn) return;

        const isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            dropdown.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        }
    }

    const liveClock = document.getElementById('live-clock');
    if (liveClock) {
        const updateClock = () => {
            const currentTime = new Date();
            liveClock.textContent = new Intl.DateTimeFormat('vi-VN', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(currentTime);
        };
        updateClock();
        window.setInterval(updateClock, 1000);
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('details')) {
            document.querySelectorAll('details[open]').forEach(d => d.removeAttribute('open'));
        }
        if (!event.target.closest('#notification-wrapper')) {
            const dropdown = document.getElementById('notification-dropdown');
            const btn = document.getElementById('notification-btn');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const dropdown = document.getElementById('notification-dropdown');
            const btn = document.getElementById('notification-btn');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
            }
            document.querySelectorAll('details[open]').forEach(d => d.removeAttribute('open'));
        }
    });
</script>

{{-- Nạp Javascript dùng chung và Floating Container --}}
@include('layouts.partials.scripts')

@stack('scripts')
</body>
</html>
