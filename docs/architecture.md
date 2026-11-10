# Kiến trúc dự án

## Các ứng dụng

- `Website_Customer/pages/`: trang PHP dành cho khách hàng.
- `Website_Customer/backend/`: bộ điều khiển, dữ liệu mẫu, cấu hình phiên và kết nối database.
- `Website_Customer/includes/`: header, footer và phần giao diện dùng chung.
- `Website_Customer/assets/`: CSS, JavaScript và hình ảnh khách hàng.
- `Website_Manage/admin/modules/`: trang nghiệp vụ quản lý theo module; `admin/_bootstrap.php` cung cấp kết nối, kiểm tra quyền, CSRF và các hàm dùng chung.
- `Website_Manage/includes/` và `Website_Manage/assets/`: khung dùng chung và static files khu vực quản lý.
- `database/`: schema khởi tạo và migration SQL.
- `storage/`: vị trí dành cho tệp tải lên, log và bản sao lưu; dữ liệu vận hành không được commit.

Các URL cũ trong `Website_Customer/frontend/` tiếp tục nạp trang tương ứng trong `pages/`. Các URL trang quản lý cũ chuyển hướng tạm thời sang module mới để giữ tương thích với bookmark và phương thức POST.

## Luồng PHP khách hàng hiện có

- `AuthController.php`: xác thực, đăng ký và cập nhật tài khoản.
- `BookingController.php`: xử lý yêu cầu đặt vé.
- `RecruitmentController.php`: xử lý form ứng tuyển.
- `SupportController.php`: xử lý form liên hệ.

Các thư mục models/services/middleware/helpers và một số module quản lý hiện là chỗ mở rộng, chưa chứa nghiệp vụ riêng. Thanh toán trực tuyến và đặt dịch vụ phía khách chưa được triển khai thành controller riêng.

## Cấu hình

Hai ứng dụng dùng chung các biến môi trường `WEBSITE_DB_HOST`, `WEBSITE_DB_USER`, `WEBSITE_DB_PASSWORD`, `WEBSITE_DB_NAME`. `.env.example` chỉ là mẫu, không được tự động nạp bởi PHP. Cấu hình chi tiết xem [database.md](./database.md).
