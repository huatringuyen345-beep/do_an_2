# Cơ sở dữ liệu

## Vị trí

- Schema khởi tạo: [`database/website_tainguyen.sql`](../database/website_tainguyen.sql)
- Migration: [`database/migrations/`](../database/migrations/)
- Seed quản trị viên demo local: `database/seeds/001_demo_admin.sql`

Các migration cần chạy theo thứ tự tên:

1. `001_accounts.sql`
2. `002_routes_and_fleet.sql`
3. `003_bookings.sql`
4. `004_services.sql`
5. `005_content_and_support.sql`
6. `006_customer_routes.sql`

Schema/migration tạo các nhóm dữ liệu tài khoản và vai trò; tuyến, xe, ghế và chuyến; đặt vé và thanh toán; dịch vụ và vận đơn; liên hệ và nội dung công khai. Tuyến khách hàng, giá ghế/giường và giờ khởi hành được đọc trực tiếp từ SQL. Migration 006 bổ sung giá giường nằm cùng các tuyến khởi hành từ Bạc Liêu cho database đã cài từ các migration trước.

## Kết nối

Thiết lập các biến môi trường trước khi khởi chạy Apache/PHP:

| Tên | Giá trị mặc định |
| --- | --- |
| `WEBSITE_DB_HOST` | `127.0.0.1` |
| `WEBSITE_DB_USER` | `root` |
| `WEBSITE_DB_PASSWORD` | *(rỗng)* |
| `WEBSITE_DB_NAME` | `website_tainguyen` |

`Website_Customer/backend/database/config.php` và `Website_Manage/config/config.php` đọc cùng các biến. `.env.example` không phải trình nạp dotenv.

Nếu khởi tạo bằng migrations và cần tài khoản quản trị local, chạy seed demo sau khi hoàn tất migrations. Seed không ghi đè tài khoản cùng email đã tồn tại. Không dùng tài khoản demo trong production; đổi mật khẩu sau lần đăng nhập đầu.

## Cài đặt an toàn

- Sao lưu trước khi chạy SQL trên database có dữ liệu.
- Schema khởi tạo là dành cho database mới; không import lặp lại.
- Chỉ chạy migration 006 một lần trên database chưa có cột `gia_ve_giuong_nam`; không chạy lại nếu đã áp dụng phần cập nhật tuyến tương ứng.
- Không commit thông tin xác thực thật.
- Production cần tài khoản database có quyền tối thiểu cần thiết, không dùng mặc định XAMPP.
