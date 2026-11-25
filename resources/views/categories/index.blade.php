@extends('layouts.app')

@section('title', 'Danh mục hàng hóa')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('products.index') }}">Sản phẩm</a>
    <span class="sep">/</span>
    <span class="current">Danh mục</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Danh mục phân loại hàng hóa</h2>
        <p>Phân nhóm các mặt hàng theo tính chất sản phẩm để quản lý tồn kho và báo cáo đa chiều.</p>
    </div>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('categories.create') }}" class="btn">
            ＋ Thêm danh mục mới
        </a>
    @endif
</div>

<div class="card" style="padding:0;overflow:hidden;max-width:960px">
    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th style="width:60px" class="text-center">#</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th class="text-center">Số lượng mặt hàng</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color:var(--text-main);font-size:13px">{{ $category->name }}</strong>
                        </td>
                        <td class="text-muted">{{ $category->description ?: 'Chưa có mô tả' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $category->products_count > 0 ? 'badge-info' : 'badge-muted' }}">
                                {{ $category->products_count }} sản phẩm
                            </span>
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px">
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-secondary">
                                    Sửa
                                </a>
                                @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Chỉ có thể xóa nếu không còn sản phẩm thuộc nhóm này.')">
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
                        <td colspan="5" class="empty-state">
                            <span class="empty-icon">📁</span>
                            <div class="empty-title">Chưa có danh mục nào</div>
                            <p class="empty-desc">Tạo nhóm hàng đầu tiên để phân loại sản phẩm.</p>
                            <a href="{{ route('categories.create') }}" class="btn btn-sm">＋ Thêm danh mục mới</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
