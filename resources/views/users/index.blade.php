@extends('layouts.app')

@section('title', 'Tài khoản')
@section('page-title', 'Tài khoản người dùng')
@section('page-subtitle', 'Quản lý quyền truy cập và vai trò trong hệ thống.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>Nhân sự hệ thống</h2>
            <p>{{ $users->count() }} tài khoản</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn">＋ Tạo tài khoản</a>
    </div>

    <section class="content">
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Người dùng</th><th>Email</th><th>Vai trò</th><th>Ngày tạo</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                @forelse($users as $account)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <span class="avatar">{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}</span>
                                <span><span class="product-title">{{ $account->name }}</span>
                                    <span class="product-meta">ID #{{ $account->id }}</span></span>
                            </div>
                        </td>
                        <td>{{ $account->email }} @if(!$account->is_active)<span class="badge badge-danger">Đã khóa</span>@endif</td>
                        <td><span class="badge {{ $account->role === 'admin' ? 'badge-info' : 'badge-muted' }}">
                            {{ $account->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}
                        </span></td>
                        <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($account->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('users.edit', $account->id) }}" class="btn btn-secondary btn-sm">Sửa</a>
                                <form action="{{ route('users.destroy', $account->id) }}" method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="5">Chưa có tài khoản nào.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
