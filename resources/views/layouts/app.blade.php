<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'N07 Inventory Management') | N07 Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body>
<svg class="icon-library" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
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
</svg>

<div class="app-shell">
    <aside class="sidebar" id="app-sidebar" aria-label="Điều hướng hệ thống">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="N07 Inventory Management - Tổng quan">
            <span class="brand-mark">N7</span>
            <span class="brand-copy">
                <span class="brand-name">N07 INVENTORY</span>
                <span class="brand-caption">MANAGEMENT SYSTEM</span>
            </span>
        </a>

        <nav class="sidebar-nav" aria-label="Điều hướng chính">
            <div class="nav-group">
                <div class="nav-label">Tổng quan</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                    <svg class="nav-icon"><use href="#icon-dashboard"/></svg><span class="nav-text">Dashboard</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Quản lý hàng hóa</div>
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}" title="Danh sách sản phẩm">
                    <svg class="nav-icon"><use href="#icon-product"/></svg><span class="nav-text">Danh sách sản phẩm</span>
                </a>
                @if(auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}" title="Danh mục hàng hóa">
                        <svg class="nav-icon"><use href="#icon-category"/></svg><span class="nav-text">Danh mục hàng hóa</span>
                    </a>
                @endif
            </div>

            <div class="nav-group">
                <div class="nav-label">Quản lý kho</div>
                <a class="nav-link {{ request()->routeIs('inventory.create') && request()->route('type') === 'import' ? 'active' : '' }}" href="{{ route('inventory.create', 'import') }}" title="Nhập kho">
                    <svg class="nav-icon"><use href="#icon-import"/></svg><span class="nav-text">Nhập kho</span>
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.create') && request()->route('type') === 'export' ? 'active' : '' }}" href="{{ route('inventory.create', 'export') }}" title="Xuất kho">
                    <svg class="nav-icon"><use href="#icon-export"/></svg><span class="nav-text">Xuất kho</span>
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.index') && !request('stock') ? 'active' : '' }}" href="{{ route('inventory.index') }}" title="Tồn kho hiện tại">
                    <svg class="nav-icon"><use href="#icon-stock"/></svg><span class="nav-text">Tồn kho hiện tại</span>
                </a>
                <a class="nav-link" href="{{ route('inventory.index') }}#recent-transactions" title="Lịch sử nhập xuất">
                    <svg class="nav-icon"><use href="#icon-history"/></svg><span class="nav-text">Lịch sử nhập xuất</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Phân tích tồn kho</div>
                <a class="nav-link" href="{{ route('reports.bc05') }}" title="Nhu cầu và dự báo">
                    <svg class="nav-icon"><use href="#icon-chart"/></svg><span class="nav-text">Nhu cầu & dự báo</span>
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.index') && request('stock') === 'attention' ? 'active' : '' }}" href="{{ route('inventory.index', ['stock' => 'attention']) }}" title="Cảnh báo tồn kho">
                    <svg class="nav-icon"><use href="#icon-alert"/></svg><span class="nav-text">Cảnh báo tồn kho</span>
                </a>
                <a class="nav-link {{ request()->routeIs('dss.*') ? 'active' : '' }}" href="{{ route('dss.index') }}" title="Đề xuất nhập hàng">
                    <svg class="nav-icon"><use href="#icon-reorder"/></svg><span class="nav-text">Đề xuất nhập hàng</span>
                </a>
                <a class="nav-link" href="{{ route('reports.bc04') }}" title="Hàng chậm luân chuyển">
                    <svg class="nav-icon"><use href="#icon-history"/></svg><span class="nav-text">Hàng chậm luân chuyển</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Báo cáo</div>
                <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}" title="Tất cả báo cáo">
                    <svg class="nav-icon"><use href="#icon-report"/></svg><span class="nav-text">Tất cả báo cáo</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc01') ? 'active' : '' }}" href="{{ route('reports.bc01') }}" title="Nhập - xuất - tồn">
                    <svg class="nav-icon"><use href="#icon-report"/></svg><span class="nav-text">Nhập - xuất - tồn</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc02') ? 'active' : '' }}" href="{{ route('reports.bc02') }}" title="Hàng cần nhập">
                    <svg class="nav-icon"><use href="#icon-reorder"/></svg><span class="nav-text">Hàng cần nhập</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc03') ? 'active' : '' }}" href="{{ route('reports.bc03') }}" title="Hàng bán chạy">
                    <svg class="nav-icon"><use href="#icon-chart"/></svg><span class="nav-text">Hàng bán chạy</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc04') ? 'active' : '' }}" href="{{ route('reports.bc04') }}" title="Hàng tồn dư">
                    <svg class="nav-icon"><use href="#icon-stock"/></svg><span class="nav-text">Hàng tồn dư</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc05') ? 'active' : '' }}" href="{{ route('reports.bc05') }}" title="Đánh giá dự báo">
                    <svg class="nav-icon"><use href="#icon-chart"/></svg><span class="nav-text">Đánh giá dự báo</span>
                </a>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="nav-group">
                    <div class="nav-label">Hệ thống</div>
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Quản lý tài khoản">
                        <svg class="nav-icon"><use href="#icon-users"/></svg><span class="nav-text">Quản lý tài khoản</span>
                    </a>
                    <a class="nav-link" href="{{ route('dss.index') }}#calculation-settings" title="Thông số kho">
                        <svg class="nav-icon"><use href="#icon-settings"/></svg><span class="nav-text">Thông số kho</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="sidebar-foot">
            <div class="user-chip">
                <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="user-chip-copy">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span>
                </span>
            </div>
        </div>
    </aside>

    <div class="main-shell">
        <header class="topbar">
            <div class="topbar-start">
                <button class="icon-button sidebar-toggle" type="button" aria-label="Thu gọn menu" aria-controls="app-sidebar" aria-expanded="true" onclick="toggleMenu()">
                    <svg class="ui-icon"><use href="#icon-menu"/></svg>
                </button>
                <div class="topbar-copy">
                    <h1>Hi, {{ auth()->user()->name }}</h1>
                    <time class="topbar-clock" id="live-clock" datetime="{{ now()->toIso8601String() }}">{{ now()->format('H:i:s') }}</time>
                </div>
            </div>
            <div class="topbar-actions">
                <a class="icon-button notification-link" href="{{ route('inventory.index', ['stock' => 'attention']) }}" aria-label="Xem cảnh báo tồn kho" title="Cảnh báo tồn kho">
                    <svg class="ui-icon"><use href="#icon-bell"/></svg>
                </a>
                <details class="account-menu">
                    <summary>
                        <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="account-copy">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Staff' }}</span>
                        </span>
                    </summary>
                    <div class="account-dropdown">
                        <div class="account-dropdown-user">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                            <span class="badge badge-info">{{ auth()->user()->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="account-logout">Đăng xuất</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success" role="status"><span>✓</span><div>{{ session('success') }}</div></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert"><span>!</span><div>{{ session('error') }}</div></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <span>!</span>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script>
    function toggleMenu() {
        const button = document.querySelector('.sidebar-toggle');

        if (window.matchMedia('(max-width: 800px)').matches) {
            const isOpen = document.body.classList.toggle('nav-open');
            button?.setAttribute('aria-expanded', String(isOpen));
            button?.setAttribute('aria-label', isOpen ? 'Đóng menu' : 'Mở menu');
            return;
        }

        const collapsed = document.body.classList.toggle('sidebar-collapsed');
        button?.setAttribute('aria-expanded', String(!collapsed));
        button?.setAttribute('aria-label', collapsed ? 'Mở rộng menu' : 'Thu gọn menu');
        localStorage.setItem('n07-sidebar-collapsed', String(collapsed));
    }

    if (!window.matchMedia('(max-width: 800px)').matches
        && localStorage.getItem('n07-sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    const liveClock = document.getElementById('live-clock');
    if (liveClock) {
        const updateClock = () => {
            const currentTime = new Date();
            liveClock.dateTime = currentTime.toISOString();
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
        if (document.body.classList.contains('nav-open')
            && !event.target.closest('#app-sidebar')
            && !event.target.closest('.sidebar-toggle')) {
            document.body.classList.remove('nav-open');
            document.querySelector('.sidebar-toggle')?.setAttribute('aria-expanded', 'false');
            document.querySelector('.sidebar-toggle')?.setAttribute('aria-label', 'Mở menu');
        }
        if (!event.target.closest('.account-menu')) {
            document.querySelector('.account-menu')?.removeAttribute('open');
        }
    });
</script>
@stack('scripts')
</body>
</html>
