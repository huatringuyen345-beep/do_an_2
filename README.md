# Website nhà xe Tài Nguyên

Ứng dụng PHP thuần gồm website khách hàng và khu vực quản lý nội bộ, sử dụng chung cơ sở dữ liệu MySQL/MariaDB.

## Cấu trúc dự án

```text
Website_TaiNguyen/
├── Website_Customer/
│   ├── backend/
│   │   ├── controllers/       # Bộ điều khiển PHP hiện có
│   │   ├── helpers/
│   │   ├── middleware/
│   │   ├── models/
│   │   └── services/
│   ├── pages/                 # Trang khách hàng
│   ├── assets/                # CSS, JavaScript, hình ảnh
│   ├── includes/              # Header, footer và phần giao diện dùng chung
├── Website_Manage/
│   ├── admin/
│   │   ├── controllers/
│   │   ├── middleware/
│   │   ├── models/
│   │   ├── services/
│   │   └── modules/           # bookings, users, services, trips, fleet, support...
│   ├── assets/
│   ├── includes/
│   └── config/
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── website_tainguyen.sql
├── storage/
│   ├── uploads/
│   ├── logs/
│   └── backups/
├── docs/
│   ├── architecture.md
│   ├── database.md
│   └── permissions.md
├── .env.example
├── .gitignore
└── README.md
```

Các thư mục `models`, `services`, `middleware`, `helpers` và một số module đã được tạo làm ranh giới tổ chức mã. Chỉ các luồng hiện có mới được chuyển vào đó; chưa tạo bộ điều khiển giả cho thanh toán hay đơn dịch vụ phía khách hàng.

## Chạy trên XAMPP

1. Đặt project trong `C:\xampp\htdocs\Website_TaiNguyen`.
2. Khởi động **Apache** và **MySQL** trong XAMPP.
3. Tạo database `website_tainguyen` bằng `database/website_tainguyen.sql`, hoặc chạy các file trong `database/migrations/` theo thứ tự. Nếu dùng migrations trên database local, có thể chạy thêm `database/seeds/001_demo_admin.sql` để tạo tài khoản quản trị demo nếu tài khoản chưa có.
4. Cấu hình các biến môi trường PHP/Apache theo `.env.example`. Ứng dụng đọc trực tiếp biến môi trường; `.env.example` là mẫu, không tự động được nạp.
5. Mở trang khách hàng:

   `http://localhost/Website_TaiNguyen/Website_Customer/`

   Đăng nhập nhân viên/quản trị:

   `http://localhost/Website_TaiNguyen/Website_Customer/pages/Login.php?area=staff`

Không import lại schema hoặc chạy lại migration trên database đang có dữ liệu. Sao lưu database trước khi thay đổi.
Tài khoản quản trị demo chỉ dùng local; đổi mật khẩu ngay sau lần đăng nhập đầu và không dùng trên môi trường công khai.

## Tài liệu

- [Kiến trúc dự án](./docs/architecture.md)
- [Cơ sở dữ liệu](./docs/database.md)
- [Vai trò và quyền truy cập](./docs/permissions.md)
- [Hướng dẫn nhanh](./docs/quick-start.txt)
- [Hướng dẫn khách hàng](./docs/customer-guide.txt)
- [Hướng dẫn quản trị viên](./docs/admin-guide.txt)
- [Báo cáo đánh giá dự án](./BAO-CAO-DANH-GIA-PROJECT.md)

## Trạng thái chức năng

Website hiện có các trang khách hàng, tài khoản, yêu cầu đặt vé, liên hệ và tuyển dụng; khu quản lý có nghiệp vụ đơn vé, chuyến xe, tuyến/đội xe, dịch vụ và liên hệ. Một số nội dung và dữ liệu tuyến còn là mẫu. Xem [báo cáo đánh giá](./BAO-CAO-DANH-GIA-PROJECT.md) trước khi triển khai thực tế.
