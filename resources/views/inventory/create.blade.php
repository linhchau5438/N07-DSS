@extends('layouts.app')

@section('title', $type === 'import' ? 'Lập phiếu nhập' : 'Lập phiếu xuất')
@section('page-title', $type === 'import' ? 'Nhập kho' : 'Xuất kho')
@section('page-subtitle', 'Ghi nhận số lượng thực tế và giá áp dụng cho giao dịch này.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>{{ $type === 'import' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho' }}</h2>
            <p>{{ $type === 'import' ? 'Ghi nhận hàng nhận thực tế và giá nhập cho từng mặt hàng.' : 'Kiểm tra lượng tồn trước khi xác nhận xuất hàng.' }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Quay lại kho</a>
    </div>

    <section class="content">
        @if($type === 'import' && $selectedRecommendation)
            <div class="alert alert-info" role="status">
                Phiếu này đang ghi nhận đề xuất đã duyệt #{{ $selectedRecommendation->id }} cho {{ $selectedRecommendation->name }}.
                Còn phải nhận {{ $selectedRecommendation->approved_quantity - $selectedRecommendation->received_quantity }} {{ $selectedRecommendation->unit }}.
            </div>
        @endif
        <form action="{{ route('inventory.store', $type) }}" method="POST">
            @csrf
            <div class="inventory-entry-grid">
                <div class="inventory-entry-main">
                    <section class="content inventory-form-panel">
                        <div class="table-caption">
                            <div><h3>Thông tin phiếu</h3><p>Người thực hiện: <strong>{{ auth()->user()->name }}</strong></p></div>
                        </div>
            <div class="form-grid" style="margin-bottom:22px">
                <div class="form-field">
                    <label for="transaction_date">Ngày giao dịch</label>
                    <input id="transaction_date" type="date" name="transaction_date"
                           max="{{ now()->toDateString() }}"
                           value="{{ old('transaction_date', now()->toDateString()) }}" required>
                </div>
                <div class="form-field">
                    <label for="reference_no">Số chứng từ tham chiếu</label><input id="reference_no" name="reference_no" maxlength="100" value="{{ old('reference_no') }}">
@if($type === 'export')
<label for="issue_purpose">Mục đích xuất</label>
<select id="issue_purpose" name="issue_purpose" required>
@foreach(['sale'=>'Bán / tiêu thụ', 'damage'=>'Hàng hỏng', 'transfer'=>'Điều chuyển', 'other'=>'Khác'] as $key=>$label)<option value="{{ $key }}" @selected(old('issue_purpose') === $key)>{{ $label }}</option>@endforeach
</select><p class="form-help">Chỉ xuất bán / tiêu thụ được dùng để dự báo nhu cầu.</p>
@endif
<label for="note">{{ $type === 'import' ? 'Nhà cung cấp / ghi chú' : 'Lý do xuất / ghi chú' }}</label>
                    <input id="note" name="note" maxlength="2000" value="{{ old('note') }}"
                           placeholder="Không bắt buộc">
                </div>
            </div>
                    </section>

                    <section class="content inventory-form-panel">
            <div class="table-caption">
                <div><h3>Sản phẩm trên phiếu</h3><p>Không thêm trùng sản phẩm trong một phiếu.</p></div>
            </div>
            <div class="table-wrap">
                <table class="form-table">
                    <thead><tr><th>Sản phẩm</th><th>Đơn vị</th><th>Tồn hiện tại</th><th>Số lượng *</th><th>Đơn giá *</th><th>Thành tiền</th><th></th></tr></thead>
                    <tbody id="items">
                    <tr class="item-row">
                        <td>
                            <select name="items[0][product_id]" class="product-select" required>
                                <option value="">Chọn sản phẩm</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                                            data-unit="{{ $product->unit }}"
                                            data-import-price="{{ $product->purchase_price }}"
                                            data-export-price="{{ $product->selling_price }}"
                                            {{ old('items.0.product_id', $selectedProductId ?? '') == $product->id ? 'selected' : '' }}>
                                        {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                                    </option>
                                @endforeach
                            </select>
                            @if($type === 'import')
                                <select name="items[0][reorder_recommendation_id]" class="order-select" style="margin-top:7px">
                                    <option value="">Nhập thường — không liên kết DSS</option>
                                    @foreach($openRecommendations as $recommendation)
                                        <option value="{{ $recommendation->id }}" data-product-id="{{ $recommendation->product_id }}"
                                                {{ old('items.0.reorder_recommendation_id', $selectedRecommendation->id ?? '') == $recommendation->id ? 'selected' : '' }}>
                                            Đề xuất #{{ $recommendation->id }} · còn {{ $recommendation->approved_quantity - $recommendation->received_quantity }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td class="unit-cell">—</td>
                        <td><small class="text-muted stock-hint">—</small></td>
                        <td><input class="quantity-input" type="number" name="items[0][quantity]" min="1" max="1000000" value="{{ old('items.0.quantity', $selectedRecommendation ? $selectedRecommendation->approved_quantity - $selectedRecommendation->received_quantity : '') }}" placeholder="Số lượng" required></td>
                        <td><input class="unit-price-input" type="number" name="items[0][unit_price]" min="0" max="9999999999999.99" step="0.01" placeholder="Tự dùng giá hiện tại"></td>
                        <td><strong class="line-total">0 đ</strong></td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>
                    </section>
                </div>

                <aside class="inventory-entry-aside">
                    <section class="content inventory-summary-card" aria-live="polite">
                        <div class="table-caption"><div><h3>{{ $type === 'import' ? 'Tổng kết phiếu nhập' : 'Kiểm tra trước khi xuất' }}</h3></div></div>
                        <div class="summary-pair"><span>Sản phẩm</span><strong id="summary-items">0</strong></div>
                        <div class="summary-pair"><span>Tổng số lượng</span><strong id="summary-quantity">0</strong></div>
                        @if($type === 'export')
                            <div class="summary-pair"><span>Dòng hợp lệ</span><strong id="summary-valid">0 / 0</strong></div>
                        @endif
                        <div class="summary-total">
                            <span>{{ $type === 'import' ? 'Tổng giá trị phiếu nhập' : 'Tổng giá trị phiếu xuất' }}</span>
                            <strong id="summary-value">0 đ</strong>
                            <small>Tính từ số lượng × đơn giá từng dòng.</small>
                        </div>
                        @if($type === 'export')
                            <div class="alert alert-info" role="status">Tồn kho được kiểm tra lại khi xác nhận. Không thể xuất số lượng âm hoặc vượt tồn.</div>
                        @else
                            <div class="form-help">Phiếu nhập chỉ cập nhật tồn kho sau khi lưu thành công.</div>
                        @endif
                        <div class="form-actions inventory-submit-actions">
                            <button type="button" class="btn btn-secondary" onclick="addInventoryRow()">＋ Thêm mặt hàng</button>
                            <button type="submit" class="btn {{ $type === 'import' ? '' : 'btn-blue' }}">Lưu phiếu {{ $type === 'import' ? 'nhập' : 'xuất' }}</button>
                            <a href="{{ route('inventory.index') }}" class="btn btn-quiet">Hủy phiếu</a>
                        </div>
                    </section>
                </aside>
            </div>
        </form>
    </section>

    <template id="item-template">
        <tr class="item-row">
            <td>
                <select name="items[__INDEX__][product_id]" class="product-select" required>
                    <option value="">Chọn sản phẩm</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $stockTotals[$product->id] ?? 0 }}"
                                data-unit="{{ $product->unit }}"
                                data-import-price="{{ $product->purchase_price }}" data-export-price="{{ $product->selling_price }}">
                            {{ $product->product_code }} — {{ $product->name }} ({{ $product->unit }})
                        </option>
                    @endforeach
                </select>
                @if($type === 'import')
                    <select name="items[__INDEX__][reorder_recommendation_id]" class="order-select" style="margin-top:7px">
                        <option value="">Nhập thường — không liên kết DSS</option>
                        @foreach($openRecommendations as $recommendation)
                            <option value="{{ $recommendation->id }}" data-product-id="{{ $recommendation->product_id }}">
                                Đề xuất #{{ $recommendation->id }} · còn {{ $recommendation->approved_quantity - $recommendation->received_quantity }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </td>
            <td class="unit-cell">—</td>
            <td><small class="text-muted stock-hint">—</small></td>
            <td><input class="quantity-input" type="number" name="items[__INDEX__][quantity]" min="1" max="1000000" placeholder="Số lượng" required></td>
            <td><input class="unit-price-input" type="number" name="items[__INDEX__][unit_price]" min="0" max="9999999999999.99" step="0.01" placeholder="Tự dùng giá hiện tại"></td>
            <td><strong class="line-total">0 đ</strong></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeInventoryRow(this)">Xóa</button></td>
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
                rows[0].querySelector('[name$="[quantity]"]').value = '';
                rows[0].querySelector('[name$="[unit_price]"]').value = '';
                rows[0].querySelector('.order-select')?.value && (rows[0].querySelector('.order-select').value = '');
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
            const priceInput = row.querySelector('[name$="[unit_price]"]');
            const stockHint = row.querySelector('.stock-hint');
            const orderSelect = row.querySelector('.order-select');
            const unitCell = row.querySelector('.unit-cell');

            if (option?.value) {
                priceInput.value = transactionType === 'import' ? option.dataset.importPrice : option.dataset.exportPrice;
                unitCell.textContent = option.dataset.unit;
                stockHint.textContent = `${option.dataset.stock}`;
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
            hint?.classList.toggle('is-warning', Boolean(exceedsStock));
            if (exceedsStock && hint) hint.textContent = `Không đủ tồn: chỉ còn ${stock}.`;
            if (!exceedsStock && option?.value && hint) {
                    hint.textContent = `${stock}`;
                }
        }

        function updateInventoryTotals() {
            const rows = Array.from(document.querySelectorAll('.item-row'));
            const itemCount = rows.filter(row => row.querySelector('.product-select').value).length;
            const totalQuantity = rows.reduce((sum, row) => sum + (Number(row.querySelector('.quantity-input').value) || 0), 0);
            const totalValue = rows.reduce((sum, row) => {
                const quantity = Number(row.querySelector('.quantity-input').value) || 0;
                const price = Number(row.querySelector('.unit-price-input').value) || 0;
                row.querySelector('.line-total').textContent = `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(quantity * price)} đ`;
                return sum + quantity * price;
            }, 0);
            const currency = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 });
            const validRows = rows.filter(row => {
                const select = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const stock = Number(select.selectedOptions[0]?.dataset.stock ?? 0);
                const quantity = Number(quantityInput.value);
                return select.value && quantity > 0 && (transactionType !== 'export' || quantity <= stock);
            }).length;

            document.getElementById('summary-items').textContent = currency.format(itemCount);
            document.getElementById('summary-quantity').textContent = currency.format(totalQuantity);
            document.getElementById('summary-value').textContent = `${currency.format(totalValue)} đ`;
            document.getElementById('summary-valid')?.replaceChildren(
                document.createTextNode(`${validRows} / ${itemCount}`)
            );
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
