@extends('layouts.app')
@section('title', 'Nhật ký nghiệp vụ')
@section('page-title', 'Nhật ký nghiệp vụ')
@section('content')
@foreach($logs as $log)<section class="content" style="margin-bottom:16px"><h3>{{ $log->action }} · {{ $log->entity_type }} #{{ $log->entity_id }}</h3><p>{{ $log->name ?? 'Hệ thống' }} · {{ $log->created_at }}</p><details><summary>Giá trị trước và sau</summary><pre style="white-space:pre-wrap">{{ json_encode(['before'=>json_decode($log->before_data ?? '{}'), 'after'=>json_decode($log->after_data ?? '{}')], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></section>@endforeach
{{ $logs->links() }}
@endsection
