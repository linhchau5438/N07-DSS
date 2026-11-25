{{--
    File: resources/views/inventory/partials/tabs.blade.php
    Mô tả: Thanh chuyển Tab điều hướng giữa Tồn kho hiện tại và Lịch sử nhập xuất.
--}}
@props([
    'currentTab' => 'stock',
    'productsCount' => 0,
    'transactionsCount' => 0,
])

<div class="flex items-center justify-between border-b border-surface-container-high/60 pb-4 flex-wrap gap-4">
    <div class="flex items-center gap-2">
        {{-- Nút Tồn kho hiện tại --}}
        <a href="{{ route('inventory.index', ['tab' => 'stock']) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $currentTab === 'stock' ? 'bg-[#1A4D38] text-white shadow-sm' : 'bg-surface-container-low text-on-surface hover:text-on-surface hover:bg-surface-container' }} select-none">
            <span class="material-symbols-outlined text-[18px] {{ $currentTab === 'stock' ? 'text-white' : 'text-on-surface' }}">warehouse</span>
            <span class="{{ $currentTab === 'stock' ? 'text-white' : 'text-on-surface font-semibold' }}">Tồn kho hiện tại</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentTab === 'stock' ? 'bg-white/20 text-white' : 'bg-white text-on-surface border border-surface-container-high/60' }}">{{ $productsCount }}</span>
        </a>

        {{-- Nút Lịch sử nhập xuất --}}
        <a href="{{ route('inventory.index', ['tab' => 'history']) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $currentTab === 'history' ? 'bg-[#1A4D38] text-white shadow-sm' : 'bg-surface-container-low text-on-surface hover:text-on-surface hover:bg-surface-container' }} select-none">
            <span class="material-symbols-outlined text-[18px] {{ $currentTab === 'history' ? 'text-white' : 'text-on-surface' }}">history</span>
            <span class="{{ $currentTab === 'history' ? 'text-white' : 'text-on-surface font-semibold' }}">Lịch sử nhập xuất</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentTab === 'history' ? 'bg-white/20 text-white' : 'bg-white text-on-surface border border-surface-container-high/60' }}">{{ $transactionsCount }}</span>
        </a>
    </div>

    <div class="text-xs text-on-surface-variant flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-secondary">sync</span>
        <span>Đồng bộ dữ liệu thời gian thực</span>
    </div>
</div>
