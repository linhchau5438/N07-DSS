{{--
    Đường dẫn: resources/views/users/index.blade.php
    Mô tả: Giao diện Quản lý nhân sự hệ thống & Tài khoản theo Monochrome Design System, đồng bộ với /dashboard và /products.
    Phụ thuộc: UserController@index, layouts/app.blade.php, components/ui/*.
--}}
@extends('layouts.app')

@section('title', 'Nhân sự hệ thống')
@section('page-title', 'Tài khoản người dùng')
@section('page-subtitle', 'Quản lý quyền truy cập và vai trò trong hệ thống.')

@section('content')
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KHUNG 1: HEADER DÙNG x-ui.page-header (Card trắng rounded-2xl, p-6 md:p-8) --}}
    {{-- SỬA: Tiêu đề cố định cỡ text-headline-lg (24px, bold) đồng bộ mọi trang   --}}
    {{-- ========================================================================= --}}
    <x-ui.page-header title="Nhân sự hệ thống"
                      chip="Quản lý tài khoản & phân quyền"
                      subtitle="Theo dõi danh sách người dùng, vai trò truy cập kho vận và kiểm soát trạng thái hoạt động của từng nhân sự.">
        <x-slot:actions>
            {{-- Nối route: users.create --}}
            <a href="{{ route('users.create') }}"
               class="h-10 px-4 rounded-xl bg-primary text-white hover:bg-neutral-800 transition-colors font-label-lg text-xs font-semibold inline-flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-white">person_add</span>
                <span class="text-white">＋ Thêm nhân sự</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ========================================================================= --}}
    {{-- KHUNG 2: KHÔNG GIAN DỮ LIỆU TỔNG THỂ DÙNG x-ui.section-card                --}}
    {{-- ========================================================================= --}}
    <x-ui.section-card spacing="space-y-6">

        {{-- 1. DẢI 4 THẺ CHỈ SỐ NHÂN SỰ GỌN 140PX ĐƠN SẮC MONOCHROME --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.kpi-card label="Tổng nhân sự"
                           :value="$users->count()"
                           unit="tài khoản"
                           icon="group"
                           tone="sage">
                <span class="truncate">Nhân sự trong hệ thống</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Quản trị viên (Admin)"
                           :value="$users->where('role', 'admin')->count()"
                           unit="người"
                           icon="shield_person"
                           tone="grey">
                <span class="truncate">Toàn quyền quản lý hệ thống</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Nhân viên kho (Staff)"
                           :value="$users->where('role', 'staff')->count()"
                           unit="người"
                           icon="badge"
                           tone="sage"
                           status="neutral">
                <span class="truncate">Vận hành nhập - xuất kho</span>
            </x-ui.kpi-card>

            <x-ui.kpi-card label="Đang hoạt động"
                           :value="$users->where('is_active', 1)->count()"
                           unit="tài khoản"
                           icon="check_circle"
                           tone="grey"
                           pill="Hoạt động"
                           status="success">
                <span class="truncate">Sẵn sàng đăng nhập</span>
            </x-ui.kpi-card>
        </div>

        {{-- 2. THANH BỘ LỌC VÀ TÌM KIẾM NHÂN SỰ DÙNG x-ui.toolbar THỐNG NHẤT --}}
        <x-ui.toolbar>
            <x-ui.search-input id="userTableSearch"
                               placeholder="Tìm theo họ tên, email hoặc ID (⌘K)..."
                               oninput="filterUserTable()"
                               class="flex-1" />

            <x-ui.filter-select id="userRoleFilter"
                                name="role"
                                :autoSubmit="false"
                                onchange="filterUserTable()"
                                width="w-full sm:w-48">
                <option value="">Tất cả vai trò</option>
                <option value="admin">Quản trị viên (Admin)</option>
                <option value="staff">Nhân viên (Staff)</option>
            </x-ui.filter-select>

            <x-ui.filter-select id="userStatusFilter"
                                name="status"
                                :autoSubmit="false"
                                onchange="filterUserTable()"
                                width="w-full sm:w-48">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Đang hoạt động</option>
                <option value="locked">Đã khóa</option>
            </x-ui.filter-select>

            <button type="button" onclick="resetUserFilters()"
                    class="h-11 px-3.5 rounded-xl border border-surface-container-high/60 hover:bg-surface-container-low text-xs font-semibold text-on-surface-variant flex items-center gap-1.5 transition-colors whitespace-nowrap cursor-pointer select-none">
                <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                <span>Đặt lại</span>
            </button>
        </x-ui.toolbar>

        {{-- 3. BẢNG DỮ LIỆU NHÂN SỰ DÙNG x-ui.data-table ĐỒNG BỘ ĐỊNH DẠNG BẢNG --}}
        <div class="space-y-3">
            <x-ui.data-table minWidth="min-w-[1050px]">
                <x-slot:header>
                    <th class="py-3.5 px-5 text-left">Người dùng</th>
                    <th class="py-3.5 px-5 text-left">Email & Liên hệ</th>
                    <th class="py-3.5 px-5 text-center">Vai trò kho</th>
                    <th class="py-3.5 px-5 text-center">Trạng thái</th>
                    <th class="py-3.5 px-5 text-left">Ngày tạo</th>
                    <th class="py-3.5 pr-6 pl-2 text-right w-24 min-w-[96px] whitespace-nowrap">Thao tác</th>
                </x-slot:header>

                @forelse($users as $account)
                    @php
                        $isAdmin = $account->role === 'admin';
                        $isActive = (bool) $account->is_active;
                        $isSelf = auth()->id() === $account->id;
                    @endphp
                    <tr class="user-row h-[72px] hover:bg-surface-container-low/40 transition-colors"
                        data-name="{{ mb_strtolower($account->name) }}"
                        data-email="{{ mb_strtolower($account->email) }}"
                        data-id="{{ $account->id }}"
                        data-role="{{ $account->role }}"
                        data-status="{{ $isActive ? 'active' : 'locked' }}">
                        {{-- Cột Người dùng: Avatar 40px + Tên 16px semibold + ID 13px --}}
                        <td class="py-5 px-5">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-sm shadow-sm {{ $isAdmin ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant border border-surface-container-high' }}">
                                    {{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-semibold text-on-surface truncate">{{ $account->name }}</span>
                                        @if($isSelf)
                                            <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-[11px] font-mono text-on-surface-variant font-medium">Bạn</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-[13px] text-on-surface-variant">ID #{{ $account->id }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Cột Email: 15px font-mono --}}
                        <td class="py-5 px-5 font-mono text-[15px] text-on-surface whitespace-nowrap">
                            {{ $account->email }}
                        </td>

                        {{-- Cột Vai trò kho --}}
                        <td class="py-5 px-5 text-center whitespace-nowrap">
                            @if($isAdmin)
                                <x-ui.status-pill variant="violet" label="Quản trị viên" />
                            @else
                                <x-ui.status-pill variant="neutral" label="Nhân viên kho" />
                            @endif
                        </td>

                        {{-- Cột Trạng thái --}}
                        <td class="py-5 px-5 text-center whitespace-nowrap">
                            @if($isActive)
                                <x-ui.status-pill variant="success" label="Đang hoạt động" />
                            @else
                                <x-ui.status-pill variant="danger" label="Đã khóa" />
                            @endif
                        </td>

                        {{-- Cột Ngày tạo: 14px --}}
                        <x-ui.cell-date :date="$account->created_at" />

                        {{-- Cột Thao tác ba chấm --}}
                        <td class="py-5 px-5 text-right whitespace-nowrap">
                            <x-ui.row-actions-menu>
                                {{-- Nối route: users.edit --}}
                                <a href="{{ route('users.edit', $account->id) }}"
                                   class="flex items-center gap-2.5 px-3.5 py-2 text-on-surface hover:bg-surface-container-low transition-colors font-medium">
                                    <span class="material-symbols-outlined text-[17px] text-on-surface-variant">edit</span>
                                    <span>Chỉnh sửa tài khoản</span>
                                </a>

                                {{-- Chặn tự xóa chính tài khoản đang đăng nhập --}}
                                @if(!$isSelf)
                                    <form action="{{ route('users.destroy', $account->id) }}" method="POST"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản {{ $account->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full flex items-center gap-2.5 px-3.5 py-2 text-error hover:bg-error-container/20 transition-colors font-medium text-left">
                                            <span class="material-symbols-outlined text-[17px]">delete</span>
                                            <span>Xóa tài khoản</span>
                                        </button>
                                    </form>
                                @endif
                            </x-ui.row-actions-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <x-ui.empty-state icon="group_off"
                                              title="Chưa có tài khoản nào"
                                              message="Hệ thống chưa có tài khoản nhân sự được khởi tạo."
                                              :actionUrl="route('users.create')"
                                              actionText="Thêm tài khoản đầu tiên" />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>

            {{-- Thông tin tóm tắt số bản ghi --}}
            <div class="flex items-center justify-between text-xs text-on-surface-variant pt-2 border-t border-surface-container-high/60">
                <span id="userCountSummary">Hiển thị {{ $users->count() }} nhân sự hệ thống</span>
                <span class="font-mono text-[11px]">Bảo mật & Phân quyền theo chuẩn N07</span>
            </div>
        </div>

    </x-ui.section-card>

</div>

{{-- SỬA: Script lọc tức thời Client-side cho danh sách nhân sự --}}
@push('scripts')
<script>
    function filterUserTable() {
        const query = (document.getElementById('userTableSearch')?.value || '').trim().toLowerCase();
        const role = document.getElementById('userRoleFilter')?.value || '';
        const status = document.getElementById('userStatusFilter')?.value || '';
        const rows = document.querySelectorAll('.user-row');

        let visibleCount = 0;
        rows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const id = row.dataset.id || '';
            const rowRole = row.dataset.role || '';
            const rowStatus = row.dataset.status || '';

            const matchQuery = !query || name.includes(query) || email.includes(query) || id.includes(query);
            const matchRole = !role || rowRole === role;
            const matchStatus = !status || rowStatus === status;

            if (matchQuery && matchRole && matchStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const summaryEl = document.getElementById('userCountSummary');
        if (summaryEl) {
            summaryEl.textContent = `Hiển thị ${visibleCount} / ${rows.length} nhân sự hệ thống`;
        }
    }

    function resetUserFilters() {
        const searchInput = document.getElementById('userTableSearch');
        const roleSelect = document.getElementById('userRoleFilter');
        const statusSelect = document.getElementById('userStatusFilter');
        if (searchInput) searchInput.value = '';
        if (roleSelect) roleSelect.value = '';
        if (statusSelect) statusSelect.value = '';
        filterUserTable();
    }
</script>
@endpush
@endsection
