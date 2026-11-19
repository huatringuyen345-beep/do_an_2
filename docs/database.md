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
7. `007_payment_flow.sql`
8. `008_recruitment_applications.sql`

Schema/migration tạo các nhóm dữ liệu tài khoản và vai trò; tuyến, xe, ghế và chuyến; đặt vé và thanh toán; lịch sử giao dịch; dịch vụ và vận đơn; liên hệ, nội dung công khai và hồ sơ ứng tuyển. Tuyến khách hàng, giá ghế/giường và giờ khởi hành được đọc trực tiếp từ SQL.

- Migration `001` tạo `vai_tro` và `nguoi_dung`; mật khẩu tài khoản phải được băm bằng `password_hash()` trong ứng dụng hoặc seed.
- Migration `006` bổ sung cột `gia_ve_giuong_nam`, cập nhật giá và thêm các tuyến/giờ khởi hành phục vụ khách hàng từ Bạc Liêu.
- Migration `007` tạo `lich_su_thanh_toan` để ghi các lần thay đổi trạng thái thanh toán và hỗ trợ đối soát.
- Migration `008` tạo `ho_so_ung_tuyen` liên kết với `tuyen_dung` bằng `slug_vi_tri`.

## Kết nối

Thiết lập các biến môi trường trước khi khởi chạy Apache/PHP:

| Tên | Giá trị mặc định |
| --- | --- |
| `WEBSITE_DB_HOST` | `127.0.0.1` |
| `WEBSITE_DB_USER` | `root` |
| `WEBSITE_DB_PASSWORD` | *(rỗng)* |
| `WEBSITE_DB_NAME` | `website_tainguyen` |

`Website_Customer/backend/database/config.php` và `Website_Manage/config/config.php` đọc cùng các biến. `.env.example` không phải trình nạp dotenv.

Nếu khởi tạo bằng migrations và cần tài khoản demo local, chạy seed tương ứng sau khi hoàn tất migrations. Các seed demo nằm trong `database/seeds/` và không ghi đè tài khoản cùng email đã tồn tại. Không dùng tài khoản demo trong production; đổi mật khẩu sau lần đăng nhập đầu.

Lệnh tạo database mới khuyến nghị:

```bash
php Website_Customer/backend/tools/install.php
```

Trên database mới, installer nhập schema khởi tạo `database/website_tainguyen.sql` rồi áp dụng migration `008_recruitment_applications.sql`. Installer không chạy tự động các migration `001-007` vì chúng là quy trình nâng cấp cho database đã dùng schema khởi tạo.

## Cài đặt an toàn

- Sao lưu trước khi chạy SQL trên database có dữ liệu.
- Schema khởi tạo là dành cho database mới; không import lặp lại.
- Chạy migrations `001-008` theo thứ tự trên database mới; không chạy lại migration đã áp dụng.
- Chỉ chạy migration `006` trên database chưa có cột `gia_ve_giuong_nam`; kiểm tra schema trước khi chạy.
- Migration `007` yêu cầu bảng `dat_ve` đã tồn tại. Migration `008` yêu cầu bảng `tuyen_dung` đã tồn tại.
- Không commit thông tin xác thực thật.
- Production cần tài khoản database có quyền tối thiểu cần thiết, không dùng mặc định XAMPP.
