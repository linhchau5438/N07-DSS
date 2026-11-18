@extends('layouts.app')

@section('title', $category ? 'Sửa danh mục' : 'Thêm danh mục')
@section('page-title', $category ? 'Chỉnh sửa danh mục' : 'Tạo danh mục')
@section('page-subtitle', 'Danh mục giúp bạn tìm và phân tích hàng hóa theo nhóm.')

@section('content')
    <div class="page-heading">
        <div><h2>{{ $category ? 'Thông tin danh mục' : 'Nhóm hàng mới' }}</h2></div>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">← Danh sách danh mục</a>
    </div>

    <section class="content" style="max-width:760px">
        <form action="{{ $category ? route('categories.update', $category->id) : route('categories.store') }}"
              method="POST">
            @csrf
            @if($category) @method('PUT') @endif

            <div class="form-grid">
                <div class="form-field form-field-full">
                    <label for="name">Tên danh mục</label>
                    <input id="name" name="name" maxlength="100" required
                           value="{{ old('name', $category->name ?? '') }}"
                           placeholder="Ví dụ: Đồ uống">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field form-field-full">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description"
                              placeholder="Mô tả ngắn về nhóm sản phẩm...">{{ old('description', $category->description ?? '') }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px">
                <button type="submit" class="btn">{{ $category ? 'Lưu thay đổi' : 'Tạo danh mục' }}</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </section>
@endsection
