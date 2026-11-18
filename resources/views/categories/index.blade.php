@extends('layouts.app')

@section('title', 'Danh mục')
@section('page-title', 'Danh mục sản phẩm')
@section('page-subtitle', 'Sắp xếp mặt hàng theo nhóm để quản lý dễ dàng hơn.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>Các nhóm hàng</h2>
            <p>{{ $categories->count() }} danh mục đang được sử dụng</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn">＋ Thêm danh mục</a>
    </div>

    <section class="content">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th>Sản phẩm</th>
                    <th>Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td class="text-muted">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td class="text-muted">{{ $category->description ?: 'Chưa có mô tả' }}</td>
                        <td><span class="badge {{ $category->products_count ? 'badge-info' : 'badge-muted' }}">{{ $category->products_count }} mặt hàng</span></td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-secondary btn-sm">Sửa</a>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="5">Chưa có danh mục. Hãy tạo danh mục đầu tiên.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
