@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm')
@section('page-title', 'Chi tiết sản phẩm')
@section('page-subtitle', 'Thông tin hàng hóa, tình trạng kinh doanh và lịch sử nhập xuất gần nhất.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>{{ $product->name }}</h2>
            <p>{{ $product->product_code }} · {{ $product->category_name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">← Danh sách sản phẩm</a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('products.edit', $product->id) }}" class="btn">Chỉnh sửa</a>
                <form action="{{ route('products.status', $product->id) }}" method="POST"
                      onsubmit="return confirm('{{ $product->status == 1 ? 'Ngừng kinh doanh sản phẩm này? Lịch sử kho vẫn được giữ lại.' : 'Mở kinh doanh lại sản phẩm này?' }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $product->status == 1 ? 0 : 1 }}">
                    <button class="btn {{ $product->status == 1 ? 'btn-warn' : '' }}" type="submit">
                        {{ $product->status == 1 ? 'Ngừng kinh doanh' : 'Mở kinh doanh' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid product-detail-layout">
        <div class="product-detail-sidebar">
        <section class="content product-detail-card">
            <img class="product-detail-image"
                 src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                 alt="{{ $product->name }}">
            <div class="product-detail-copy">
                <span class="badge {{ $product->status == 1 ? 'badge-success' : 'badge-muted' }}">
                    {{ $product->status == 1 ? 'Đang kinh doanh' : 'Ngừng kinh doanh' }}
                </span>
                <h3>{{ $product->name }}</h3>
                <p class="text-muted">{{ $product->description ?: 'Chưa có mô tả cho sản phẩm này.' }}</p>
            </div>
            <div class="detail-grid product-detail-grid">
                <div class="detail-item"><span>Mã sản phẩm</span><strong>{{ $product->product_code }}</strong></div>
                <div class="detail-item"><span>Danh mục</span><strong>{{ $product->category_name }}</strong></div>
                <div class="detail-item"><span>Đơn vị tính</span><strong>{{ $product->unit }}</strong></div>
                <div class="detail-item"><span>Số lượng tồn</span><strong>{{ $product->on_hand }} {{ $product->unit }}</strong></div>
                <div class="detail-item"><span>Giá nhập</span><strong>{{ number_format($product->purchase_price, 0, ',', '.') }} đ</strong></div>
                <div class="detail-item"><span>Giá bán</span><strong>{{ number_format($product->selling_price, 0, ',', '.') }} đ</strong></div>
            </div>
        </section>
        <section class="content product-stock-card">
            <div class="table-caption"><div><h3>Tình trạng tồn kho</h3></div></div>
            <div class="stock-metrics">
                <div><span>Tồn hiện tại</span><strong class="{{ $product->on_hand <= 0 ? 'text-danger' : '' }}">{{ $product->on_hand }} {{ $product->unit }}</strong></div>
                <div><span>Tồn an toàn</span><strong>{{ $recommendation->safety_stock ?? '—' }} {{ $recommendation ? $product->unit : '' }}</strong></div>
                <div><span>Điểm đặt lại</span><strong>{{ $recommendation->reorder_point ?? '—' }} {{ $recommendation ? $product->unit : '' }}</strong></div>
                <div><span>Gợi ý bổ sung</span><strong>{{ $recommendation->suggested_quantity ?? '—' }} {{ $recommendation ? $product->unit : '' }}</strong></div>
            </div>
            @if($recommendation && $recommendation->suggested_quantity > 0)
                <p class="stock-recommendation">Nên xem xét nhập thêm {{ $recommendation->suggested_quantity }} {{ $product->unit }}.</p>
            @else
                <p class="form-help">Chưa có gợi ý mới. Số tồn thay đổi sau khi ghi nhận phiếu nhập hoặc xuất.</p>
            @endif
            <a href="{{ route('dss.index') }}" class="text-link">Xem phân tích và đề xuất →</a>
        </section>
        </div>

        <section class="content">
            <div class="table-caption">
                <div><h3>Lịch sử nhập xuất</h3><p>10 giao dịch gần nhất của mặt hàng</p></div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mã phiếu</th><th>Loại</th><th>Ngày</th><th>Số lượng</th><th>Đơn giá</th><th>Người thực hiện</th></tr></thead>
                    <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td><span class="product-title">{{ $movement->transaction_code }}</span></td>
                            <td><span class="badge {{ $movement->type === 'import' ? 'badge-success' : 'badge-info' }}">{{ $movement->type === 'import' ? 'Nhập' : 'Xuất' }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($movement->transaction_date)->format('d/m/Y') }}</td>
                            <td>{{ $movement->quantity }} {{ $product->unit }}</td>
                            <td>{{ number_format($movement->unit_price, 0, ',', '.') }} đ</td>
                            <td>{{ $movement->staff_name ?? 'Tài khoản đã xóa' }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-state" colspan="6">Chưa có giao dịch cho sản phẩm này.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
