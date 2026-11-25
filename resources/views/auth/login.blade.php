<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | N07 Inventory Management</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
<main class="auth-page">
    <section class="auth-art">
        <a class="brand" href="{{ route('login') }}">
            <span class="brand-mark">N7</span>
            <span><span class="brand-name">N07 INVENTORY</span><span class="brand-caption">MANAGEMENT SYSTEM</span></span>
        </a>

        <div>
            <div class="eyebrow" style="color:#a7e4bf">IS402 · Decision support</div>
            <h1>Quản lý tồn kho thông minh hơn mỗi ngày.</h1>
            <p>Theo dõi hàng hóa, hiểu nhu cầu và đưa ra quyết định nhập hàng dựa trên dữ liệu thực tế.</p>
            <div class="auth-highlights">
                <div class="auth-highlight"><span>✓</span> Nhập xuất kho rõ ràng, có lịch sử</div>
                <div class="auth-highlight"><span>✓</span> Cảnh báo tồn kho và đề xuất nhập hàng</div>
                <div class="auth-highlight"><span>✓</span> Báo cáo trực quan cho cửa hàng</div>
            </div>
        </div>
        <div class="auth-art-foot">N07 DSS · Hệ hỗ trợ ra quyết định nhập hàng</div>
    </section>

    <section class="auth-main">
        <div class="auth-card">
            <div class="auth-brand"><span class="brand-mark">N7</span> N07 INVENTORY MANAGEMENT</div>
            <h2>Chào mừng trở lại</h2>
            <p>Đăng nhập bằng tài khoản được cấp để tiếp tục làm việc.</p>

            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf
                <div class="form-field" style="margin-bottom:15px">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           autocomplete="username" placeholder="ten@cuahang.vn" required autofocus>
                </div>
                <div class="form-field">
                    <label for="password">Mật khẩu</label>
                    <input id="password" type="password" name="password" autocomplete="current-password"
                           placeholder="Nhập mật khẩu" required>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:14px;color:var(--muted);font-size:11px">
                    <input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập
                </label>
                <button type="submit" class="btn auth-submit">Đăng nhập vào hệ thống <span>→</span></button>
            </form>
            <div class="auth-foot">Tài khoản do quản trị viên tạo và cấp quyền.</div>
        </div>
    </section>
</main>
</body>
</html>
