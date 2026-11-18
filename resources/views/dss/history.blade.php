@extends('layouts.app')
@section('title', 'Lịch sử DSS')
@section('page-title', 'Lịch sử tính toán và quyết định')
@section('content')
@if(auth()->user()->role === 'admin')<a class="btn btn-secondary" href="{{ route('audit.index') }}">Nhật ký nghiệp vụ</a>@endif
<a class="btn" href="{{ route('dss.index') }}">Quay lại đề xuất</a>
@foreach($records as $record)<section class="content" style="margin-top:16px"><h3>#{{ $record->id }} · {{ $record->product_code }} · {{ $record->name }}</h3>
<p>{{ $record->created_at }} · {{ $record->status }} · {{ $record->stock_status }}</p><p>{{ $record->explanation }}</p>
<p>Đề xuất: {{ $record->suggested_quantity }} · Duyệt: {{ $record->approved_quantity ?? '—' }} · Đã nhận: {{ $record->received_quantity }}</p>
<p>Người quyết định: {{ $record->reviewer ?? '—' }} · {{ $record->reviewed_at }} · Lý do: {{ $record->decision_note ?? '—' }}</p>
<details><summary>Đầu vào và công thức đã lưu</summary><pre style="white-space:pre-wrap">{{ json_encode(json_decode($record->calculation_snapshot ?? '{}'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></section>@endforeach
{{ $records->links() }}
@endsection
