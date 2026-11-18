@extends('layouts.app')

@section('title', 'Chi tiết phiếu')
@section('page-title', $transaction->type === 'import' ? 'Chi tiết phiếu nhập' : 'Chi tiết phiếu xuất')
@section('page-subtitle', 'Xem nội dung và giá trị từng mặt hàng trong giao dịch.')

@section('content')
    <div class="page-heading">
        <div>
            <h2>{{ $transaction->transaction_code }}</h2>
            <p>Phiếu {{ $transaction->type === 'import' ? 'nhập kho' : 'xuất kho' }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Quay lại kho</a>
    </div>

    <section class="content">
@if($transaction->type === 'export' && auth()->user()->role === 'admin')
<details><summary>Rà soát mục đích xuất (có lưu nhật ký)</summary><form action="{{ route('inventory.purpose', $transaction->id) }}" method="POST">@csrf @method('PUT')
<label>Mục đích<select name="issue_purpose">@foreach(['sale'=>'Bán / tiêu thụ','damage'=>'Hàng hỏng','transfer'=>'Điều chuyển','other'=>'Khác'] as $key=>$label)<option value="{{ $key }}" @selected($transaction->issue_purpose===$key)>{{ $label }}</option>@endforeach</select></label>
<label>Lý do xác nhận<input name="reason" required maxlength="1000"></label><button class="btn">Lưu xác nhận</button></form></details>@endif
        <div class="detail-grid"><div class="detail-item"><span>Mục đích</span><strong>{{ ['sale'=>'Bán / tiêu thụ','damage'=>'Hàng hỏng','transfer'=>'Điều chuyển','other'=>'Khác','receipt'=>'Nhập kho','unknown'=>'Chưa xác định'][$transaction->issue_purpose] ?? $transaction->issue_purpose }}</strong></div><div class="detail-item"><span>Chứng từ tham chiếu</span><strong>{{ $transaction->reference_no ?? '—' }}</strong></div>
            <div class="detail-item"><span>Ngày giao dịch</span><strong>{{ \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</strong></div>
            <div class="detail-item"><span>Nhân viên thực hiện</span><strong>{{ $transaction->staff_name ?? 'Tài khoản đã xóa' }}</strong></div>
            <div class="detail-item"><span>Tổng giá trị phiếu</span><strong>{{ number_format($transaction->total_value, 0, ',', '.') }} đ</strong></div>
            <div class="detail-item"><span>Ghi chú</span><strong>{{ $transaction->note ?: 'Không có' }}</strong></div>
        </div>

        <div class="table-caption"><div><h3>Danh sách mặt hàng</h3></div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mã sản phẩm</th><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th><th>Đề xuất DSS</th></tr></thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->product_code }}</td>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>{{ number_format($item->unit_price, 0, ',', '.') }} đ</td>
                        <td><strong>{{ number_format($item->line_total, 0, ',', '.') }} đ</strong></td>
                        <td>{{ $item->reorder_recommendation_id ? '#'.$item->reorder_recommendation_id : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
