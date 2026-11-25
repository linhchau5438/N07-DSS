@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Sản phẩm</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Quản lý sản phẩm & hàng hóa</h2>
        <p>Quản lý danh mục mặt hàng, đơn vị tính, giá nhập tham chiếu và tình trạng tồn kho.</p>
    </div>
    @if(auth()->user()->role === 'admin')
        <div class="page-actions">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <svg class="ui-icon"><use href="#icon-category"/></svg> Danh mục hàng
            </a>
            <a href="{{ route('products.create') }}" class="btn">
                ＋ Thêm sản phẩm mới
            </a>
        </div>
    @endif
</div>

<!-- Search & Filters -->
<form action="{{ route('products.index') }}" method="GET" class="filters">
    <div class="field-search">
        <input type="search" name="search" value="{{ $search }}"
               placeholder="⌕  Tìm theo mã SKU, tên sản phẩm hoặc danh mục..."
               aria-label="Từ khóa tìm kiếm">
    </div>
    <select name="category_id" aria-label="Lọc theo danh mục" onchange="this.form.submit()">
        <option value="">Tất cả danh mục</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <select name="status" aria-label="Lọc trạng thái" onchange="this.form.submit()">
        <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Đang kinh doanh</option>
        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Ngừng kinh doanh</option>
    </select>
    <button type="submit" class="btn btn-secondary">Tìm kiếm</button>
    @if($search || !empty($filters['category_id']) || (($filters['status'] ?? 'all') !== 'all'))
        <a href="{{ route('products.index') }}" class="btn btn-quiet">Xóa bộ lọc</a>
    @endif
</form>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table>
            <thead>
                <tr>
                    <th>Mã SKU / Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th class="text-center">ĐVT</th>
                    <th class="text-right">Tồn kho thực tế</th>
                    <th class="text-right">Giá nhập tham chiếu</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img class="product-thumb"
                                     src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                     alt="{{ $product->name }}">
                                <div>
                                    <a href="{{ route('products.show', $product->id) }}" class="product-title" style="color:var(--primary)">
                                        {{ $product->name }}
                                    </a>
                                    <span class="product-meta">Mã SKU: <strong>{{ $product->product_code }}</strong></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-muted">{{ $product->category_name }}</span>
                        </td>
                        <td class="text-center">
                            <strong>{{ $product->unit }}</strong>
                        </td>
                        <td class="text-right">
                            <strong class="{{ $product->on_hand <= 0 ? 'text-danger' : '' }}" style="font-size:13px">
                                {{ number_format($product->on_hand) }}
                            </strong>
                            <small class="text-muted">{{ $product->unit }}</small>
                        </td>
                        <td class="text-right">
                            {{ $product->purchase_price ? number_format($product->purchase_price, 0, ',', '.') . ' đ' : '—' }}
                        </td>
                        <td class="text-center">
                            @if($product->status == 1)
                                <span class="badge badge-success">Đang kinh doanh</span>
                            @else
                                <span class="badge badge-muted">Ngừng kinh doanh</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div style="display:inline-flex;gap:4px">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-secondary" title="Xem chi tiết & lịch sử">
                                    Chi tiết
                                </a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-secondary" title="Chỉnh sửa">
                                        Sửa
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <span class="empty-icon">📦</span>
                            <div class="empty-title">Không tìm thấy sản phẩm nào</div>
                            <p class="empty-desc">Thử thay đổi từ khóa tìm kiếm hoặc lọc danh mục khác.</p>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('products.create') }}" class="btn btn-sm">＋ Thêm sản phẩm mới</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div style="padding:12px 16px;border-top:1px solid var(--border-light)">
            {{ $products->links('pagination.n07') }}
        </div>
    @endif
</div>
@endsection
