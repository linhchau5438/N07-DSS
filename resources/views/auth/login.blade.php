<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống | N07 WAREHOUSE DSS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            background: var(--bg-app);
        }
        .login-visual {
            background: linear-gradient(145deg, #0b1523 0%, #13273e 50%, #0d3835 100%);
            padding: 48px 64px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-visual::before {
            content: "";
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, transparent 70%);
        }
        .login-visual::after {
            content: "";
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%);
        }
        .visual-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }
        .visual-body {
            max-width: 520px;
            position: relative;
            z-index: 2;
        }
        .visual-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #2dd4bf;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
        }
        .visual-title {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.03em;
            color: #fff;
            margin-bottom: 16px;
        }
        .visual-desc {
            font-size: 14px;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 32px;
        }
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: #cbd5e1;
        }
        .feature-check {
            width: 20px;
            height: 20px;
            border-radius: var(--radius-full);
            background: rgba(45, 212, 191, 0.15);
            color: #2dd4bf;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .visual-footer {
            font-size: 11px;
            color: #64748b;
            position: relative;
            z-index: 2;
        }
        .login-form-pane {
            display: grid;
            place-items: center;
            padding: 40px 24px;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 36px 32px;
            box-shadow: var(--shadow-md);
        }
        .login-card h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }
        .login-card p.subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin: 4px 0 24px;
        }
        .btn-submit {
            width: 100%;
            height: 42px;
            font-size: 13px;
            margin-top: 8px;
        }
        @media (max-width: 900px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }
            .login-visual {
                display: none;
            }
        }
    </style>
</head>

<body>
<div class="login-wrapper">
    <section class="login-visual">
        <a class="visual-brand" href="{{ route('login') }}">
            <span class="brand-mark">N7</span>
            <div>
                <strong style="display:block;font-size:14px;letter-spacing:0.04em">N07 WAREHOUSE</strong>
                <span style="font-size:10px;color:#94a3b8;letter-spacing:0.08em">REPLENISHMENT DSS</span>
            </div>
        </a>

        <div class="visual-body">
            <span class="visual-eyebrow">Quản lý kho & Hỗ trợ ra quyết định</span>
            <h1 class="visual-title">Kiểm soát tồn kho chính xác. Đặt hàng đúng thời điểm.</h1>
            <p class="visual-desc">
                Hệ thống chuyên nghiệp kết hợp giữa quản lý nhập - xuất - tồn thực tế và mô hình hỗ trợ ra quyết định nhập hàng (DSS) dựa trên dữ liệu tiêu thụ.
            </p>
            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-check">✓</span>
                    <div>
                        <strong>Theo dõi tồn khả dụng & hàng đang về (On Order)</strong>
                        <div style="font-size:11.5px;color:#94a3b8">Phân biệt rõ ràng lượng tồn thực tế trong kho và các đơn hàng nhà cung cấp đang giao.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-check">✓</span>
                    <div>
                        <strong>Tự động tính ngưỡng đặt hàng (ROP) & lượng đề xuất (Q)</strong>
                        <div style="font-size:11.5px;color:#94a3b8">Bảo vệ hàng hóa không bị đứt gãy với tồn kho an toàn và phát hiện méo nhu cầu.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-check">✓</span>
                    <div>
                        <strong>Luồng phê duyệt & đặt hàng minh bạch</strong>
                        <div style="font-size:11.5px;color:#94a3b8">Phân quyền chặt chẽ giữa Quản lý (Admin) và Nhân viên thực hiện (Staff).</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="visual-footer">
            Môn học IS402 · Decision Support System · Đồ án Nhóm N07
        </div>
    </section>

    <section class="login-form-pane">
        <div class="login-card">
            <h2>Đăng nhập</h2>
            <p class="subtitle">Nhập tài khoản được cấp quyền để truy cập hệ thống.</p>

            @if(session('success'))
                <div class="alert alert-success" role="status">
                    <span>✓</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <span>!</span>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf
                <div class="form-field" style="margin-bottom: 16px;">
                    <label for="email">Tài khoản Email *</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           autocomplete="username" placeholder="admin@example.com hoặc staff..." required autofocus>
                </div>

                <div class="form-field" style="margin-bottom: 16px;">
                    <label for="password">Mật khẩu *</label>
                    <input id="password" type="password" name="password" autocomplete="current-password"
                           placeholder="Nhập mật khẩu" required>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;font-size:11.5px;color:var(--text-muted)">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập
                    </label>
                    <span style="font-size:10.5px;color:var(--text-subtle)">Tài khoản nội bộ</span>
                </div>

                <button type="submit" class="btn btn-submit">
                    Đăng nhập hệ thống <span>→</span>
                </button>
            </form>

            <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border-light);font-size:11px;color:var(--text-muted);text-align:center">
                Cần cấp tài khoản mới hoặc đặt lại mật khẩu? Liên hệ Quản trị viên (Admin).
            </div>
        </div>
    </section>
</div>
</body>
</html>
