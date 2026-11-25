<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản lý kho & Hỗ trợ nhập hàng') | N07 DSS</title>
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
    <symbol id="icon-audit" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></symbol>
</svg>

<div class="app-shell">
    <aside class="sidebar" id="app-sidebar" aria-label="Điều hướng hệ thống">
        <a class="brand" href="{{ route('dashboard') }}" title="N07 Inventory System">
            <span class="brand-mark">N7</span>
            <span class="brand-copy">
                <span class="brand-name">N07 WAREHOUSE</span>
                <span class="brand-caption">REPLENISHMENT DSS</span>
            </span>
        </a>

        <nav class="sidebar-nav" aria-label="Điều hướng chính">
            <!-- 1. Tổng quan -->
            <div class="nav-group">
                <div class="nav-label">Tổng quan</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <svg class="nav-icon"><use href="#icon-dashboard"/></svg>
                    <span class="nav-text">Tổng quan kho</span>
                </a>
            </div>

            <!-- 2. Quản lý hàng hóa -->
            <div class="nav-group">
                <div class="nav-label">Quản lý hàng hóa</div>
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <svg class="nav-icon"><use href="#icon-product"/></svg>
                    <span class="nav-text">Danh sách sản phẩm</span>
                </a>
                @if(auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                        <svg class="nav-icon"><use href="#icon-category"/></svg>
                        <span class="nav-text">Danh mục hàng hóa</span>
                    </a>
                @endif
            </div>

            <!-- 3. Nghiệp vụ kho -->
            <div class="nav-group">
                <div class="nav-label">Nghiệp vụ kho</div>
                <a class="nav-link {{ request()->routeIs('inventory.create') && request()->route('type') === 'import' ? 'active' : '' }}" href="{{ route('inventory.create', 'import') }}">
                    <svg class="nav-icon"><use href="#icon-import"/></svg>
                    <span class="nav-text">Phiếu nhập kho</span>
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.create') && request()->route('type') === 'export' ? 'active' : '' }}" href="{{ route('inventory.create', 'export') }}">
                    <svg class="nav-icon"><use href="#icon-export"/></svg>
                    <span class="nav-text">Phiếu xuất kho</span>
                </a>
                <a class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <svg class="nav-icon"><use href="#icon-stock"/></svg>
                    <span class="nav-text">Tra cứu tồn kho</span>
                </a>
            </div>

            <!-- 4. Hỗ trợ nhập hàng (DSS) -->
            <div class="nav-group">
                <div class="nav-label">Hỗ trợ nhập hàng</div>
                <a class="nav-link {{ request()->routeIs('dss.index') ? 'active' : '' }}" href="{{ route('dss.index') }}">
                    <svg class="nav-icon"><use href="#icon-reorder"/></svg>
                    <span class="nav-text">Đề xuất bổ sung hàng</span>
                </a>
                <a class="nav-link {{ request()->routeIs('dss.history') ? 'active' : '' }}" href="{{ route('dss.history') }}">
                    <svg class="nav-icon"><use href="#icon-history"/></svg>
                    <span class="nav-text">Lịch sử đặt & nhận hàng</span>
                </a>
            </div>

            <!-- 5. Báo cáo & Phân tích -->
            <div class="nav-group">
                <div class="nav-label">Báo cáo & Phân tích</div>
                <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <svg class="nav-icon"><use href="#icon-report"/></svg>
                    <span class="nav-text">Trung tâm báo cáo</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc05') ? 'active' : '' }}" href="{{ route('reports.bc05') }}">
                    <svg class="nav-icon"><use href="#icon-chart"/></svg>
                    <span class="nav-text">Dự báo nhu cầu</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.bc04') ? 'active' : '' }}" href="{{ route('reports.bc04') }}">
                    <svg class="nav-icon"><use href="#icon-alert"/></svg>
                    <span class="nav-text">Rủi ro tồn & chậm luân chuyển</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.olap') ? 'active' : '' }}" href="{{ route('reports.olap') }}">
                    <svg class="nav-icon"><use href="#icon-chart"/></svg>
                    <span class="nav-text">Phân tích kho đa chiều</span>
                </a>
            </div>

            <!-- 6. Quản trị hệ thống (Admin) -->
            @if(auth()->user()->role === 'admin')
                <div class="nav-group">
                    <div class="nav-label">Quản trị hệ thống</div>
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <svg class="nav-icon"><use href="#icon-users"/></svg>
                        <span class="nav-text">Quản lý người dùng</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                        <svg class="nav-icon"><use href="#icon-audit"/></svg>
                        <span class="nav-text">Nhật ký hoạt động</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="sidebar-foot">
            <div class="user-chip">
                <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="user-chip-copy">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Quản trị viên (Admin)' : 'Nhân viên kho (Staff)' }}</span>
                </div>
            </div>
        </div>
    </aside>

    <div class="main-shell">
        <header class="topbar">
            <div class="topbar-start">
                <button class="icon-button sidebar-toggle" type="button" aria-label="Đóng/mở menu" onclick="toggleMenu()">
                    <svg class="ui-icon"><use href="#icon-menu"/></svg>
                </button>
                <span class="visually-hidden">Hi, {{ auth()->user()->name }}</span>
                <div class="topbar-breadcrumbs">
                    <a href="{{ route('dashboard') }}">Kho</a>
                    @yield('breadcrumbs')
                </div>
            </div>

            <div class="topbar-actions">
                <time class="topbar-clock" id="live-clock" datetime="{{ now()->toIso8601String() }}">{{ now()->format('H:i:s') }}</time>
                <a class="notification-link" href="{{ route('inventory.index', ['stock' => 'attention']) }}" title="Mặt hàng cần chú ý">
                    <svg class="ui-icon"><use href="#icon-bell"/></svg>
                </a>
                <details class="account-menu">
                    <summary>
                        <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="account-copy">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Staff' }}</span>
                        </div>
                    </summary>
                    <div class="account-dropdown">
                        <div class="account-dropdown-user">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                            <span class="badge badge-info">{{ auth()->user()->role === 'admin' ? 'Quản trị viên' : 'Nhân viên kho' }}</span>
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
                <div class="alert alert-success" role="status">
                    <span>✓</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <span>!</span>
                    <div>{{ session('error') }}</div>
                </div>
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
        if (window.matchMedia('(max-width: 1024px)').matches) {
            document.body.classList.toggle('nav-open');
            return;
        }
        const collapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('n07-sidebar-collapsed', String(collapsed));
    }

    if (!window.matchMedia('(max-width: 1024px)').matches && localStorage.getItem('n07-sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    const liveClock = document.getElementById('live-clock');
    if (liveClock) {
        const updateClock = () => {
            const now = new Date();
            liveClock.dateTime = now.toISOString();
            liveClock.textContent = new Intl.DateTimeFormat('vi-VN', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            }).format(now);
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    document.addEventListener('click', function (e) {
        if (document.body.classList.contains('nav-open') && !e.target.closest('#app-sidebar') && !e.target.closest('.sidebar-toggle')) {
            document.body.classList.remove('nav-open');
        }
        if (!e.target.closest('.account-menu')) {
            document.querySelector('.account-menu')?.removeAttribute('open');
        }
    });
</script>
@stack('scripts')
</body>
</html>
