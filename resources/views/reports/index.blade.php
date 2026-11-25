@extends('layouts.app')

@section('title', 'Trung tâm báo cáo & Phân tích kho')

@section('breadcrumbs')
    <span class="sep">/</span>
    <span class="current">Báo cáo & Phân tích</span>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h2>Trung tâm Báo cáo & Phân tích Dữ liệu Kho</h2>
        <p>Hệ thống cung cấp các báo cáo chuẩn hỗ trợ kiểm soát lưu chuyển hàng hóa, đánh giá rủi ro đọng vốn và phân tích đa chiều.</p>
    </div>
</div>

<!-- Group 1: Báo cáo Vận hành & Đặt hàng -->
<div style="margin-bottom:28px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <span style="font-size:16px">📦</span>
        <h3 style="font-size:15px;color:var(--text-main)">1. Kiểm soát tồn kho & Bổ sung hàng</h3>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:16px">
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-warning">Cảnh báo ngưỡng</span>
                    <span class="badge badge-muted">BC01</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Cảnh báo ngưỡng đặt hàng lại (ROP)</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Theo dõi tồn thực tế, hàng đang về, vị thế tồn kho (IP), tồn an toàn (SS) và các mặt hàng chạm ngưỡng cần nhập.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.bc01') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Xem báo cáo ROP →
                </a>
            </div>
        </div>

        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-success">Nhập - Xuất - Tồn</span>
                    <span class="badge badge-muted">BC02</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Tổng hợp Nhập – Xuất – Tồn theo kỳ</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Báo cáo số lượng đầu kỳ, tổng nhập từ NCC, xuất tiêu thụ nội bộ, xuất bán và tồn cuối kỳ chi tiết từng sản phẩm.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.bc02') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Xem báo cáo NXT →
                </a>
            </div>
        </div>

        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-info">Tiến độ nhập hàng</span>
                    <span class="badge badge-muted">BC03</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Kế hoạch & Đề xuất nhập hàng</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Thống kê danh sách đề xuất nhập hàng, số lượng phê duyệt, tiến độ nhận hàng thực tế và đối soát chênh lệch.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.bc03') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Xem báo cáo đề xuất →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Group 2: Rủi ro & Dự báo -->
<div style="margin-bottom:28px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <span style="font-size:16px">🛡️</span>
        <h3 style="font-size:15px;color:var(--text-main)">2. Kiểm soát rủi ro tồn kho & Dự báo nhu cầu</h3>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:16px">
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-danger">Kiểm soát rủi ro</span>
                    <span class="badge badge-muted">BC04</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Hàng tồn dư & Chậm luân chuyển</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Phân loại rủi ro: Hàng không tiêu thụ (Dead Stock), Hàng tồn mức cao (Excess Stock > 60 ngày) và Hàng chậm luân chuyển.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.bc04') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Kiểm tra rủi ro tồn kho →
                </a>
            </div>
        </div>

        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-info">Mô hình DSS</span>
                    <span class="badge badge-muted">BC05</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Dự báo nhu cầu xuất hàng (MA & ML)</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Đánh giá và so sánh độ chính xác giữa Trung bình trượt (Moving Average) và Hồi quy xu hướng (Linear Trend).
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.bc05') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Xem phân tích dự báo →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Group 3: Phân tích Nâng cao -->
<div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <span style="font-size:16px">📊</span>
        <h3 style="font-size:15px;color:var(--text-main)">3. Phân tích đa chiều & Thống kê nâng cao</h3>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:16px">
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-muted">Phân tích đa chiều</span>
                    <span class="badge badge-muted">OLAP</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Phân tích kho đa chiều (OLAP)</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Khai phá dữ liệu nhập xuất theo các lát cắt: Theo nhóm hàng, Theo từng sản phẩm, Theo tháng và Loại giao dịch.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.olap') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Mở bảng phân tích OLAP →
                </a>
            </div>
        </div>

        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span class="badge badge-muted">Xếp hạng</span>
                </div>
                <h4 style="font-size:14px;color:var(--text-main);margin-bottom:6px">Mặt hàng có lưu lượng xuất cao nhất</h4>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
                    Xếp hạng các sản phẩm có số lượng xuất tiêu thụ hoặc bán ra lớn nhất trong kỳ để lập kế hoạch dự trữ ưu tiên.
                </p>
            </div>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-light)">
                <a href="{{ route('reports.best-selling') }}" class="btn btn-secondary btn-sm" style="width:100%">
                    Xem bảng xếp hạng →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
