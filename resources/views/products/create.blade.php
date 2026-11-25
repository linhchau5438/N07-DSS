{{--
    Đường dẫn: resources/views/products/create.blade.php
    Mô tả: Giao diện Thêm mới / Chỉnh sửa sản phẩm chuẩn Design System N07 Monochrome, full bề rộng đồng bộ với /products.
    Phụ thuộc: ProductController, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@php $product = $product ?? null; @endphp

@section('title', $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm')
@section('page-title', $product ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm')
@section('page-subtitle', 'Cập nhật thông tin hàng hóa, đơn vị tính và định giá.')

@section('content')
<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- Khung 1: Header dùng x-ui.page-header full width --}}
    <x-ui.page-header :title="$product ? 'Chỉnh sửa sản phẩm: ' . $product->name : 'Thêm sản phẩm mới'"
                      :chip="$product ? 'SKU: ' . $product->product_code : 'Sản phẩm mới'"
                      subtitle="Điền đầy đủ thông tin hàng hóa, phân loại danh mục, quy cách đơn vị và giá nhập/bán tham chiếu.">
        <x-slot:actions>
            <a href="{{ route('products.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">arrow_back</span>
                <span>Danh sách sản phẩm</span>
            </a>
            @if($product)
                <a href="{{ route('products.show', $product->id) }}"
                   class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md">
                    <span class="material-symbols-outlined text-[19px] text-on-surface-variant">visibility</span>
                    <span>Xem chi tiết</span>
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Khung 2: Form nhập liệu dùng x-ui.section-card full width --}}
    <x-ui.section-card spacing="space-y-6">
        <form action="{{ $product ? route('products.update', $product->id) : route('products.store') }}"
              method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($product) @method('PUT') @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Khối trái: Thông tin cơ bản & Giá (Chiếm 2 cột) --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-surface-container-high/60 space-y-4">
                        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-primary">inventory_2</span>
                            <span>Thông tin cơ bản</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Mã sản phẩm --}}
                            <div>
                                <label for="product_code" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">
                                    Mã sản phẩm (SKU)
                                </label>
                                <input id="product_code" value="{{ $product->product_code ?? 'Tự động tạo khi lưu' }}" readonly
                                       class="w-full h-11 px-3.5 rounded-xl bg-surface-container-high/50 text-on-surface-variant text-[14px] font-mono font-semibold border border-surface-container-high cursor-not-allowed">
                                <p class="text-[11px] text-on-surface-variant mt-1">{{ $product ? 'Mã định danh duy nhất của sản phẩm trong kho.' : 'Hệ thống tự động cấp mã theo cấu hình danh mục.' }}</p>
                            </div>

                            {{-- Tên sản phẩm --}}
                            <x-ui.form-field name="name"
                                             id="name"
                                             label="Tên sản phẩm"
                                             :required="true"
                                             :value="$product->name ?? ''"
                                             placeholder="Ví dụ: Bia Tiger lon 330ml"
                                             maxlength="150" />

                            {{-- Danh mục --}}
                            <x-ui.form-select name="category_id"
                                              id="category_id"
                                              label="Danh mục hàng hóa"
                                              :required="true">
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </x-ui.form-select>

                            {{-- Đơn vị tính --}}
                            <x-ui.form-field name="unit"
                                             id="unit"
                                             label="Đơn vị tính (ĐVT)"
                                             :required="true"
                                             :value="$product->unit ?? ''"
                                             placeholder="Lon, chai, thùng, gói, kg..."
                                             maxlength="30" />

                            {{-- Mô tả sản phẩm --}}
                            <div class="md:col-span-2">
                                <x-ui.form-textarea name="description"
                                                    id="description"
                                                    label="Mô tả sản phẩm"
                                                    :value="$product->description ?? ''"
                                                    placeholder="Quy cách đóng gói, thương hiệu, nhà sản xuất hoặc lưu ý bảo quản..."
                                                    maxlength="255"
                                                    :rows="3" />
                            </div>
                        </div>
                    </div>

                    {{-- Khối Định giá tham chiếu --}}
                    <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-surface-container-high/60 space-y-4">
                        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-secondary">payments</span>
                            <span>Định giá & Tài chính</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Giá nhập tham chiếu --}}
                            <x-ui.form-field name="purchase_price"
                                             id="purchase_price"
                                             type="number"
                                             label="Giá nhập tham chiếu (VNĐ)"
                                             :required="true"
                                             min="0"
                                             max="9999999999999.99"
                                             step="0.01"
                                             :value="$product->purchase_price ?? ''"
                                             placeholder="0" />

                            {{-- Giá bán đề xuất --}}
                            <x-ui.form-field name="selling_price"
                                             id="selling_price"
                                             type="number"
                                             label="Giá bán đề xuất (VNĐ)"
                                             :required="true"
                                             min="0"
                                             max="9999999999999.99"
                                             step="0.01"
                                             :value="$product->selling_price ?? ''"
                                             placeholder="0" />
                        </div>
                        <p class="text-[11px] text-on-surface-variant">Tồn kho được cập nhật tự động khi lập phiếu nhập / xuất, không sửa trực tiếp số lượng ở đây.</p>
                    </div>
                </div>

                {{-- Khối phải: Hình ảnh & Trạng thái kinh doanh (Chiếm 1 cột) --}}
                <div class="space-y-6">
                    {{-- Hình ảnh sản phẩm --}}
                    <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-surface-container-high/60 space-y-4">
                        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-on-surface-variant">image</span>
                            <span>Hình ảnh sản phẩm</span>
                        </h3>

                        <div class="flex flex-col items-center gap-4">
                            <div class="w-40 h-40 rounded-2xl bg-white border border-surface-container-high overflow-hidden shadow-2xs flex items-center justify-center">
                                <img id="image-preview"
                                     src="{{ $product && $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                                     alt="Ảnh xem trước sản phẩm" class="w-full h-full object-cover">
                            </div>

                            <label class="h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors text-xs font-semibold inline-flex items-center gap-2 border border-surface-container-high/60 cursor-pointer shadow-2xs" for="image">
                                <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                                <span>{{ $product && $product->image ? 'Thay ảnh khác' : 'Chọn ảnh sản phẩm' }}</span>
                            </label>
                            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewProductImage(event)" class="sr-only">
                            <p class="text-[11px] text-on-surface-variant text-center">JPG, PNG hoặc WEBP · tối đa 2 MB.</p>
                            @error('image')<p class="text-xs text-error font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Trạng thái kinh doanh --}}
                    <div class="p-6 rounded-2xl bg-surface-container-low/40 border border-surface-container-high/60 space-y-4">
                        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-on-surface-variant">toggle_on</span>
                            <span>Trạng thái kinh doanh</span>
                        </h3>

                        @php($currentStatus = (string) old('status', (string) ($product->status ?? 1)))
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all {{ $currentStatus === '1' ? 'bg-secondary-container/20 border-secondary' : 'bg-white border-surface-container-high hover:bg-surface-container-low' }}">
                                <input type="radio" name="status" value="1" {{ $currentStatus === '1' ? 'checked' : '' }} class="text-primary focus:ring-primary w-4 h-4">
                                <div class="text-xs">
                                    <strong class="text-on-surface block font-bold">Đang kinh doanh</strong>
                                    <span class="text-on-surface-variant text-[11px]">Cho phép xuất nhập và quản lý tồn kho</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all {{ $currentStatus === '0' ? 'bg-surface-container-high border-neutral-400' : 'bg-white border-surface-container-high hover:bg-surface-container-low' }}">
                                <input type="radio" name="status" value="0" {{ $currentStatus === '0' ? 'checked' : '' }} class="text-primary focus:ring-primary w-4 h-4">
                                <div class="text-xs">
                                    <strong class="text-on-surface block font-bold">Ngừng kinh doanh</strong>
                                    <span class="text-on-surface-variant text-[11px]">Tạm ngưng nhập và bán sản phẩm này</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Cụm nút Lưu / Hủy căn phải --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-high/60">
                <x-ui.button variant="secondary" href="{{ route('products.index') }}">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    {{ $product ? 'Lưu thay đổi' : 'Thêm sản phẩm' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.section-card>

</div>
@endsection

@push('scripts')
<script>
    function previewProductImage(event) {
        const file = event.target.files?.[0];
        if (file) {
            document.getElementById('image-preview').src = URL.createObjectURL(file);
        }
    }
</script>
@endpush
