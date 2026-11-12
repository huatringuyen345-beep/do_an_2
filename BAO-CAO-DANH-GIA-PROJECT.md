# Báo cáo đánh giá website Tài Nguyên

**Phạm vi:** Website khách hàng (`Website_Customer`), khu vực quản lý (`Website_Manage`) và schema/migration trong `database`.

**Mục đích:** Tóm tắt những phần đã triển khai, phân loại theo quản trị viên, nhân viên và khách hàng; chỉ rõ phần nào đang dùng cơ sở dữ liệu, phần nào chỉ là giao diện/dữ liệu mẫu và các hạng mục cần hoàn thiện.

> Đánh giá dựa trên mã nguồn và schema hiện có. Chưa chạy website với một cơ sở dữ liệu đang hoạt động, nên không khẳng định cấu hình môi trường hiện tại đã chạy được. Dữ liệu tuyến, giá, giờ chạy và nội dung mẫu cần được doanh nghiệp xác minh trước khi dùng thật.

## 1. Kết luận chung

Project hiện có nền tảng website PHP tương đối đầy đủ cho demo và quy trình vận hành cơ bản:

- Có giao diện nhiều trang cho khách hàng, tài khoản khách hàng và khu vực quản lý.
- Có đăng nhập, phân quyền theo vai trò ở mức khu vực (khách hàng so với nhân viên/quản trị), kiểm tra CSRF ở các thao tác quản trị và tài khoản.
- Có schema MySQL cho tài khoản, tuyến, xe, chuyến xe, ghế, đặt vé, thanh toán, dịch vụ, liên hệ, tin tức, FAQ và tuyển dụng.
- Khu quản lý đã đọc/ghi cơ sở dữ liệu để xử lý đặt vé, chuyến xe, đội xe/tuyến, đơn dịch vụ và liên hệ.
- Khách hàng có thể gửi yêu cầu đặt vé và tin nhắn liên hệ; một số thông tin tài khoản và lịch sử vé được đọc từ CSDL.

Tuy vậy, các luồng cốt lõi chưa khép kín:

1. **Đặt vé từ website chưa đặt chỗ trên một chuyến xe cụ thể.** Danh sách tuyến, giá cơ bản và giờ khởi hành phía khách hàng được đọc từ CSDL, nhưng chưa lấy chuyến chạy theo ngày từ `chuyen_xe`; sơ đồ ghế vẫn được tạo cố định ở giao diện. Yêu cầu chỉ ghi vào bảng `dat_ve`, thông tin ghế/chuyến được gói trong ghi chú. Chưa tạo `chi_tiet_ve`/`thanh_toan` cho yêu cầu mới và chưa kiểm tra ghế còn trống.
2. **Khách hàng chưa gửi đơn dịch vụ hoặc hồ sơ ứng tuyển thực sự.** Trang dịch vụ là nội dung giới thiệu/ước tính; form tuyển dụng chỉ kiểm tra rồi báo nhận hồ sơ, không lưu dữ liệu.
3. **Quản trị nội dung công khai còn thiếu.** Tuyến được đọc từ CSDL; tin tức, FAQ và vị trí tuyển dụng cũng được nạp từ CSDL ở phía khách hàng. Tuy nhiên, chưa thấy trang quản trị để cập nhật các nội dung này; một số nội dung như dịch vụ và thông tin công ty vẫn khai báo tĩnh.
4. **Admin và nhân viên dùng chung quyền thao tác.** Tài khoản cả hai vai trò vào cùng khu quản lý, chưa có phân quyền chi tiết theo nghiệp vụ.
5. **Còn vấn đề triển khai trước production:** một số form thiếu CSRF, phản hồi liên hệ có thể báo thành công dù ghi thất bại, cấu hình thông tin kết nối/hotline còn là mẫu, và cần kiểm thử các luồng với database thật.

## 2. Quản trị viên (admin)

### Đã có

- Đăng nhập bằng tài khoản có vai trò `quan_tri`; không có luồng tự đăng ký tài khoản quản lý.
- Dùng chung khu vực nghiệp vụ với nhân viên.
- Có thể vào các trang quản lý:
  - **Đặt vé:** tìm kiếm/lọc đơn, xem thông tin đặt vé, tuyến/chuyến/ghế và dữ liệu thanh toán nếu có; cập nhật trạng thái đơn. Trạng thái đơn được đồng bộ sang các vé chi tiết trong giao dịch CSDL.
  - **Chuyến xe:** tạo/sửa chuyến, chọn tuyến/xe/tài xế, đặt giờ đi/đến, giá vé và trạng thái chuyến; kiểm tra tuyến, xe, tài xế đang hoạt động.
  - **Tuyến và đội xe:** thêm/sửa điểm dừng, tuyến, xe và ghế; cập nhật trạng thái xe/tuyến và quản lý sơ đồ ghế.
  - **Đơn dịch vụ:** tìm/lọc đơn, cập nhật trạng thái đơn và trạng thái vận đơn hàng hóa.
  - **Liên hệ:** xem yêu cầu, ghi nội dung phản hồi, lưu người xử lý và trạng thái.
- Có thông tin hồ sơ tài khoản quản lý dạng chỉ đọc.
- Các thao tác ghi trong khu quản lý có kiểm tra CSRF; trang quản trị kiểm tra lại vai trò và trạng thái tài khoản từ CSDL.

### Còn thiếu / cần làm rõ

- **Quản lý người dùng và phân quyền:** chưa thấy giao diện tạo/sửa/khóa tài khoản, cấp vai trò, đặt lại mật khẩu nhân viên/tài xế hoặc phân quyền theo từng chức năng. Hướng dẫn hiện mô tả cấp tài khoản qua database.
- **Tách quyền admin với nhân viên:** hiện cả `quan_tri` và `nhan_vien` đều vượt qua cùng điều kiện truy cập và thấy cùng menu. Nhân viên có thể truy cập các màn hình tuyến/đội xe/chuyến xe như admin; chưa có quy tắc “admin-only”.
- **Quản trị nội dung website:** chưa thấy màn hình CRUD cho tin tức, khuyến mãi, FAQ, vị trí tuyển dụng, dịch vụ và thông tin công ty. Phía khách hàng đã đọc tin tức, FAQ và vị trí tuyển dụng từ CSDL, nhưng chưa có giao diện quản trị các nội dung đó; dịch vụ và thông tin công ty vẫn có nội dung tĩnh.
- **Thanh toán/đối soát:** trang quản lý đặt vé có thể hiển thị giao dịch đã tồn tại, nhưng chưa thấy luồng tạo giao dịch thanh toán từ yêu cầu đặt vé của khách, đối soát với ngân hàng/cổng thanh toán hoặc hoàn tiền tự động.
- **Báo cáo vận hành:** chưa thấy báo cáo doanh thu, số vé theo tuyến/thời gian, công nợ, tỷ lệ hủy, hiệu suất xe/tài xế hoặc xuất Excel/CSV.
- **Nhật ký kiểm toán:** chưa thấy lịch sử ai sửa trạng thái nào, thời điểm nào, giá trị trước/sau; thông tin người xử lý hiện có ở liên hệ nhưng chưa phải audit log tổng quát.
- **Vận hành dữ liệu:** cần quy trình sao lưu/khôi phục, migration an toàn và quản lý cấu hình production; không nên phụ thuộc tài khoản DB mẫu hay import lại SQL demo.

## 3. Nhân viên

### Đã có

- Đăng nhập bằng tài khoản `nhan_vien` được cấp sẵn; không tự đăng ký tài khoản quản lý.
- Vào cùng khu quản lý như admin để thực hiện các nghiệp vụ hiện có:
  - tra cứu và cập nhật trạng thái đặt vé;
  - xem/chỉnh chuyến xe, tuyến, xe và ghế;
  - cập nhật đơn dịch vụ/vận đơn;
  - xử lý liên hệ và lưu nội dung phản hồi.
- Tài khoản phải đang hoạt động; quyền khu quản lý được xác thực lại từ CSDL.
- Có trang xem hồ sơ cá nhân; đăng xuất qua POST và token CSRF dùng chung.

### Còn thiếu / cần làm rõ

- **Phân quyền công việc:** chưa có giới hạn để nhân viên chỉ xử lý đặt vé/liên hệ mà không sửa tuyến, giá, đội xe hay cấu hình chuyến. Cần xác định vai trò từng nhóm nhân viên trước khi áp dụng thật.
- **Quản lý ca và phân công:** chưa thấy phân công đơn đặt vé/liên hệ/đơn dịch vụ theo nhân viên, trạng thái “đang xử lý bởi tôi”, SLA hoặc nhắc việc.
- **Phản hồi khách qua hệ thống:** nội dung phản hồi liên hệ được lưu nhưng chưa thấy gửi email/SMS/thông báo cho khách. Khách cũng chưa có trang xem nội dung phản hồi.
- **Tác nghiệp đặt vé đầy đủ:** do khách chưa tạo chi tiết vé gắn với chuyến/ghế, nhân viên chưa có quy trình xác nhận chỗ thật, giữ ghế có thời hạn, đổi ghế/chuyến hoặc xử lý xung đột ghế từ luồng khách.
- **Thu/hoàn tiền:** chưa có luồng nghiệp vụ đầy đủ để ghi nhận thu tiền, xác nhận thanh toán, hoàn tiền và lưu bằng chứng/giao dịch.
- **Tài khoản và bảo mật vận hành:** khu quản lý có trang hồ sơ chỉ đọc; chưa thấy chức năng đổi mật khẩu quản lý trong khu này, khóa phiên khác hoặc ghi nhận lịch sử đăng nhập.

## 4. Khách hàng

### Đã có

- **Trang thông tin công khai:** trang chủ, giới thiệu, lịch trình, dịch vụ, tin tức, hỗ trợ, liên hệ và tuyển dụng.
- **Tài khoản khách hàng:** đăng ký, đăng nhập, đăng xuất; mật khẩu được băm; có thể cập nhật họ tên/số điện thoại/địa chỉ và đổi mật khẩu.
- **Đặt vé ở mức gửi yêu cầu:** chọn tuyến, chiều đi/về, ngày, giờ, loại xe, số lượng/chỗ mong muốn, thông tin khách, phương thức thanh toán dự kiến và địa chỉ đón trung chuyển; có kiểm tra dữ liệu cơ bản.
- **Lịch sử vé:** trang tài khoản đọc các đơn có liên kết tài khoản hoặc email khách hàng từ bảng `dat_ve`.
- **Liên hệ:** biểu mẫu kiểm tra tên, điện thoại, email và nội dung; có thể ghi vào bảng `lien_he`.
- Có nội dung hướng dẫn về thanh toán, chính sách; tin tức, FAQ và vị trí tuyển dụng được nạp từ CSDL. Một số nội dung giới thiệu/dịch vụ vẫn tĩnh hoặc mẫu và chưa có giao diện quản trị nội dung.

### Còn thiếu / cần làm

- **Chọn chuyến có dữ liệu thực:** trang đặt vé cần lấy chuyến theo ngày từ `chuyen_xe`, tuyến/giờ từ CSDL, và chỉ hiển thị chuyến mở bán.
- **Sơ đồ ghế và giữ ghế thực tế:** tải ghế theo xe, hiển thị ghế đã giữ/đã bán, kiểm tra lại ở server khi đặt và ngăn hai người lấy cùng ghế.
- **Lưu đầy đủ vé:** khi đặt cần tạo đơn và chi tiết vé gắn với chuyến, ghế, điểm đón/trả, giá tại thời điểm đặt; hiện chỉ có dòng đơn `dat_ve` và ghi chú văn bản.
- **Theo dõi/cập nhật đặt chỗ:** cần màn hình trạng thái đáng tin cậy, mã vé, thông tin chuyến/ghế có cấu trúc, lịch sử trạng thái; cân nhắc chức năng hủy/đổi theo chính sách.
- **Thanh toán trực tuyến hoặc hướng dẫn thanh toán hoàn chỉnh:** hiện phương thức chỉ là lựa chọn dự kiến; chưa tạo bản ghi thanh toán hoặc kết nối cổng. Cần thông báo rõ đơn đang chờ xác nhận và không thể hiện như vé đã thanh toán.
- **Đặt dịch vụ:** chưa có form đặt gửi hàng/trung chuyển để tạo `dat_dich_vu` và `chi_tiet_hang`, theo dõi mã vận đơn hoặc xem lịch sử dịch vụ.
- **Gửi hồ sơ ứng tuyển:** form chưa lưu vào CSDL, không có bảng hồ sơ ứng tuyển; kết quả “đã nhận hồ sơ” hiện chỉ dựa trên kiểm tra form.
- **Tin nhắn hỗ trợ:** khách chưa gửi được phản hồi tiếp theo hoặc xem trạng thái/nội dung trả lời từ trang tài khoản.
- **CSRF và chống gửi lặp:** cần token CSRF cho form đặt vé, liên hệ và ứng tuyển; thêm cơ chế chống gửi lặp khi người dùng tải lại trang hoặc nhấn gửi nhiều lần.
- **Thông tin cá nhân:** cần kiểm tra quyền riêng tư và quy tắc tra cứu vé bằng email; tránh để việc dùng cùng email làm lộ thông tin đơn của người khác.

## 5. Đánh giá cơ sở dữ liệu và mức độ tích hợp

### Schema đã có

Các migration định nghĩa được những nhóm dữ liệu chính:

- `vai_tro`, `nguoi_dung`: tài khoản và vai trò.
- `diem_dung`, `tuyen_duong`, `loai_xe`, `xe`, `ghe_xe`, `gio_khoi_hanh_tuyen`, `chuyen_xe`: tuyến, đội xe và chuyến.
- `dat_ve`, `chi_tiet_ve`, `thanh_toan`, `khuyen_mai`, `phuong_thuc_thanh_toan`: đặt vé và thanh toán.
- `dich_vu`, `dat_dich_vu`, `chi_tiet_hang`: dịch vụ và vận đơn hàng hóa.
- `lien_he`, `tin_tuc`, `hoi_dap`, `tuyen_dung`: hỗ trợ và nội dung.

### Những điểm chưa đồng bộ với luồng khách

- Khu quản lý dùng CSDL cho chuyến/xe/đơn; phía khách cũng nạp danh sách tuyến, giá cơ bản và giờ khởi hành từ `backend/data/routes.php`. Tuy nhiên, trang đặt vé chưa lấy chuyến cụ thể theo ngày từ `chuyen_xe`; sơ đồ ghế vẫn là dữ liệu dựng cố định ở giao diện.
- Form đặt vé chỉ ghi vào `dat_ve`; chưa nối với `chuyen_xe`, `ghe_xe`, `chi_tiet_ve` và `thanh_toan`.
- Các bảng `dich_vu`/`dat_dich_vu`/`chi_tiet_hang` có schema và trang quản lý đơn, nhưng chưa thấy form phía khách tạo dữ liệu đơn mới.
- Bảng `tin_tuc`, `hoi_dap`, `tuyen_dung` có schema/seed và nội dung khách hàng được nạp từ các bảng này qua `backend/data/customer-content.php`; hiện chưa thấy giao diện quản trị tương ứng. Một số nội dung công khai khác như dịch vụ/thông tin công ty vẫn khai báo tĩnh.
- Chưa có bảng hồ sơ ứng tuyển; cần thiết kế trước khi triển khai nhận CV/tệp đính kèm.

## 6. Các vấn đề cần xử lý trước khi đưa vào sử dụng thật

1. **Sửa phản hồi liên hệ:** hiện controller đặt trạng thái “đã gửi” trước khi xác nhận ghi CSDL; nếu lỗi ghi, khách vẫn có thể nhận thông báo thành công. Chỉ báo thành công sau khi insert thành công, nếu không thì hiển thị lỗi rõ ràng.
2. **Bổ sung CSRF cho form nghiệp vụ khách:** tối thiểu đặt vé, liên hệ và ứng tuyển.
3. **Rà soát cấu hình database:** thông tin kết nối hiện khai báo trực tiếp trong file; dùng cấu hình môi trường ngoài mã nguồn, hạn chế quyền tài khoản CSDL và không để lộ thông tin thật.
4. **Thay dữ liệu mẫu:** xác nhận tuyến, giá, giờ, địa chỉ, hotline, email, khuyến mãi, chính sách và tài khoản demo.
5. **Kiểm thử liên kết session và quyền:** xác nhận nhân viên/quản trị đăng nhập, đăng xuất, bị khóa tài khoản và mất vai trò thì không thể tiếp tục truy cập khu quản lý.
6. **Kiểm thử lỗi CSDL:** mọi thao tác ghi phải có thông báo đúng khi mất kết nối, dữ liệu sai hoặc trùng; không báo hoàn tất nếu không ghi được.
7. **Kiểm tra quy trình sao lưu/migration:** sao lưu trước khi chạy SQL; không import lại file khởi tạo lên CSDL đã có dữ liệu.

## 7. Thứ tự ưu tiên đề xuất

### Ưu tiên 1 — Hoàn thiện nghiệp vụ cốt lõi

- Chốt mô hình chuyến/ngày/ghế và nguồn dữ liệu tuyến thống nhất.
- Nối form đặt vé với chuyến thật; tạo đơn + chi tiết vé trong transaction; khóa/kiểm tra ghế chống trùng.
- Sửa kết quả gửi liên hệ và bổ sung CSRF cho các biểu mẫu POST phía khách.

### Ưu tiên 2 — Hoàn thiện quy trình khách hàng

- Tạo lưu trữ hồ sơ ứng tuyển.
- Tạo form đặt dịch vụ và theo dõi đơn/vận đơn.
- Hoàn thiện tra cứu vé, thông báo xác nhận và chính sách đổi/hủy.
- Tích hợp thanh toán nếu doanh nghiệp cần thu tiền online; nếu chưa tích hợp, thể hiện rõ là yêu cầu chờ nhân viên xác nhận.

### Ưu tiên 3 — Hoàn thiện công cụ vận hành

- Tách quyền admin/nhân viên theo nghiệp vụ; thêm quản lý tài khoản và vai trò.
- Thêm phân công xử lý, lịch sử cập nhật/audit log và thông báo cho khách.
- Chuyển tin tức, FAQ, tuyển dụng, dịch vụ và thông tin tuyến sang nguồn quản trị có kiểm soát.
- Bổ sung báo cáo, sao lưu, logging và kiểm thử nghiệp vụ.

## 8. Các tài liệu/mã nguồn liên quan

- Hướng dẫn khách hàng: [docs/customer-guide.txt](./docs/customer-guide.txt)
- Hướng dẫn admin: [docs/admin-guide.txt](./docs/admin-guide.txt)
- Backend khách hàng: [Website_Customer/backend/](./Website_Customer/backend/)
- Khu quản lý: [Website_Manage/admin/](./Website_Manage/admin/)
- Schema và migrations: [database/migrations/](./database/migrations/)
