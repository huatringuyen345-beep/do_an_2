-- Migration 005: content and customer support tables
-- Run after 004_services.sql; account tables from 001_accounts.sql are required.

CREATE TABLE `hoi_dap` (
  `ma_faq` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cau_hoi` varchar(255) NOT NULL,
  `cau_tra_loi` text NOT NULL,
  `thu_tu` int(11) NOT NULL DEFAULT 0,
  `trang_thai` enum('hien_thi','an') NOT NULL DEFAULT 'hien_thi',
  PRIMARY KEY (`ma_faq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lien_he` (
  `ma_lien_he` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_nguoi_dung` bigint(20) UNSIGNED DEFAULT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `noi_dung` text NOT NULL,
  `trang_thai` enum('moi','dang_xu_ly','da_phan_hoi','dong') NOT NULL DEFAULT 'moi',
  `noi_dung_phan_hoi` text DEFAULT NULL,
  `ma_nguoi_xu_ly` bigint(20) UNSIGNED DEFAULT NULL,
  `ngay_gui` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_phan_hoi` datetime DEFAULT NULL,
  PRIMARY KEY (`ma_lien_he`),
  KEY `idx_lien_he_nguoi_dung` (`ma_nguoi_dung`),
  KEY `idx_lien_he_nguoi_xu_ly` (`ma_nguoi_xu_ly`),
  CONSTRAINT `fk_lien_he_nguoi_dung_005`
    FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_lien_he_nguoi_xu_ly_005`
    FOREIGN KEY (`ma_nguoi_xu_ly`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tin_tuc` (
  `ma_tin_tuc` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_nguoi_dang` bigint(20) UNSIGNED DEFAULT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `loai_tin` enum('tin_tuc','su_kien','thong_bao','khuyen_mai') NOT NULL DEFAULT 'tin_tuc',
  `trang_thai` enum('ban_nhap','da_dang','an') NOT NULL DEFAULT 'ban_nhap',
  `luot_xem` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ngay_dang` datetime DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_tin_tuc`),
  KEY `idx_tin_tuc_nguoi_dang` (`ma_nguoi_dang`),
  CONSTRAINT `fk_tin_tuc_nguoi_dang_005`
    FOREIGN KEY (`ma_nguoi_dang`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tuyen_dung` (
  `ma_tuyen_dung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `ten_vi_tri` varchar(150) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `dia_diem` varchar(150) NOT NULL,
  `muc_luong` varchar(100) DEFAULT NULL,
  `mo_ta` text NOT NULL,
  `yeu_cau` text NOT NULL,
  `trang_thai` enum('mo_tuyen','dong_tuyen') NOT NULL DEFAULT 'mo_tuyen',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_tuyen_dung`),
  UNIQUE KEY `uk_tuyen_dung_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hoi_dap` (`cau_hoi`, `cau_tra_loi`, `thu_tu`, `trang_thai`) VALUES
('Tôi cần đến sớm bao lâu trước giờ xe chạy?', 'Quý khách nên có mặt tại điểm đón trước giờ khởi hành 20 đến 30 phút để làm thủ tục và gửi hành lý.', 1, 'hien_thi'),
('Xe ghế ngồi và giường nằm khác nhau thế nào?', 'Xe ghế ngồi phù hợp các tuyến ngắn trong khu vực miền Tây. Xe giường nằm có hai tầng, phù hợp các tuyến dài đi TP. Hồ Chí Minh, Đà Lạt, Nha Trang.', 2, 'hien_thi'),
('Tôi có thể đổi hoặc hủy vé không?', 'Quý khách có thể đổi hoặc hủy vé trước giờ xe chạy. Mức phí phụ thuộc thời điểm hủy, vui lòng xem chính sách hoặc gọi hotline.', 3, 'hien_thi'),
('Trẻ em có phải mua vé không?', 'Trẻ em dưới 5 tuổi ngồi cùng người lớn được miễn vé. Từ 5 tuổi trở lên mua vé như người lớn.', 4, 'hien_thi'),
('Hành lý được mang theo bao nhiêu?', 'Mỗi khách được mang theo hành lý tối đa 20 kg miễn cước. Phần vượt quá tính theo bảng cước gửi hàng.', 5, 'hien_thi'),
('Tôi muốn gửi hàng thì làm thế nào?', 'Mang hàng đến văn phòng Tài Nguyên hoặc gọi hotline để xe trung chuyển đến lấy. Xem thêm tại trang Dịch vụ.', 6, 'hien_thi');

INSERT INTO `lien_he`
  (`ho_ten`, `email`, `tieu_de`, `noi_dung`, `trang_thai`)
VALUES
  ('Khách hàng mẫu', NULL, 'Hỏi về dịch vụ xe',
   'Đây là nội dung liên hệ mẫu để kiểm tra màn hình quản lý liên hệ.', 'moi');

INSERT INTO `tin_tuc`
  (`tieu_de`, `slug`, `tom_tat`, `noi_dung`, `loai_tin`, `trang_thai`, `luot_xem`, `ngay_dang`, `ngay_ket_thuc`)
VALUES
('Mở rộng tuyến xe Miền Nam 2026', 'mo-rong-tuyen-xe-mien-nam',
 'Tăng cường xe chất lượng cao các tỉnh Tây Nam Bộ.',
 '<p>Phục vụ bà con các tỉnh Đồng bằng sông Cửu Long với các dòng xe chất lượng cao.</p>',
 'tin_tuc', 'da_dang', 50, '2026-10-24 08:00:00', NULL),
('Giảm 10% vé giường nằm đi TP. Hồ Chí Minh khi đặt online', 'giam-10-ve-giuong-nam-di-tp-ho-chi-minh',
 'Ưu đãi áp dụng cho một số chuyến đặt trước trên website.',
 '<p>Chương trình khuyến mãi mẫu. Vui lòng xác nhận thời hạn và điều kiện áp dụng trước khi đăng công khai.</p>',
 'khuyen_mai', 'ban_nhap', 0, NULL, '2026-11-30'),
('Tăng chuyến đi Đà Lạt và Nha Trang dịp cuối năm', 'tang-chuyen-di-da-lat-nha-trang',
 'Bổ sung thêm giờ chạy buổi tối cho hai tuyến đường dài.',
 '<p>Lịch chạy chi tiết được cập nhật trên trang Lịch trình. Quý khách nên đặt vé sớm để chọn được chỗ mong muốn.</p>',
 'thong_bao', 'da_dang', 0, '2026-10-03 08:00:00', NULL),
('Xe trung chuyển đón khách tận nơi trong nội ô Bạc Liêu', 'xe-trung-chuyen-don-khach-tan-noi',
 'Khách đặt vé online có thể đăng ký đón tại nhà.',
 '<p>Nhân viên sẽ gọi xác nhận thời gian đón trước giờ xe chạy. Dịch vụ áp dụng trong khu vực được nhà xe phục vụ.</p>',
 'tin_tuc', 'da_dang', 0, '2026-09-28 08:00:00', NULL),
('Tuyển lái xe và nhân viên phòng vé', 'tuyen-lai-xe-nhan-vien-phong-ve',
 'Tài Nguyên đang tuyển nhân sự cho các tuyến và văn phòng.',
 '<p>Công ty đang tuyển lái xe và nhân viên phòng vé. Xem chi tiết vị trí tại trang Tuyển dụng.</p>',
 'thong_bao', 'da_dang', 0, '2026-09-20 08:00:00', NULL),
('Ưu đãi cước gửi hàng cho khách thường xuyên', 'uu-dai-cuoc-gui-hang',
 'Chính sách cước ưu đãi dành cho khách gửi hàng thường xuyên.',
 '<p>Vui lòng liên hệ phòng điều hành để được tư vấn bảng giá và điều kiện áp dụng.</p>',
 'khuyen_mai', 'da_dang', 0, '2026-09-12 08:00:00', NULL),
('Hướng dẫn đặt vé online trong 3 bước', 'huong-dan-dat-ve-online',
 'Chọn chuyến, chọn chỗ và điền thông tin hành khách.',
 '<p>Vào trang Đặt vé, chọn tuyến, ngày đi và giờ khởi hành. Chọn chỗ, điền thông tin hành khách và xác nhận yêu cầu đặt vé.</p>',
 'su_kien', 'da_dang', 0, '2026-08-30 08:00:00', NULL);

INSERT INTO `tuyen_dung`
  (`slug`, `ten_vi_tri`, `so_luong`, `dia_diem`, `muc_luong`, `mo_ta`, `yeu_cau`, `trang_thai`)
VALUES
('lai-xe', 'Lái xe khách', 4, 'Bạc Liêu', 'Từ 12 – 18 triệu',
 'Điều khiển xe khách các tuyến đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu và Đà Lạt.',
 '["Bằng lái hạng D còn hạn", "Từ 3 năm kinh nghiệm lái xe khách", "Sức khỏe tốt, có thể đi tuyến đường dài"]', 'mo_tuyen'),
('phu-xe', 'Phụ xe', 3, 'Bạc Liêu', 'Từ 7 – 10 triệu',
 'Hỗ trợ khách lên xuống, sắp xếp hành lý và hàng hóa gửi theo xe.',
 '["Nhanh nhẹn, giao tiếp lịch sự", "Chấp nhận đi tuyến dài ngày", "Ưu tiên có kinh nghiệm"]', 'mo_tuyen'),
('phong-ve', 'Nhân viên phòng vé', 2, 'Bạc Liêu', 'Từ 8 – 10 triệu',
 'Bán vé, tư vấn tuyến và giờ chạy, tiếp nhận hàng gửi tại văn phòng.',
 '["Tốt nghiệp trung cấp trở lên", "Sử dụng thành thạo máy tính", "Có thể làm theo ca"]', 'mo_tuyen'),
('web', 'Nhân viên chăm sóc khách hàng online', 1, 'Bạc Liêu', 'Thỏa thuận',
 'Trả lời khách qua điện thoại và website, xác nhận vé đặt online.',
 '["Giọng nói rõ ràng, kiên nhẫn", "Biết sử dụng máy tính văn phòng", "Ưu tiên người biết quản trị website"]', 'mo_tuyen');
