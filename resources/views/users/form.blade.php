@extends('layouts.app')

@section('title', $account ? 'Chỉnh sửa tài khoản: ' . $account->name : 'Tạo tài khoản mới')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('users.index') }}">Người dùng</a>
    <span class="sep">/</span>
    <span class="current">{{ $account ? 'Chỉnh sửa' : 'Tạo mới' }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>{{ $account ? 'Chỉnh sửa tài khoản: ' . $account->name : 'Tạo tài khoản người dùng mới' }}</h2>
        <p>Phân quyền truy cập chính xác: Admin quản lý toàn diện & phê duyệt DSS; Staff tập trung nhập/xuất kho.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">← Danh sách tài khoản</a>
</div>

<section class="card" style="max-width:720px">
    <form action="{{ $account ? route('users.update', $account->id) : route('users.store') }}" method="POST">
        @csrf
        @if($account) @method('PUT') @endif

        <div class="form-grid">
            <div class="form-field">
                <label for="name">Họ và tên *</label>
                <input id="name" name="name" required value="{{ old('name', $account->name ?? '') }}"
                       placeholder="Ví dụ: Nguyễn Văn An">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="email">Địa chỉ Email đăng nhập *</label>
                <input id="email" type="email" name="email" required
                       value="{{ old('email', $account->email ?? '') }}"
                       placeholder="ten@khohang.vn">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="role">Vai trò & Phân quyền *</label>
                <select id="role" name="role" required>
                    <option value="staff" {{ old('role', $account->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Nhân viên kho (Staff - Nhập/xuất, tra cứu)</option>
                    <option value="admin" {{ old('role', $account->role ?? '') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin - Toàn quyền & Duyệt DSS)</option>
                </select>
                @error('role')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="is_active">Trạng thái hoạt động</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected(old('is_active', $account->is_active ?? 1) == 1)>Đang hoạt động</option>
                    <option value="0" @selected(old('is_active', $account->is_active ?? 1) == 0)>Khóa tài khoản</option>
                </select>
            </div>

            <div class="form-field">
                <label for="password">{{ $account ? 'Mật khẩu mới (Để trống nếu không đổi)' : 'Mật khẩu đăng nhập *' }}</label>
                <input id="password" type="password" name="password" autocomplete="new-password"
                       {{ $account ? '' : 'required' }} placeholder="Tối thiểu 8 ký tự">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Xác nhận lại mật khẩu {{ $account ? '' : '*' }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       autocomplete="new-password" {{ $account ? '' : 'required' }} placeholder="Nhập lại mật khẩu trên">
            </div>
        </div>

        <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border-light);display:flex;gap:10px">
            <button type="submit" class="btn">
                {{ $account ? '✓ Lưu thay đổi' : '＋ Tạo tài khoản' }}
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                Hủy bỏ
            </a>
        </div>
    </form>
</section>
@endsection
