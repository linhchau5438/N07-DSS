{{--
    Đường dẫn: resources/views/users/form.blade.php
    Mô tả: Form Thêm / Sửa tài khoản nhân sự hệ thống theo Monochrome Design System, đồng bộ với /dashboard và /products.
    Phụ thuộc: UserController, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', $account ? 'Sửa tài khoản' : 'Tạo tài khoản')
@section('page-title', $account ? 'Chỉnh sửa tài khoản' : 'Tạo tài khoản')
@section('page-subtitle', 'Cấp quyền phù hợp cho từng người sử dụng hệ thống.')

@section('content')
<div class="space-y-6 w-full pb-12">

    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header --}}
    <x-ui.page-header :title="$account ? 'Chỉnh sửa tài khoản: ' . $account->name : 'Thêm tài khoản nhân sự mới'"
                      :chip="$account ? 'ID #' . $account->id : 'Tài khoản mới'"
                      subtitle="Điền thông tin định danh, thiết lập vai trò phân quyền và mật khẩu bảo mật truy cập kho.">
        <x-slot:actions>
            <a href="{{ route('users.index') }}"
               class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/70 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Danh sách nhân sự</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- KHUNG 2: FORM NHẬP LIỆU DÙNG x-ui.section-card FULL WIDTH --}}
    <x-ui.section-card spacing="space-y-6">
        <form action="{{ $account ? route('users.update', $account->id) : route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($account) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Họ và tên --}}
                <x-ui.form-field name="name"
                                 id="name"
                                 label="Họ và tên nhân sự"
                                 :required="true"
                                 :value="$account->name ?? ''"
                                 placeholder="Ví dụ: Nguyễn Văn A" />

                {{-- Email đăng nhập --}}
                <x-ui.form-field name="email"
                                 id="email"
                                 type="email"
                                 label="Email đăng nhập"
                                 :required="true"
                                 :value="$account->email ?? ''"
                                 placeholder="ten@cuahang.vn" />

                {{-- Vai trò --}}
                <x-ui.form-select name="role"
                                  id="role"
                                  label="Vai trò phân quyền"
                                  :required="true">
                    <option value="staff" {{ old('role', $account->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Nhân viên kho (Staff)</option>
                    <option value="admin" {{ old('role', $account->role ?? '') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                </x-ui.form-select>

                {{-- Trạng thái --}}
                <x-ui.form-select name="is_active"
                                  id="is_active"
                                  label="Trạng thái hoạt động">
                    <option value="1" @selected(old('is_active', $account->is_active ?? 1) == 1)>Đang hoạt động (Cho phép đăng nhập)</option>
                    <option value="0" @selected(old('is_active', $account->is_active ?? 1) == 0)>Đã khóa (Chặn đăng nhập)</option>
                </x-ui.form-select>

                {{-- Mật khẩu --}}
                <x-ui.form-field name="password"
                                 id="password"
                                 type="password"
                                 :label="$account ? 'Mật khẩu mới (bỏ trống nếu giữ nguyên)' : 'Mật khẩu truy cập'"
                                 :required="!$account"
                                 :placeholder="$account ? '••••••••' : 'Tối thiểu 8 ký tự'"
                                 autocomplete="new-password" />

                {{-- Xác nhận mật khẩu --}}
                <x-ui.form-field name="password_confirmation"
                                 id="password_confirmation"
                                 type="password"
                                 label="Xác nhận mật khẩu"
                                 :required="!$account"
                                 placeholder="Nhập lại mật khẩu"
                                 autocomplete="new-password" />
            </div>

            {{-- Nút Lưu / Hủy căn phải --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-high/60">
                <x-ui.button variant="secondary" href="{{ route('users.index') }}">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    {{ $account ? 'Lưu thay đổi' : 'Tạo tài khoản' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.section-card>

</div>
@endsection
