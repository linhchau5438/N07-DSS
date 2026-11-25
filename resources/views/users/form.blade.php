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
<div class="space-y-6 max-w-4xl">

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

    {{-- KHUNG 2: FORM NHẬP LIỆU DÙNG x-ui.section-card --}}
    <x-ui.section-card spacing="space-y-6">
        <form action="{{ $account ? route('users.update', $account->id) : route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($account) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Họ và tên --}}
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        Họ và tên nhân sự <span class="text-error">*</span>
                    </label>
                    <input id="name" name="name" required value="{{ old('name', $account->name ?? '') }}"
                           placeholder="Ví dụ: Nguyễn Văn A"
                           class="w-full h-11 px-3.5 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition-all">
                    @error('name')<p class="text-[11px] text-error font-medium">{{ $message }}</p>@enderror
                </div>

                {{-- Email đăng nhập --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        Email đăng nhập <span class="text-error">*</span>
                    </label>
                    <input id="email" type="email" name="email" required
                           value="{{ old('email', $account->email ?? '') }}"
                           placeholder="ten@cuahang.vn"
                           class="w-full h-11 px-3.5 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition-all">
                    @error('email')<p class="text-[11px] text-error font-medium">{{ $message }}</p>@enderror
                </div>

                {{-- Vai trò --}}
                <div class="space-y-1.5">
                    <label for="role" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        Vai trò phân quyền <span class="text-error">*</span>
                    </label>
                    <div class="relative">
                        <select id="role" name="role" required
                                class="w-full h-11 pl-3.5 pr-9 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary appearance-none transition-all">
                            <option value="staff" {{ old('role', $account->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Nhân viên kho (Staff)</option>
                            <option value="admin" {{ old('role', $account->role ?? '') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                    @error('role')<p class="text-[11px] text-error font-medium">{{ $message }}</p>@enderror
                </div>

                {{-- Trạng thái --}}
                <div class="space-y-1.5">
                    <label for="is_active" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        Trạng thái hoạt động
                    </label>
                    <div class="relative">
                        <select id="is_active" name="is_active"
                                class="w-full h-11 pl-3.5 pr-9 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary appearance-none transition-all">
                            <option value="1" @selected(old('is_active', $account->is_active ?? 1) == 1)>Đang hoạt động (Cho phép đăng nhập)</option>
                            <option value="0" @selected(old('is_active', $account->is_active ?? 1) == 0)>Đã khóa (Chặn đăng nhập)</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>

                {{-- Mật khẩu --}}
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        {{ $account ? 'Mật khẩu mới (bỏ trống nếu giữ nguyên)' : 'Mật khẩu truy cập' }}
                        @if(!$account) <span class="text-error">*</span> @endif
                    </label>
                    <input id="password" type="password" name="password" autocomplete="new-password"
                           {{ $account ? '' : 'required' }}
                           placeholder="{{ $account ? '••••••••' : 'Tối thiểu 8 ký tự' }}"
                           class="w-full h-11 px-3.5 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition-all">
                    @error('password')<p class="text-[11px] text-error font-medium">{{ $message }}</p>@enderror
                </div>

                {{-- Xác nhận mật khẩu --}}
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-on-surface uppercase tracking-wider font-mono">
                        Xác nhận mật khẩu @if(!$account) <span class="text-error">*</span> @endif
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           autocomplete="new-password" {{ $account ? '' : 'required' }}
                           placeholder="Nhập lại mật khẩu"
                           class="w-full h-11 px-3.5 rounded-xl bg-surface-container-low border border-surface-container-high text-xs text-on-surface focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition-all">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-surface-container-high/60">
                <button type="submit"
                        class="h-11 px-6 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-bold inline-flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[18px] text-white">save</span>
                    <span class="text-white">{{ $account ? 'Lưu thay đổi' : 'Tạo tài khoản' }}</span>
                </button>
                <a href="{{ route('users.index') }}"
                   class="h-11 px-5 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors text-xs font-semibold inline-flex items-center border border-surface-container-high/60">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </x-ui.section-card>

</div>
@endsection
