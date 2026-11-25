@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Người dùng & Phân quyền</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Quản lý người dùng & phân quyền hệ thống</h2>
        <p>Quản lý danh sách tài khoản, vai trò truy cập và trạng thái hoạt động của nhân sự kho.</p>
    </div>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('users.create') }}" class="btn">
            ＋ Tạo tài khoản mới
        </a>
    @endif
</div>

<div class="card" style="padding:0;overflow:hidden;max-width:1100px">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 class="card-title">Danh sách tài khoản nội bộ</h3>
            <div class="card-subtitle">Hiện có <strong>{{ $users->count() }}</strong> người dùng trong hệ thống</div>
        </div>
    </div>

    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th>Họ và tên</th>
                    <th>Email đăng nhập</th>
                    <th class="text-center">Vai trò</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày cấp tài khoản</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $account)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <span class="avatar">{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}</span>
                                <div>
                                    <strong style="color:var(--text-main);font-size:13px">{{ $account->name }}</strong>
                                    <span class="product-meta">ID #{{ $account->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $account->email }}</td>
                        <td class="text-center">
                            @if($account->role === 'admin')
                                <span class="badge badge-info">Quản trị viên (Admin)</span>
                            @else
                                <span class="badge badge-muted">Nhân viên kho (Staff)</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($account->is_active)
                                <span class="badge badge-success">Đang hoạt động</span>
                            @else
                                <span class="badge badge-danger">Đã khóa</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ \Illuminate\Support\Carbon::parse($account->created_at)->format('d/m/Y') }}
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px">
                                <a href="{{ route('users.edit', $account->id) }}" class="btn btn-sm btn-secondary">
                                    Sửa
                                </a>
                                @if(auth()->user()->role === 'admin' && auth()->id() !== $account->id)
                                    <form action="{{ route('users.destroy', $account->id) }}" method="POST"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản {{ $account->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Xóa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            Chưa có tài khoản nào trong hệ thống.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
