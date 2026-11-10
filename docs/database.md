# Cơ sở dữ liệu

## Vị trí

- Schema khởi tạo: [`database/website_tainguyen.sql`](../database/website_tainguyen.sql)
- Migration: [`database/migrations/`](../database/migrations/)
- Seed: `database/seeds/` (chưa có dữ liệu seed riêng)

Các migration cần chạy theo thứ tự tên:

1. `001_accounts.sql`
2. `002_routes_and_fleet.sql`
3. `003_bookings.sql`
4. `004_services.sql`
5. `005_content_and_support.sql`

Schema/migration tạo các nhóm dữ liệu tài khoản và vai trò; tuyến, xe, ghế và chuyến; đặt vé và thanh toán; dịch vụ và vận đơn; liên hệ và nội dung công khai.

## Kết nối

Thiết lập các biến môi trường trước khi khởi chạy Apache/PHP:

| Tên | Giá trị mặc định |
| --- | --- |
| `WEBSITE_DB_HOST` | `127.0.0.1` |
| `WEBSITE_DB_USER` | `root` |
| `WEBSITE_DB_PASSWORD` | *(rỗng)* |
| `WEBSITE_DB_NAME` | `website_tainguyen` |

`Website_Customer/backend/database/config.php` và `Website_Manage/config/config.php` đọc cùng các biến. `.env.example` không phải trình nạp dotenv.

## Cài đặt an toàn

- Sao lưu trước khi chạy SQL trên database có dữ liệu.
- Schema khởi tạo là dành cho database mới; không import lặp lại.
- Không commit thông tin xác thực thật.
- Production cần tài khoản database có quyền tối thiểu cần thiết, không dùng mặc định XAMPP.
