@extends('layouts.app')
@section('title', 'Đề xuất nhập hàng')
@section('page-title', 'Phân tích và đề xuất nhập hàng')
@section('page-subtitle', 'Kiểm tra căn cứ, quyết định lượng nhập và theo dõi hàng đang về.')
@section('content')
<div class="page-heading"><p>{{ $pendingCount }} đề xuất chờ duyệt · {{ $openOrders->count() }} lô chờ nhận</p><a class="btn btn-secondary" href="{{ route('dss.history') }}">Lịch sử tính toán và quyết định</a></div>
<section class="content"><p>MA dùng các ngày đã kết thúc. Chỉ xuất bán được tính là nhu cầu. Ngày không bán được tính bằng 0 kể từ ngày Admin xác nhận dữ liệu đầy đủ. IP = tồn thực tế + hàng đang về; lượng cam kết = 0.</p><p>Chỉ đề xuất khi IP ≤ ROP. Sản phẩm thiếu cấu hình hoặc chưa đủ lịch sử sẽ không được tự đề xuất.</p></section>
@php $labels = ['pending'=>'Chờ duyệt', 'approved'=>'Đã duyệt', 'rejected'=>'Từ chối', 'no_action'=>'Chưa cần nhập', 'missing_config'=>'Chưa cấu hình', 'insufficient_data'=>'Thiếu dữ liệu', 'invalid_data'=>'Dữ liệu bất thường', 'fulfilled'=>'Đã nhận đủ']; @endphp
@foreach($products as $product)
<section class="content" style="margin-bottom:20px">
<div class="page-heading"><h3>{{ $product->product_code }} · {{ $product->name }}</h3><span class="badge badge-info">{{ $labels[$product->recommendation_status] ?? $product->recommendation_status }}</span></div>
<p>Tồn: {{ $product->current_on_hand }} · Đang về: {{ $product->on_order ?? 0 }} · IP: {{ $product->inventory_position ?? '—' }} · SS: {{ $product->safety_stock ?? '—' }} · ROP: {{ $product->reorder_point ?? '—' }} · Đề xuất: <strong>{{ $product->suggested_quantity ?? 0 }} {{ $product->unit }}</strong></p>
<p>{{ $product->explanation }}</p>
@if(auth()->user()->role === 'admin')
@if($product->recommendation_status === 'pending')
<form action="{{ route('dss.decide', $product->recommendation_id) }}" method="POST" class="form-grid">@csrf
<label>Số lượng duyệt<input name="approved_quantity" type="number" min="1" max="1000000" value="{{ $product->suggested_quantity }}"></label>
<label>Lý do điều chỉnh / từ chối<input name="decision_note" maxlength="1000" value="{{ old('decision_note') }}"></label>
<button class="btn" name="decision" value="approved">Duyệt</button><button class="btn btn-danger" name="decision" value="rejected">Từ chối</button></form>
@endif
<details><summary>Cấu hình phân tích cho sản phẩm</summary>
<form action="{{ route('dss.settings', $product->id) }}" method="POST" class="form-grid">@csrf @method('PUT')
<label>Lead Time (ngày)<input name="lead_time_days" type="number" min="0" max="365" value="{{ $product->lead_time_days }}" required></label>
<label>Số ngày dự trữ mục tiêu<input name="target_days" type="number" min="0" max="365" value="{{ $product->target_days }}" required></label>
<label>Cửa sổ MA<select name="ma_days">@foreach([7,14,30] as $days)<option @selected(($product->ma_days ?? 30)==$days)>{{ $days }}</option>@endforeach</select></label>
<label>Quy cách đóng gói<input name="pack_size" type="number" min="1" max="1000000" value="{{ $product->pack_size ?? 1 }}" required></label>
<label>Cách tính tồn an toàn<select name="safety_mode"><option value="manual" @selected($product->safety_mode==='manual')>Nhập thủ công</option><option value="statistical" @selected($product->safety_mode==='statistical')>Hệ số × độ lệch chuẩn × căn Lead Time</option></select></label>
<label>Tồn an toàn thủ công<input name="manual_safety_stock" type="number" min="0" max="1000000" value="{{ $product->manual_safety_stock ?? 0 }}" required></label>
<label>Hệ số an toàn<input name="safety_factor" type="number" min="0" max="3" step="0.01" value="{{ $product->safety_factor }}" required></label>
<label>Dữ liệu được xác nhận đầy đủ từ ngày<input name="observation_start" type="date" max="{{ now()->toDateString() }}" value="{{ $product->observation_start }}" required></label>
<label>Phương pháp nhu cầu<select name="forecast_method"><option value="ma">Moving Average</option><option value="auto" @selected($product->forecast_method==='auto')>ML nếu đánh giá tốt hơn MA; nếu chưa có dùng MA</option></select></label>
<button class="btn btn-secondary">Lưu và tính lại</button></form></details>
@endif
</section>
@endforeach
{{ $products->links('pagination.n07') }}
<section class="content"><h3>Nhận hàng đã duyệt</h3>
@forelse($openOrders as $order)<p>{{ $order->product_code }} · {{ $order->name }}: đã nhận {{ $order->received_quantity }}/{{ $order->approved_quantity }} {{ $order->unit }} <a class="btn btn-sm" href="{{ route('inventory.create', ['type'=>'import', 'recommendation_id'=>$order->id]) }}">Tạo phiếu nhập</a></p>@empty<p>Không có lô chờ nhận.</p>@endforelse
</section>
@endsection
