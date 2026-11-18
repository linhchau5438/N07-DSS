# Phiếu kiểm thử N07 đã ghi vào MySQL thật

Đợt: `TEST-N07-20261118`. CSDL: `n07_dss` theo .env. Đã lưu 10 phiếu nhập và 10 phiếu xuất, mỗi phiếu 3 dòng sản phẩm hiện có (60 dòng). Dữ liệu mô phỏng kiểm thử nằm trong bảng nghiệp vụ thật; giao diện truy vấn DB, không viết cứng dữ liệu hiển thị.

Có 8 phiếu xuất bán, 1 xuất hỏng, 1 điều chuyển. Giá lấy từ sản phẩm trong DB. Ngày giao dịch 08–17/11/2026. Đã đối soát tồn trước/sau, tổng tiền và không xuất vượt tồn.

| Mã phiếu | Loại | Ngày | Mục đích | Chi tiết |
|---|---|---|---|---|
| PN-20261118010814-LCEG | Nhập | 2026-11-08 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/21) |
| PX-20261118010814-CTDB | Xuất | 2026-11-08 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/22) |
| PN-20261118010814-CQD5 | Nhập | 2026-11-09 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/23) |
| PX-20261118010815-YOBS | Xuất | 2026-11-09 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/24) |
| PN-20261118010815-9D8L | Nhập | 2026-11-10 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/25) |
| PX-20261118010815-HNJQ | Xuất | 2026-11-10 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/26) |
| PN-20261118010816-RFLT | Nhập | 2026-11-11 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/27) |
| PX-20261118010817-GOMH | Xuất | 2026-11-11 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/28) |
| PN-20261118010818-6XGI | Nhập | 2026-11-12 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/29) |
| PX-20261118010818-WDKR | Xuất | 2026-11-12 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/30) |
| PN-20261118010819-QLU0 | Nhập | 2026-11-13 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/31) |
| PX-20261118010820-6LUE | Xuất | 2026-11-13 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/32) |
| PN-20261118010820-13XJ | Nhập | 2026-11-14 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/33) |
| PX-20261118010821-7BIC | Xuất | 2026-11-14 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/34) |
| PN-20261118010822-JFOR | Nhập | 2026-11-15 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/35) |
| PX-20261118010822-YS7H | Xuất | 2026-11-15 | sale | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/36) |
| PN-20261118010823-QGMG | Nhập | 2026-11-16 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/37) |
| PX-20261118010823-YMA1 | Xuất | 2026-11-16 | damage | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/38) |
| PN-20261118010824-LBWI | Nhập | 2026-11-17 | receipt | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/39) |
| PX-20261118010825-6QFE | Xuất | 2026-11-17 | transfer | [Xem phiếu](http://localhost/N07_DSS/public/inventory/transactions/40) |

Mở Kho để xem tồn và phiếu gần nhất; BC02 chọn 08/11/2026 đến 17/11/2026 để đối soát. Danh sách phía trên có đủ 20 liên kết chi tiết.

Cập nhật theo yêu cầu tiếp theo: đã cấu hình DSS cho 26 sản phẩm trong DB thật. Giả định kiểm thử chung: MA 7 ngày, Lead Time 3 ngày, mục tiêu dự trữ 7 ngày, tồn an toàn thủ công 10 đơn vị, quy cách 1, mốc dữ liệu 08/11/2026, phương pháp MA. Sau tính lại ngày 18/11/2026: 18 sản phẩm cần nhập (chờ duyệt), 8 bình thường. Không tự duyệt đề xuất. Bộ 10 ngày này chưa đủ đánh giá ML (tối thiểu MA 7 + 7 ngày kiểm thử).

Script: `php scripts/seed_inventory_test.php --apply`; chạy lại không thêm trùng. Bản chụp dữ liệu trước khi thêm và danh sách ID đã tạo nằm trong `storage/app/backups`, đã chặn truy cập HTTP.
