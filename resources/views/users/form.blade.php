@extends('layouts.app')

@section('title', $account ? 'Sửa tài khoản' : 'Tạo tài khoản')
@section('page-title', $account ? 'Chỉnh sửa tài khoản' : 'Tạo tài khoản')
@section('page-subtitle', 'Cấp quyền phù hợp cho từng người sử dụng hệ thống.')

@section('content')
    <div class="page-heading">
        <div><h2>{{ $account ? 'Thông tin người dùng' : 'Tài khoản mới' }}</h2></div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">← Danh sách tài khoản</a>
    </div>

    <section class="content" style="max-width:780px">
        <form action="{{ $account ? route('users.update', $account->id) : route('users.store') }}" method="POST">
            @csrf
            @if($account) @method('PUT') @endif

            <div class="form-grid">
                <div class="form-field">
                    <label for="name">Họ và tên</label>
                    <input id="name" name="name" required value="{{ old('name', $account->name ?? '') }}"
                           placeholder="Tên nhân viên">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="email">Email đăng nhập</label>
                    <input id="email" type="email" name="email" required
                           value="{{ old('email', $account->email ?? '') }}"
                           placeholder="ten@cuahang.vn">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="is_active">Trạng thái</label>
<select id="is_active" name="is_active"><option value="1" @selected(old('is_active', $account->is_active ?? 1) == 1)>Hoạt động</option><option value="0" @selected(old('is_active', $account->is_active ?? 1) == 0)>Đã khóa</option></select>
<label for="role">Vai trò</label>
                    <select id="role" name="role" required>
                        <option value="staff" {{ old('role', $account->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                        <option value="admin" {{ old('role', $account->role ?? '') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    </select>
                    @error('role')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password">{{ $account ? 'Mật khẩu mới (không bắt buộc)' : 'Mật khẩu' }}</label>
                    <input id="password" type="password" name="password" autocomplete="new-password"
                           {{ $account ? '' : 'required' }}>
                    <p class="form-help">{{ $account ? 'Để trống để giữ nguyên mật khẩu hiện tại.' : 'Tối thiểu 8 ký tự.' }}</p>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field form-field-full">
                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           autocomplete="new-password" {{ $account ? '' : 'required' }}>
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px">
                <button type="submit" class="btn">{{ $account ? 'Lưu thay đổi' : 'Tạo tài khoản' }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </section>
@endsection
