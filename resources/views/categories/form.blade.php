@extends('layouts.app')

@section('title', $category ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('products.index') }}">Sản phẩm</a>
    <span class="sep">/</span>
    <a href="{{ route('categories.index') }}">Danh mục</a>
    <span class="sep">/</span>
    <span class="current">{{ $category ? 'Chỉnh sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>{{ $category ? 'Chỉnh sửa danh mục: ' . $category->name : 'Thêm danh mục hàng hóa mới' }}</h2>
        <p>Danh mục giúp phân nhóm các sản phẩm cùng ngành hàng để tiện tra cứu và phân tích đa chiều OLAP.</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">← Danh sách danh mục</a>
</div>

<section class="card" style="max-width:640px">
    <form action="{{ $category ? route('categories.update', $category->id) : route('categories.store') }}" method="POST">
        @csrf
        @if($category) @method('PUT') @endif

        <div class="form-grid">
            <div class="form-field form-field-full">
                <label for="name">Tên danh mục *</label>
                <input id="name" name="name" maxlength="100" required
                       value="{{ old('name', $category->name ?? '') }}"
                       placeholder="Ví dụ: Đồ uống, Hàng tiêu dùng, Thực phẩm đóng gói...">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field form-field-full">
                <label for="description">Mô tả đặc điểm nhóm hàng</label>
                <textarea id="description" name="description"
                          placeholder="Mô tả ngắn về nhóm mặt hàng hoặc lưu ý bảo quản...">{{ old('description', $category->description ?? '') }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border-light);display:flex;gap:10px">
            <button type="submit" class="btn">
                {{ $category ? '✓ Lưu thay đổi' : '＋ Tạo danh mục' }}
            </button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                Hủy bỏ
            </a>
        </div>
    </form>
</section>
@endsection
