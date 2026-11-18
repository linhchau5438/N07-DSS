@extends('layouts.app')

@section('title', 'Báo cáo')
@section('page-title', 'Báo cáo & phân tích')
@section('page-subtitle', 'Khai thác số liệu vận hành, tồn kho và chất lượng dự báo.')

@section('content')
    <div class="page-heading">
        <div><h2>Báo cáo cửa hàng</h2><p>Chọn nội dung cần xem và lọc theo khoảng thời gian.</p></div>
    </div>

    <div class="grid grid-3"><article class="card report-card"><h3 class="card-title">BC01 · Tồn kho và cảnh báo ROP</h3><p class="card-description">Tồn, hàng đang về, IP, nhu cầu/ngày, SS, ROP và trạng thái dữ liệu.</p><a class="btn btn-secondary" href="{{ route('reports.bc01') }}">Mở báo cáo →</a></article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-stock"/></svg></div><h3 class="card-title">Nhập – xuất – tồn</h3>
            <p class="card-description">Xem lượng hàng đầu kỳ, nhập, xuất và tồn cuối kỳ theo sản phẩm.</p>
            <a class="btn btn-secondary" href="{{ route('reports.bc02') }}">Mở báo cáo →</a>
        </article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-reorder"/></svg></div><h3 class="card-title">Hàng cần nhập</h3>
            <p class="card-description">Theo dõi lượng hệ thống gợi ý, lượng được duyệt và tiến độ nhận.</p>
            <a class="btn btn-secondary" href="{{ route('reports.bc03') }}">Mở báo cáo →</a>
        </article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-chart"/></svg></div><h3 class="card-title">Sản phẩm bán chạy</h3>
            <p class="card-description">Xếp hạng mặt hàng theo số lượng và giá trị xuất kho.</p>
            <a class="btn btn-secondary" href="{{ route('reports.best-selling') }}">Mở báo cáo →</a>
        </article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-history"/></svg></div><h3 class="card-title">Hàng tồn lâu</h3>
            <p class="card-description">Tìm mặt hàng còn tồn nhưng ít hoặc không phát sinh xuất trong kỳ.</p>
            <a class="btn btn-secondary" href="{{ route('reports.bc04') }}">Mở báo cáo →</a>
        </article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-chart"/></svg></div><h3 class="card-title">Đánh giá dự báo nhu cầu</h3>
            <p class="card-description">So sánh cách ước tính tiêu thụ để tham khảo độ sai lệch trên dữ liệu đã có.</p>
            <a class="btn btn-secondary" href="{{ route('reports.bc05') }}">Mở báo cáo →</a>
        </article>
        <article class="card report-card">
            <div class="report-icon"><svg class="ui-icon"><use href="#icon-report"/></svg></div><h3 class="card-title">Phân tích theo nhiều chiều</h3>
            <p class="card-description">Xem số liệu theo danh mục, sản phẩm, thời gian và loại giao dịch.</p>
            <a class="btn btn-secondary" href="{{ route('reports.olap') }}">Mở phân tích →</a>
        </article>
    </div>
@endsection
