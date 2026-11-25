{{--
    Đường dẫn: resources/views/categories/form.blade.php
    Mô tả: Giao diện Thêm / Chỉnh sửa danh mục hàng hóa chuẩn Design System N07 Monochrome, sử dụng x-ui.page-header & x-ui.section-card.
    Phụ thuộc: CategoryController, layouts/app.blade.php.
--}}

@extends('layouts.app')

@php $category = $category ?? null; @endphp

@section('title', $category ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới')
@section('page-title', $category ? 'Chỉnh sửa danh mục' : 'Tạo danh mục')

@section('content')
<div class="flex flex-col w-full space-y-6 pb-12 max-w-4xl mx-auto">

    {{-- Khung 1: Header dùng x-ui.page-header --}}
    <x-ui.page-header :title="$category ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới'"
                      :subtitle="$category ? 'Cập nhật tên hoặc mô tả cho nhóm hàng hóa này.' : 'Khởi tạo nhóm phân loại mới cho sản phẩm kho.'">
        <x-slot:actions>
            <a href="{{ route('categories.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md w-fit">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">arrow_back</span>
                <span>Quay lại danh mục</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Khung 2: Form nhập liệu dùng x-ui.section-card --}}
    <x-ui.section-card>
        <form action="{{ $category ? route('categories.update', $category->id) : route('categories.store') }}"
              method="POST" class="space-y-6">
            @csrf
            @if($category)
                @method('PUT')
            @endif

            <div class="space-y-4">
                {{-- Tên danh mục --}}
                <div class="space-y-1.5">
                    <label for="name" class="block font-label-md text-xs font-bold text-on-surface uppercase tracking-wider">
                        Tên danh mục <span class="text-error">*</span>
                    </label>
                    <input id="name" name="name" maxlength="100" required
                           value="{{ old('name', $category->name ?? '') }}"
                           placeholder="Ví dụ: Đồ uống, Bánh kẹo, Gia vị..."
                           class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-on-surface-variant font-body-md text-sm border border-surface-container-high focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none transition-all"/>
                    @error('name')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mô tả danh mục --}}
                <div class="space-y-1.5">
                    <label for="description" class="block font-label-md text-xs font-bold text-on-surface uppercase tracking-wider">
                        Mô tả ngắn
                    </label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Ghi chú thêm về quy cách hoặc điều kiện bảo quản của nhóm hàng này..."
                              class="w-full p-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-on-surface-variant font-body-md text-sm border border-surface-container-high focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none transition-all">{{ old('description', $category->description ?? '') }}</textarea>
                    @error('description')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Cụm nút lưu/hủy --}}
            <div class="pt-4 border-t border-surface-container-high/60 flex items-center justify-end gap-3">
                <a href="{{ route('categories.index') }}"
                   class="h-10 px-5 rounded-xl text-on-surface hover:bg-surface-container-low font-label-md text-xs flex items-center transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit"
                        class="h-10 px-6 rounded-xl bg-primary text-white font-label-md text-xs font-bold hover:bg-neutral-800 transition-all shadow-sm flex items-center gap-2 active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    <span>{{ $category ? 'Lưu thay đổi' : 'Tạo danh mục' }}</span>
                </button>
            </div>
        </form>
    </x-ui.section-card>
</div>
@endsection
