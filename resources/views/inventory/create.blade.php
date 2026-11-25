@extends('layouts.app')

@section('title', $type === 'import' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho')

@section('breadcrumbs')
    <span class="sep">/</span>
    <a href="{{ route('inventory.index') }}">Kho hàng</a>
    <span class="sep">/</span>
    <span class="current">{{ $type === 'import' ? 'Phiếu nhập' : 'Phiếu xuất' }}</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>{{ $type === 'import' ? 'Tạo phiếu nhập hàng vào kho' : 'Tạo phiếu xuất hàng khỏi kho' }}</h2>
        <p>
            @if($type === 'import')
                Ghi nhận số lượng thực tế nhận từ nhà cung cấp hoặc điều chuyển nội bộ.
            @else
                Ghi nhận hàng xuất tiêu thụ, xuất hủy hỏng hoặc điều chuyển. Hệ thống tự động kiểm tra tồn khả dụng.
            @endif
        </p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Danh sách tồn kho</a>
</div>

@if($type === 'import' && $selectedRecommendation)
    <div class="alert alert-info" role="status">
        <span>🚚</span>
        <div>
            <strong>Đang nhập kho theo đề xuất đặt hàng đã duyệt #{{ $selectedRecommendation->id }}:</strong>
            Sản phẩm <strong>{{ $selectedRecommendation->name }}</strong> (Mã: {{ $selectedRecommendation->product_code }}). 
            Đã đặt: <strong>{{ $selectedRecommendation->ordered_quantity }}</strong> {{ $selectedRecommendation->unit }} · 
            Đã nhận trước: {{ $selectedRecommendation->received_quantity }} · 
            Còn phải nhận đợt này: <strong>{{ $selectedRecommendation->ordered_quantity - $selectedRecommendation->received_quantity }}</strong> {{ $selectedRecommendation->unit }}.
        </div>
    </div>
@endif

<form action="{{ route('inventory.store', $type) }}" method="POST">
    @csrf

    <div style="display:grid;grid-template-columns:1.35fr 0.65fr;gap:20px;align-items:start" class="panels">
        <!-- Main Form & Items Table -->
        <div style="display:flex;flex-direction:column;gap:20px">
            <!-- Header Information -->
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">1. Thông tin chung phiếu {{ $type === 'import' ? 'nhập' : 'xuất' }}</h3>
                        <div class="card-subtitle">Thủ kho lập phiếu: <strong>{{ auth()->user()->name }}</strong></div>
                    </div>
                    <span class="badge {{ $type === 'import' ? 'badge-success' : 'badge-info' }}" style="font-size:12px;padding:4px 10px">
                        {{ $type === 'import' ? '📥 Phiếu Nhập Kho' : '📤 Phiếu Xuất Kho' }}
                    </span>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="transaction_date">Ngày thực hiện giao dịch *</label>
                        <input id="transaction_date" type="date" name="transaction_date"
                               max="{{ now()->toDateString() }}"
                               value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        <span class="form-help">Mặc định là ngày hôm nay. Không thể ghi nhận ngày tương lai.</span>
                        @error('transaction_date')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="reference_no">Số chứng từ / Hóa đơn tham chiếu</label>
                        <input id="reference_no" name="reference_no" maxlength="100"
                               value="{{ old('reference_no') }}"
                               placeholder="Ví dụ: HĐ-88291, Vận đơn VNPost...">
                        <span class="form-help">Số phiếu giao nhận từ NCC hoặc số biên bản (tùy chọn).</span>
                        @error('reference_no')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    @if($type === 'export')
                        <div class="form-field">
                            <label for="issue_purpose">Mục đích xuất kho *</label>
                            <select id="issue_purpose" name="issue_purpose" required>
                                <option value="consumption" @selected(old('issue_purpose') === 'consumption')>Xuất tiêu thụ / Sử dụng nội bộ / Cấp phát</option>
                                <option value="sale" @selected(old('issue_purpose') === 'sale')>Xuất bán hàng thương mại</option>
                                <option value="damage" @selected(old('issue_purpose') === 'damage')>Xuất hàng hỏng, lỗi, hết hạn, hủy bỏ</option>
                                <option value="return_vendor" @selected(old('issue_purpose') === 'return_vendor')>Xuất trả nhà cung cấp</option>
                                <option value="transfer" @selected(old('issue_purpose') === 'transfer')>Xuất điều chuyển sang kho khác</option>
                                <option value="adjustment" @selected(old('issue_purpose') === 'adjustment')>Xuất điều chỉnh kiểm kê định kỳ</option>
                                <option value="other" @selected(old('issue_purpose') === 'other')>Mục đích khác</option>
                            </select>
                            <span class="form-help" style="color:var(--primary)">💡 Nhu cầu DSS chỉ tính các phiếu xuất tiêu thụ hoặc bán hàng.</span>
                            @error('issue_purpose')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="form-field {{ $type === 'import' ? 'form-field-full' : '' }}">
                        <label for="note">{{ $type === 'import' ? 'Nhà cung cấp / Diễn giải nguồn gốc' : 'Ghi chú thêm' }}</label>
                        <input id="note" name="note" maxlength="2000" value="{{ old('note') }}"
                               placeholder="{{ $type === 'import' ? 'Tên nhà cung cấp, số xe, ghi chú tình trạng kiện...' : 'Người nhận hàng, bộ phận yêu cầu...' }}">
                        @error('note')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <!-- Line Items Table -->
            <section class="card" style="padding:0;overflow:hidden">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                    <div>
                        <h3 class="card-title">2. Danh sách mặt hàng trên phiếu</h3>
                        <div class="card-subtitle">Chọn mặt hàng và điền số lượng thực tế kiểm đếm</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addInventoryRow()">
                        ＋ Thêm dòng hàng
                    </button>
                </div>

                <div class="table-wrap" style="border:none">
                    <table>
                        <thead>
                            <tr>
                                <th style="min-width:240px">Sản phẩm</th>
                                <th class="text-center" style="width:70px">ĐVT</th>
                                <th class="text-right" style="width:100px">Tồn hiện tại</th>
                                <th class="text-right" style="width:130px">Số lượng *</th>
                                <th class="text-right" style="width:140px">Đơn giá vốn {{ $type === 'export' ? '(tùy chọn)' : '*' }}</th>
                                <th class="text-right" style="width:130px">Thành tiền</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="items">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][product_id]" class="product-select" required style="width:100%">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                                                    data-unit="{{ $product->unit }}"
                                                    data-import-price="{{ $product->purchase_price }}"
                                                    data-export-price="{{ $product->purchase_price }}"
                                                    @selected($selectedRecommendation && $selectedRecommendation->product_id == $product->id)>
                                                {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($type === 'import')
                                        <select name="items[0][reorder_recommendation_id]" class="order-select" style="margin-top:6px;width:100%;font-size:11px">
                                            <option value="">Nhập thông thường (Không liên kết đề xuất)</option>
                                            @foreach($openRecommendations as $rec)
                                                <option value="{{ $rec->id }}" data-product-id="{{ $rec->product_id }}"
                                                        @selected($selectedRecommendation && $selectedRecommendation->id == $rec->id)>
                                                    Liên kết Đơn đặt DSS #{{ $rec->id }} (còn thiếu {{ $rec->ordered_quantity - $rec->received_quantity }} {{ $rec->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td class="text-center unit-cell" style="font-weight:600">
                                    {{ $selectedRecommendation ? $selectedRecommendation->unit : '—' }}
                                </td>
                                <td class="text-right">
                                    <span class="stock-hint" style="font-weight:600">—</span>
                                </td>
                                <td>
                                    <input class="quantity-input" type="number" name="items[0][quantity]" min="1" max="1000000"
                                           value="{{ $selectedRecommendation ? ($selectedRecommendation->ordered_quantity - $selectedRecommendation->received_quantity) : '' }}"
                                           placeholder="Số lượng" required style="text-align:right">
                                </td>
                                <td>
                                    <input class="unit-price-input" type="number" name="items[0][unit_price]" min="0" max="9999999999999.99" step="0.01"
                                           placeholder="Giá tham chiếu" style="text-align:right">
                                </td>
                                <td class="text-right">
                                    <strong class="line-total" style="color:var(--text-main)">0 đ</strong>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-quiet btn-sm" onclick="removeInventoryRow(this)" title="Xóa dòng này" style="color:var(--danger)">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Sticky Summary & Submit Panel -->
        <aside style="position:sticky;top:80px">
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tổng kết phiếu</h3>
                        <div class="card-subtitle">Kiểm tra trước khi ghi nhận</div>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:12px;font-size:12.5px">
                    <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                        <span class="text-muted">Loại phiếu</span>
                        <strong>{{ $type === 'import' ? 'Nhập kho' : 'Xuất kho' }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                        <span class="text-muted">Số mặt hàng</span>
                        <strong id="summary-items">1</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
                        <span class="text-muted">Tổng số lượng</span>
                        <strong id="summary-quantity" style="color:var(--primary);font-size:15px">0</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:4px">
                        <span class="text-muted">Tổng giá trị ước tính</span>
                        <strong id="summary-value" style="font-size:16px;color:var(--text-main)">0 đ</strong>
                    </div>
                </div>

                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);display:flex;flex-direction:column;gap:8px">
                    <button type="submit" class="btn" style="width:100%;height:40px;font-size:13px">
                        ✓ Xác nhận hoàn tất {{ $type === 'import' ? 'nhập kho' : 'xuất kho' }}
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary" style="width:100%">
                        Hủy phiếu
                    </a>
                </div>

                <div style="margin-top:12px;font-size:11px;color:var(--text-muted);line-height:1.4">
                    @if($type === 'export')
                        ⚠️ Hệ thống sẽ tự động chặn nếu số lượng xuất lớn hơn tồn kho khả dụng thực tế.
                    @else
                        📦 Sau khi nhập kho, số lượng sẽ ngay lập tức được cộng vào tồn thực tế (On Hand) và trừ khỏi hàng đang về (On Order).
                    @endif
                </div>
            </section>
        </aside>
    </div>
</form>

<!-- Row Template -->
<template id="item-template">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][product_id]" class="product-select" required style="width:100%">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                            data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                            data-unit="{{ $product->unit }}"
                            data-import-price="{{ $product->purchase_price }}"
                            data-export-price="{{ $product->purchase_price }}">
                        {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                    </option>
                @endforeach
            </select>
            @if($type === 'import')
                <select name="items[__INDEX__][reorder_recommendation_id]" class="order-select" style="margin-top:6px;width:100%;font-size:11px">
                    <option value="">Nhập thông thường (Không liên kết đề xuất)</option>
                    @foreach($openRecommendations as $rec)
                        <option value="{{ $rec->id }}" data-product-id="{{ $rec->product_id }}">
                            Liên kết Đơn đặt DSS #{{ $rec->id }} (còn thiếu {{ $rec->ordered_quantity - $rec->received_quantity }} {{ $rec->unit }})
                        </option>
                    @endforeach
                </select>
            @endif
        </td>
        <td class="text-center unit-cell" style="font-weight:600">—</td>
        <td class="text-right">
            <span class="stock-hint" style="font-weight:600">—</span>
        </td>
        <td>
            <input class="quantity-input" type="number" name="items[__INDEX__][quantity]" min="1" max="1000000"
                   placeholder="Số lượng" required style="text-align:right">
        </td>
        <td>
            <input class="unit-price-input" type="number" name="items[__INDEX__][unit_price]" min="0" max="9999999999999.99" step="0.01"
                   placeholder="Giá tham chiếu" style="text-align:right">
        </td>
        <td class="text-right">
            <strong class="line-total" style="color:var(--text-main)">0 đ</strong>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-quiet btn-sm" onclick="removeInventoryRow(this)" title="Xóa dòng này" style="color:var(--danger)">
                ✕
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    let nextInventoryIndex = 1;
    const transactionType = @json($type);

    function addInventoryRow() {
        const html = document.getElementById('item-template').innerHTML.replaceAll('__INDEX__', nextInventoryIndex++);
        document.getElementById('items').insertAdjacentHTML('beforeend', html);
        updateInventoryTotals();
    }

    function removeInventoryRow(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 1) {
            rows[0].querySelector('.product-select').value = '';
            rows[0].querySelector('.quantity-input').value = '';
            rows[0].querySelector('.unit-price-input').value = '';
            if (rows[0].querySelector('.order-select')) rows[0].querySelector('.order-select').value = '';
            updateInventoryRow(rows[0]);
            updateInventoryTotals();
            return;
        }
        button.closest('tr').remove();
        updateInventoryTotals();
    }

    function updateInventoryRow(row) {
        const productSelect = row.querySelector('.product-select');
        const option = productSelect.options[productSelect.selectedIndex];
        const priceInput = row.querySelector('.unit-price-input');
        const stockHint = row.querySelector('.stock-hint');
        const orderSelect = row.querySelector('.order-select');
        const unitCell = row.querySelector('.unit-cell');

        if (option?.value) {
            priceInput.value = option.dataset.importPrice || '';
            unitCell.textContent = option.dataset.unit || '—';
            stockHint.textContent = `${option.dataset.stock || 0} ${option.dataset.unit || ''}`;
        } else {
            unitCell.textContent = '—';
            stockHint.textContent = '—';
            priceInput.value = '';
        }

        if (orderSelect) {
            Array.from(orderSelect.options).forEach(item => {
                if (item.value) item.disabled = item.dataset.productId !== productSelect.value;
            });
            if (orderSelect.selectedOptions[0]?.disabled) orderSelect.value = '';
        }
        updateInventoryTotals();
        updateStockWarning(row);
    }

    function updateStockWarning(row) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const hint = row.querySelector('.stock-hint');
        const option = select.options[select.selectedIndex];
        const stock = Number(option?.dataset.stock ?? 0);
        const quantity = Number(quantityInput.value);
        const exceedsStock = transactionType === 'export' && option?.value && quantity > stock;

        quantityInput.setCustomValidity(exceedsStock ? `Số lượng xuất vượt tồn hiện tại (${stock}).` : '');
        if (exceedsStock && hint) {
            hint.textContent = `❌ Vượt tồn (chỉ còn ${stock})`;
            hint.style.color = 'var(--danger)';
        } else if (option?.value && hint) {
            hint.textContent = `${stock} ${option.dataset.unit || ''}`;
            hint.style.color = 'var(--text-muted)';
        }
    }

    function updateInventoryTotals() {
        const rows = Array.from(document.querySelectorAll('.item-row'));
        const itemCount = rows.filter(row => row.querySelector('.product-select').value).length;
        const totalQuantity = rows.reduce((sum, row) => sum + (Number(row.querySelector('.quantity-input').value) || 0), 0);
        const totalValue = rows.reduce((sum, row) => {
            const quantity = Number(row.querySelector('.quantity-input').value) || 0;
            const price = Number(row.querySelector('.unit-price-input').value) || 0;
            const line = quantity * price;
            row.querySelector('.line-total').textContent = `${new Intl.NumberFormat('vi-VN').format(line)} đ`;
            return sum + line;
        }, 0);

        const currency = new Intl.NumberFormat('vi-VN');
        document.getElementById('summary-items').textContent = currency.format(itemCount);
        document.getElementById('summary-quantity').textContent = currency.format(totalQuantity);
        document.getElementById('summary-value').textContent = `${currency.format(totalValue)} đ`;
    }

    document.addEventListener('change', event => {
        if (event.target.matches('.product-select')) updateInventoryRow(event.target.closest('tr'));
        if (event.target.matches('.order-select') && event.target.value) {
            const row = event.target.closest('tr');
            row.querySelector('.product-select').value = event.target.selectedOptions[0].dataset.productId;
            updateInventoryRow(row);
        }
        if (event.target.matches('.quantity-input, .unit-price-input')) {
            const row = event.target.closest('tr');
            updateStockWarning(row);
            updateInventoryTotals();
        }
    });

    document.addEventListener('input', event => {
        if (event.target.matches('.quantity-input, .unit-price-input')) {
            const row = event.target.closest('tr');
            updateStockWarning(row);
            updateInventoryTotals();
        }
    });

    document.querySelectorAll('.item-row').forEach(updateInventoryRow);
    updateInventoryTotals();
</script>
@endpush
