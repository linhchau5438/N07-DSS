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
<div class="flex flex-col w-full space-y-6 pb-12">

    {{-- Khung 1: Header dùng x-ui.page-header --}}
    <x-ui.page-header :title="$category ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới'"
                      chip="Quản lý danh mục"
                      :subtitle="$category ? 'Cập nhật tên hoặc mô tả cho nhóm hàng hóa này.' : 'Khởi tạo nhóm phân loại mới cho sản phẩm kho.'">
        <x-slot:actions>
            <a href="{{ route('categories.index') }}"
               class="group flex items-center gap-2 h-10 px-4 rounded-xl bg-surface-container-low text-on-surface hover:bg-surface-container transition-all border border-surface-container-high/60 font-label-md text-label-md w-fit">
                <span class="material-symbols-outlined text-[19px] text-on-surface-variant">arrow_back</span>
                <span>Quay lại danh mục</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Khung 2: Form nhập liệu dùng x-ui.section-card full width --}}
    <x-ui.section-card>
        <form action="{{ $category ? route('categories.update', $category->id) : route('categories.store') }}"
              method="POST" class="space-y-6">
            @csrf
            @if($category)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Tên danh mục --}}
                <div class="md:col-span-2">
                    <x-ui.form-field name="name"
                                     id="name"
                                     label="Tên danh mục"
                                     :required="true"
                                     :value="$category->name ?? ''"
                                     placeholder="Ví dụ: Đồ uống, Bánh kẹo, Gia vị..."
                                     maxlength="100" />
                </div>

                {{-- Mô tả danh mục --}}
                <div class="md:col-span-2">
                    <x-ui.form-textarea name="description"
                                        id="description"
                                        label="Mô tả ngắn"
                                        :value="$category->description ?? ''"
                                        placeholder="Ghi chú thêm về quy cách hoặc điều kiện bảo quản của nhóm hàng này..."
                                        :rows="4" />
                </div>
            </div>

            {{-- Cụm nút lưu/hủy --}}
            <div class="pt-4 border-t border-surface-container-high/60 flex items-center justify-end gap-3">
                <x-ui.button variant="secondary" href="{{ route('categories.index') }}">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    {{ $category ? 'Lưu thay đổi' : 'Tạo danh mục' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.section-card>
</div>
@endsection
