@extends('layouts.app')

@section('title', $product ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('products.index') }}">Sản phẩm</a>
    <span class="sep">/</span>
    <span class="current">{{ $product ? 'Chỉnh sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>{{ $product ? 'Chỉnh sửa sản phẩm: ' . $product->name : 'Thêm sản phẩm mới vào kho' }}</h2>
        <p>Khai báo thông tin cơ bản của mặt hàng. Thông số bổ sung hàng (DSS) sẽ tự động áp dụng giá trị mặc định tối ưu.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">← Quay lại danh sách</a>
</div>

<form action="{{ $product ? route('products.update', $product->id) : route('products.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($product) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1.3fr 0.7fr;gap:20px;align-items:start" class="panels">
        <!-- Main Form Section -->
        <div style="display:flex;flex-direction:column;gap:20px">
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">1. Thông tin định danh & quy cách</h3>
                        <div class="card-subtitle">Các trường có dấu (*) là bắt buộc</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="product_code">Mã SKU sản phẩm</label>
                        <input id="product_code" value="{{ $product->product_code ?? 'Hệ thống tự động sinh khi lưu' }}" readonly style="background:var(--bg-subtle)">
                        <span class="form-help">Mã định danh duy nhất của mặt hàng trong kho.</span>
                    </div>

                    <div class="form-field">
                        <label for="name">Tên sản phẩm *</label>
                        <input id="name" name="name" maxlength="150" required
                               value="{{ old('name', $product->name ?? '') }}"
                               placeholder="Ví dụ: Nước ngọt Coca Cola lon 330ml">
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="category_id">Danh mục hàng hóa *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="unit">Đơn vị tính (ĐVT) *</label>
                        <input id="unit" name="unit" maxlength="30" required
                               value="{{ old('unit', $product->unit ?? '') }}"
                               placeholder="Thùng, Chai, Lon, Hộp, Gói, Kg...">
                        @error('unit')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field form-field-full">
                        <label for="description">Mô tả hoặc quy cách đóng gói</label>
                        <textarea id="description" name="description" maxlength="255"
                                  placeholder="Quy cách thùng, hạn bảo quản, nhà sản xuất hoặc lưu ý bốc dỡ...">{{ old('description', $product->description ?? '') }}</textarea>
                        @error('description')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">2. Giá nhập tham chiếu</h3>
                        <div class="card-subtitle">Dùng để ước tính vốn tồn kho và chi phí đề xuất mua hàng</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="purchase_price">Giá nhập vốn gần nhất (VNĐ) *</label>
                        <input id="purchase_price" type="number" name="purchase_price" min="0"
                               max="9999999999999.99" step="0.01" required
                               value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
                               placeholder="Ví dụ: 120000">
                        <span class="form-help">Hệ thống tính giá trị tồn kho = Tồn thực tế × Giá nhập tham chiếu.</span>
                        @error('purchase_price')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="selling_price">Giá bán tham khảo (Không bắt buộc)</label>
                        <input id="selling_price" type="number" name="selling_price" min="0"
                               max="9999999999999.99" step="0.01"
                               value="{{ old('selling_price', $product->selling_price ?? '') }}"
                               placeholder="Tùy chọn">
                        <span class="form-help">Hệ thống quản lý kho thuần túy không bắt buộc giá bán.</span>
                        @error('selling_price')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        <!-- Aside: Image & Status -->
        <div style="display:flex;flex-direction:column;gap:20px">
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Hình ảnh minh họa</h3>
                        <div class="card-subtitle">Hỗ trợ nhận diện khi bốc xếp hàng</div>
                    </div>
                </div>

                <div style="text-align:center">
                    <img id="image-preview"
                         src="{{ $product && $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                         alt="Ảnh sản phẩm"
                         style="width:160px;height:160px;object-fit:cover;border-radius:var(--radius-md);border:1px solid var(--border);margin-bottom:12px;background:#fff">
                    
                    <label class="btn btn-secondary btn-sm" for="image" style="cursor:pointer;display:inline-flex">
                        📁 Chọn ảnh khác
                    </label>
                    <input id="image" type="file" name="image" accept="image/*" style="display:none"
                           onchange="if(this.files[0]){document.getElementById('image-preview').src = URL.createObjectURL(this.files[0])}">
                    <p class="form-help" style="margin-top:8px">Định dạng PNG, JPG, GIF. Tối đa 2MB.</p>
                    @error('image')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Trạng thái kinh doanh</h3>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer">
                        <input type="radio" name="status" value="1" {{ old('status', $product->status ?? 1) == 1 ? 'checked' : '' }}>
                        <div>
                            <strong>Đang hoạt động / kinh doanh</strong>
                            <div style="font-size:10.5px;color:var(--text-muted)">Cho phép tạo phiếu nhập, xuất và tính toán đề xuất DSS.</div>
                        </div>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer;margin-top:4px">
                        <input type="radio" name="status" value="0" {{ old('status', $product->status ?? 1) == 0 ? 'checked' : '' }}>
                        <div>
                            <strong>Tạm ngừng kinh doanh</strong>
                            <div style="font-size:10.5px;color:var(--text-muted)">Ngừng tạo mới phiếu và ẩn khỏi danh sách gợi ý bổ sung.</div>
                        </div>
                    </label>
                </div>

                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);display:flex;flex-direction:column;gap:8px">
                    <button type="submit" class="btn" style="width:100%;height:38px">
                        {{ $product ? '✓ Lưu thay đổi' : '＋ Lưu sản phẩm' }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary" style="width:100%">
                        Hủy bỏ
                    </a>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection
