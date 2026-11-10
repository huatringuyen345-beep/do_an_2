# Vai trò và quyền truy cập

## Vai trò database

| Vai trò | Mục đích |
| --- | --- |
| `quan_tri` | Quản trị viên |
| `nhan_vien` | Nhân viên vận hành |
| `khach_hang` | Khách hàng đăng ký |
| `tai_xe` | Tài xế |

## Hiện trạng truy cập

- Khách có thể xem các trang thông tin; thao tác đặt vé, liên hệ và ứng tuyển yêu cầu đăng nhập tài khoản khách hàng.
- Khu vực quản lý kiểm tra tài khoản còn hoạt động và chỉ cho phép vai trò `quan_tri` hoặc `nhan_vien`.
- Admin và nhân viên hiện dùng chung quyền thao tác trên các module quản lý; chưa có phân quyền chi tiết theo nghiệp vụ.
- Tài khoản quản lý được cấp bên ngoài luồng đăng ký khách hàng. Không tự nâng quyền khách hàng thành admin.
- Form POST trong khu quản lý yêu cầu token CSRF. Đăng xuất dùng endpoint POST có kiểm tra CSRF.

## Khuyến nghị vận hành

Tạo tài khoản riêng cho từng nhân viên, dùng mật khẩu băm bằng `password_hash()`, thu hồi quyền khi nhân sự thay đổi và không dùng tài khoản mẫu trên môi trường công khai. Cần xác định ma trận quyền chi tiết trước khi tách quyền admin/nhân viên.
