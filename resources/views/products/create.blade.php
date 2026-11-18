@extends('layouts.app')

@section('title', $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm')
@section('page-title', 'Quản lý sản phẩm')
@section('page-subtitle', 'Cập nhật thông tin hàng hóa và giá bán.')

@section('content')
    <div class="page-heading">
        <div>
            <div class="breadcrumb-text">
                <a href="{{ route('products.index') }}">Sản phẩm</a>
                <span>/</span>
                <span>{{ $product ? 'Chỉnh sửa' : 'Thêm mới' }}</span>
            </div>
            <h2>{{ $product ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm' }}</h2>
            <p>Thông tin có dấu * là bắt buộc. Giá được tính bằng VNĐ.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">← Danh sách sản phẩm</a>
    </div>

    <form action="{{ $product ? route('products.update', $product->id) : route('products.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($product) @method('PUT') @endif

        <div class="product-form-layout">
            <div class="product-form-main">
                <section class="content form-section">
                    <div class="table-caption"><div><h3>Thông tin cơ bản</h3></div></div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="product_code">Mã sản phẩm</label>
                            <input id="product_code" value="{{ $product->product_code ?? 'Tự động tạo khi lưu' }}" readonly>
                            <small class="text-muted">{{ $product ? 'Mã sản phẩm được hệ thống quản lý.' : 'Mã duy nhất sẽ được tạo tự động.' }}</small>
                        </div>
                        <div class="form-field">
                            <label for="name">Tên sản phẩm *</label>
                            <input id="name" name="name" maxlength="150" required
                                   value="{{ old('name', $product->name ?? '') }}"
                                   placeholder="Ví dụ: Bia Tiger lon 330ml">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label for="category_id">Danh mục *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
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
                            <label for="unit">Đơn vị tính *</label>
                            <input id="unit" name="unit" maxlength="30" required
                                   value="{{ old('unit', $product->unit ?? '') }}"
                                   placeholder="Lon, chai, thùng, gói...">
                            @error('unit')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field form-field-full">
                            <label for="description">Mô tả sản phẩm</label>
                            <textarea id="description" name="description" maxlength="255"
                                      placeholder="Quy cách, thương hiệu hoặc thông tin cần lưu ý...">{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="content form-section">
                    <div class="table-caption"><div><h3>Giá bán & tồn kho</h3></div></div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="purchase_price">Giá nhập tham chiếu *</label>
                            <input id="purchase_price" type="number" name="purchase_price" min="0"
                                   max="9999999999999.99" step="0.01" required
                                   value="{{ old('purchase_price', $product->purchase_price ?? '') }}">
                            @error('purchase_price')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label for="selling_price">Giá bán *</label>
                            <input id="selling_price" type="number" name="selling_price" min="0"
                                   max="9999999999999.99" step="0.01" required
                                   value="{{ old('selling_price', $product->selling_price ?? '') }}">
                            @error('selling_price')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p class="form-help">Tồn kho được cập nhật bằng phiếu nhập / xuất, không sửa trực tiếp ở đây.</p>
                </section>
            </div>

            <aside class="product-form-aside">
                <section class="content form-section">
                    <div class="table-caption"><div><h3>Hình ảnh sản phẩm</h3></div></div>
                    <img id="image-preview"
                         src="{{ $product && $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                         alt="Ảnh xem trước sản phẩm" class="product-image-preview">
                    <label class="btn btn-secondary image-upload-label" for="image">
                        {{ $product && $product->image ? 'Thay ảnh' : 'Chọn ảnh' }}
                    </label>
                    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                           onchange="previewProductImage(event)" class="visually-hidden">
                    <p class="form-help">JPG, PNG hoặc WEBP · tối đa 2 MB.</p>
                    @error('image')<p class="field-error">{{ $message }}</p>@enderror
                </section>

                <section class="content form-section">
                    <div class="table-caption"><div><h3>Trạng thái kinh doanh</h3></div></div>
                    @php($currentStatus = (string) old('status', (string) ($product->status ?? 1)))
                    <label class="status-choice {{ $currentStatus === '1' ? 'selected' : '' }}">
                        <input type="radio" name="status" value="1" {{ $currentStatus === '1' ? 'checked' : '' }}>
                        <span class="status-dot"></span> Đang kinh doanh
                    </label>
                    <label class="status-choice {{ $currentStatus === '0' ? 'selected' : '' }}">
                        <input type="radio" name="status" value="0" {{ $currentStatus === '0' ? 'checked' : '' }}>
                        <span class="status-dot"></span> Ngừng kinh doanh
                    </label>
                    <p class="form-help">Chỉ sản phẩm đang kinh doanh mới được chọn khi lập phiếu nhập / xuất.</p>
                </section>
            </aside>
        </div>

        <div class="product-form-footer">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Hủy</a>
            <button type="submit" class="btn">{{ $product ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function previewProductImage(event) {
            const file = event.target.files?.[0];
            if (file) {
                document.getElementById('image-preview').src = URL.createObjectURL(file);
            }
        }

        document.querySelectorAll('.status-choice input').forEach(input => {
            input.addEventListener('change', () => {
                document.querySelectorAll('.status-choice').forEach(choice => choice.classList.remove('selected'));
                input.closest('.status-choice').classList.add('selected');
            });
        });
    </script>
@endpush
