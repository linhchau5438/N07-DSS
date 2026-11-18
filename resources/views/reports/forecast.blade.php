@extends('layouts.app')
@section('title', 'BC05 - Đánh giá dự báo')
@section('page-title', 'BC05 - Phân tích nhu cầu xuất hàng')
@section('content')
<section class="content"><h3>Phân tích nhu cầu xuất hàng</h3><p>Moving Average và Linear Regression</p>
<p>Mỗi sản phẩm được đánh giá riêng theo đơn vị tính của nó. Linear Regression học xu hướng theo thời gian; chỉ được chọn nếu cả MAE và RMSE thấp hơn MA trên cùng 7 ngày cuối. Dự báo có đơn vị sản phẩm/ngày, không phải tổng tuần.</p>
<p>Chưa đủ dữ liệu: cần ít nhất cửa sổ MA + 7 ngày đã kết thúc và ngày bắt đầu quan sát được xác nhận trong cấu hình DSS. Chỉ xuất bán / tiêu thụ được sử dụng; ngày không bán được tính 0.</p>
<form method="GET"><label>Sản phẩm<select name="product_id"><option value="">Tất cả</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(request('product_id')==$product->id)>{{ $product->product_code }} · {{ $product->name }}</option>@endforeach</select></label><button class="btn">Lọc lịch sử</button></form>
@if(auth()->user()->role === 'admin')
<form method="POST" action="{{ route('forecast.run') }}">@csrf <label>Sản phẩm cần đánh giá<select name="product_id" required>@foreach($products as $product)<option value="{{ $product->id }}" @selected(request('product_id')==$product->id)>{{ $product->product_code }} · {{ $product->name }}</option>@endforeach</select></label><button class="btn">Huấn luyện, đánh giá và lưu</button></form>
@endif</section>
@forelse($runs as $run)
@php $result = json_decode($run->results, true); @endphp
<section class="content" style="margin-top:20px"><h3>{{ $run->product_code }} · {{ $run->name }}</h3>
<p>Kỳ dữ liệu {{ $run->period_start }} → {{ $run->period_end }} · MA {{ $run->ma_days }} ngày · {{ $run->model_version }} · chạy lúc {{ $run->created_at }}</p>
<p>Chọn: <strong>{{ $result['selected'] === 'linear' ? 'Linear Regression' : 'Moving Average' }}</strong>. Kết quả chỉ được áp dụng khi cấu hình sản phẩm chọn tự động và dữ liệu vẫn khớp lần chạy.</p>
@php $peak = max(1, max($result['daily'])); @endphp
<div style="display:flex;align-items:flex-end;gap:3px;height:140px;overflow-x:auto;margin:20px 0" role="img" aria-label="Lượng tiêu thụ từng ngày của {{ $run->name }}">
@foreach($result['daily'] as $offset=>$quantity)<div style="flex:1;min-width:8px;background:#6d8fe7;height:{{ max(2, 100*$quantity/$peak) }}%" title="{{ \Illuminate\Support\Carbon::parse($run->period_start)->addDays($offset)->toDateString() }}: {{ $quantity }} {{ $run->unit }}"></div>@endforeach
</div><p>Biểu đồ tiêu thụ theo thứ tự ngày trong kỳ; bảy ngày cuối dùng để kiểm thử.</p>
<table><thead><tr><th>Phương pháp</th><th>MAE</th><th>RMSE</th><th>Dự báo ngày kế tiếp ({{ $run->unit }}/ngày)</th></tr></thead><tbody>
@foreach($result['metrics'] as $method=>$values)<tr><td>{{ $method === 'ma' ? 'Moving Average' : 'Linear Regression' }}</td><td>{{ number_format($values['mae'], 4) }}</td><td>{{ number_format($values['rmse'], 4) }}</td><td>{{ number_format($values['forecast'], 4) }}</td></tr>@endforeach
</tbody></table><details><summary>Chuỗi dữ liệu và dấu vết tái lập</summary><p>{{ implode(', ', $result['daily']) }}</p><code>{{ $result['series_sha256'] }}</code></details></section>
@empty<section class="content"><p>Chưa có lần đánh giá đã lưu. Admin chọn sản phẩm và chạy đánh giá khi dữ liệu đủ dài.</p></section>@endforelse
{{ $runs->links() }}
@endsection
