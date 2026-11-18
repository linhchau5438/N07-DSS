@extends('layouts.app')

@section('title', 'Sản phẩm')
@section('page-title', 'Danh sách sản phẩm')
@section('page-subtitle', 'Tìm kiếm, theo dõi giá và quản lý danh mục hàng hóa.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>Quản lý sản phẩm</h2>
            <p>{{ $products->total() }} mặt hàng · tìm theo tên, mã hoặc danh mục</p>
        </div>
        @if(auth()->user()->role === 'admin')
            <div class="page-actions">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Quản lý danh mục</a>
                <a href="{{ route('products.create') }}" class="btn">＋ Thêm sản phẩm</a>
            </div>
        @endif
    </div>

    <section class="content">
        <div class="table-caption">
            <div><h3>Danh sách hàng hóa</h3><p>Giá bán, số lượng tồn và trạng thái kinh doanh</p></div>
            <span class="badge badge-info">{{ $products->total() }} kết quả</span>
        </div>

        <form action="{{ route('products.index') }}" method="GET" class="filters">
            <div class="field-search">
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="⌕  Tìm theo mã, tên hoặc danh mục"
                       aria-label="Từ khóa tìm kiếm">
            </div>
            <select class="filter-select-auto" name="category_id" aria-label="Lọc theo danh mục"
                    onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select class="filter-select-auto" name="status" aria-label="Lọc trạng thái kinh doanh"
                    onchange="this.form.submit()">
                <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Đang kinh doanh</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Ngừng kinh doanh</option>
            </select>
            <button type="submit" class="btn">Tìm sản phẩm</button>
            @if($search || !empty($filters['category_id']) || (($filters['status'] ?? 'all') !== 'all'))
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
            @endif
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Sản phẩm / Mã</th>
                    <th>Danh mục</th>
                    <th>Đơn vị</th>
                    <th>Giá nhập</th>
                    <th>Giá bán</th>
                    <th>Trạng thái</th>
                    @if(auth()->user()->role === 'admin')<th>Thao tác</th>@endif
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                     class="product-thumb" alt="{{ $product->name }}">
                                <span>
                                    <a href="{{ route('products.show', $product->id) }}" class="product-title">{{ $product->name }}</a>
                                    <span class="product-meta">{{ $product->product_code }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $product->category_name }}</td>
                        <td>{{ $product->unit }}</td>
                        <td>{{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                        <td><strong>{{ number_format($product->selling_price, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge {{ $product->status == 1 ? 'badge-success' : 'badge-muted' }}">
                                {{ $product->status == 1 ? 'Đang kinh doanh' : 'Ngừng kinh doanh' }}
                            </span>
                        </td>
                        @if(auth()->user()->role === 'admin')
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('products.show', $product->id) }}" class="product-action action-view" aria-label="Xem {{ $product->name }}" title="Xem chi tiết"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="product-action action-edit" aria-label="Sửa {{ $product->name }}" title="Chỉnh sửa"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16.5-.8 4.3 4.3-.8L20 7.5 16.5 4 4 16.5Z"/><path d="m14.8 5.7 3.5 3.5"/></svg></a>
                                    <form action="{{ route('products.status', $product->id) }}" method="POST"
                                          onsubmit="return confirm('{{ $product->status == 1 ? 'Ngừng kinh doanh sản phẩm này?' : 'Mở kinh doanh lại sản phẩm này?' }}')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $product->status == 1 ? 0 : 1 }}">
                                        <button type="submit" class="product-action {{ $product->status == 1 ? 'action-disable' : 'action-enable' }}"
                                                aria-label="{{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }} {{ $product->name }}"
                                                title="{{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }}">
                                            @if($product->status == 1)<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/></svg>@else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7"/></svg>@endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="{{ auth()->user()->role === 'admin' ? 8 : 7 }}">
                        Chưa tìm thấy sản phẩm phù hợp. Hãy thử từ khóa hoặc danh mục khác.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links('pagination.n07') }}
    </section>
@endsection
