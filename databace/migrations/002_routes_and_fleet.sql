-- Migration 002: stops, routes, vehicles, seats and scheduled trips
-- Requires 001_accounts.sql.
--
-- Chuc nang cua giai doan:
--   * Luu diem don/diem den va thong tin tuyen xe.
--   * Quan ly loai xe, xe, danh sach ghe tung xe va gio chay tham khao.
--   * Tao cac chuyen xe cu the theo ngay gio, gan tuyen va xe; tai xe co the de trong.
--   * Du lieu INSERT ben duoi la du lieu mau de thu nghiem giao dien va truy van.
--
-- Thu tu tao bang trong file cung la thu tu phu thuoc khoa ngoai.
-- Chay sau 001_accounts.sql va truoc 003_bookings.sql.
-- File nay tao schema va du lieu mau; khong chay lai tren database da co cac bang nay.

-- Danh sach ben xe/diem don tra, dung lam diem dau/cuoi cho tuyen va ve.
CREATE TABLE `diem_dung` (
  `ma_diem_dung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_diem_dung` varchar(120) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `tinh_thanh` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ma_diem_dung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tuyen co diem di, diem den, khoang cach, thoi gian va gia ve tham khao.
CREATE TABLE `tuyen_duong` (
  `ma_tuyen` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_tuyen` varchar(150) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `nhom_tuyen` varchar(100) DEFAULT NULL,
  `ma_diem_di` int(10) UNSIGNED NOT NULL,
  `ma_diem_den` int(10) UNSIGNED NOT NULL,
  `khoang_cach_km` decimal(8,2) DEFAULT NULL,
  `thoi_gian_phut` int(10) UNSIGNED DEFAULT NULL,
  `gia_ve_co_ban` decimal(12,2) NOT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong',
  PRIMARY KEY (`ma_tuyen`),
  UNIQUE KEY `uk_tuyen_slug` (`slug`),
  KEY `idx_tuyen_diem_di` (`ma_diem_di`),
  KEY `idx_tuyen_diem_den` (`ma_diem_den`),
  CONSTRAINT `fk_tuyen_diem_di_002`
    FOREIGN KEY (`ma_diem_di`) REFERENCES `diem_dung` (`ma_diem_dung`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tuyen_diem_den_002`
    FOREIGN KEY (`ma_diem_den`) REFERENCES `diem_dung` (`ma_diem_dung`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mau phuong tien va suc chua; duoc xe tham chieu.
CREATE TABLE `loai_xe` (
  `ma_loai_xe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_loai_xe` varchar(100) NOT NULL,
  `so_ghe` int(10) UNSIGNED NOT NULL,
  `mo_ta` text DEFAULT NULL,
  PRIMARY KEY (`ma_loai_xe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Xe cu the trong doi xe; bien so khong duoc trung.
CREATE TABLE `xe` (
  `ma_xe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_loai_xe` int(10) UNSIGNED NOT NULL,
  `bien_so` varchar(20) NOT NULL,
  `ten_xe` varchar(100) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','bao_tri','ngung_hoat_dong') NOT NULL DEFAULT 'hoat_dong',
  PRIMARY KEY (`ma_xe`),
  UNIQUE KEY `uk_xe_bien_so` (`bien_so`),
  KEY `idx_xe_loai` (`ma_loai_xe`),
  CONSTRAINT `fk_xe_loai_002`
    FOREIGN KEY (`ma_loai_xe`) REFERENCES `loai_xe` (`ma_loai_xe`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So do ghe cua tung xe. So ghe chi duy nhat trong pham vi mot xe.
CREATE TABLE `ghe_xe` (
  `ma_ghe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_xe` int(10) UNSIGNED NOT NULL,
  `so_ghe` varchar(10) NOT NULL,
  `tang` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`ma_ghe`),
  UNIQUE KEY `uk_ghe_xe_so_ghe` (`ma_xe`, `so_ghe`),
  CONSTRAINT `fk_ghe_xe_002`
    FOREIGN KEY (`ma_xe`) REFERENCES `xe` (`ma_xe`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cac gio chay tham khao lap lai theo tuyen; khong phai chuyen xe theo ngay.
CREATE TABLE `gio_khoi_hanh_tuyen` (
  `ma_gio_khoi_hanh` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_tuyen` int(10) UNSIGNED NOT NULL,
  `gio_chay` time NOT NULL,
  PRIMARY KEY (`ma_gio_khoi_hanh`),
  UNIQUE KEY `uk_gio_tuyen` (`ma_tuyen`, `gio_chay`),
  CONSTRAINT `fk_gio_chay_tuyen_002`
    FOREIGN KEY (`ma_tuyen`) REFERENCES `tuyen_duong` (`ma_tuyen`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chuyen xe cu the co ngay gio khoi hanh, xe, tuyen va gia.
-- Bang nay duoc 003_bookings.sql tham chieu khi tao chi tiet ve.
CREATE TABLE `chuyen_xe` (
  `ma_chuyen` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_tuyen` int(10) UNSIGNED NOT NULL,
  `ma_xe` int(10) UNSIGNED NOT NULL,
  `ma_tai_xe` bigint(20) UNSIGNED DEFAULT NULL,
  `gio_khoi_hanh` datetime NOT NULL,
  `gio_den_du_kien` datetime DEFAULT NULL,
  `gia_ve` decimal(12,2) NOT NULL,
  `trang_thai` enum('mo_ban','het_cho','dang_chay','hoan_thanh','da_huy') NOT NULL DEFAULT 'mo_ban',
  PRIMARY KEY (`ma_chuyen`),
  KEY `idx_chuyen_tuyen` (`ma_tuyen`),
  KEY `idx_chuyen_xe` (`ma_xe`),
  KEY `idx_chuyen_tai_xe` (`ma_tai_xe`),
  KEY `idx_chuyen_gio_khoi_hanh` (`gio_khoi_hanh`),
  CONSTRAINT `fk_chuyen_tuyen_002`
    FOREIGN KEY (`ma_tuyen`) REFERENCES `tuyen_duong` (`ma_tuyen`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_chuyen_xe_002`
    FOREIGN KEY (`ma_xe`) REFERENCES `xe` (`ma_xe`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_chuyen_tai_xe_002`
    FOREIGN KEY (`ma_tai_xe`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Du lieu mau diem dung. ID duoc khai bao ro de cac tuyen mau tham chieu duoc.
INSERT INTO `diem_dung` (`ma_diem_dung`, `ten_diem_dung`, `dia_chi`, `tinh_thanh`, `so_dien_thoai`) VALUES
(1, 'Bến xe Miền Tây', '395 Kinh Dương Vương, An Lạc, Bình Tân', 'Hồ Chí Minh', '02838771234'),
(2, 'Bến xe Bạc Liêu', 'Quốc lộ 1A, Khóm 2, Phường 7', 'Bạc Liêu', '02913822333'),
(3, 'Bến xe Trung tâm Cần Thơ', 'Khu đô thị Nam Cần Thơ, Hưng Thạnh, Cái Răng', 'Cần Thơ', '02923911222'),
(4, 'Bến xe Cà Mau', 'Lý Thường Kiệt, Phường 6', 'Cà Mau', '02903833444'),
(5, 'Bến xe Châu Đốc', 'Tôn Đức Thắng, Phường Vĩnh Mỹ', 'An Giang', '02963866866'),
(6, 'Bến xe Rạch Giá', '206A Mạc Cửu, Phường Vĩnh Thanh', 'Kiên Giang', '02973862274'),
(7, 'Bến xe Sóc Trăng', '38 Lê Duẩn, Phường 3', 'Sóc Trăng', NULL),
(8, 'Bến xe Bình Dương', 'Đường 30/4, Chánh Nghĩa, Thủ Dầu Một', 'Bình Dương', NULL),
(9, 'Bến xe Vũng Tàu', 'Nam Kỳ Khởi Nghĩa, Phường Thắng Tam', 'Bà Rịa - Vũng Tàu', NULL),
(10, 'Bến xe Liên tỉnh Đà Lạt', '1 Tô Hiến Thành, Phường 3', 'Lâm Đồng', NULL),
(11, 'Bến xe phía Nam Nha Trang', 'Đường 23/10, Vĩnh Trung', 'Khánh Hòa', NULL);

-- Du lieu mau tuyen, tham chieu cac diem dung o tren.
INSERT INTO `tuyen_duong` (`ma_tuyen`, `ten_tuyen`, `slug`, `nhom_tuyen`, `ma_diem_di`, `ma_diem_den`, `khoang_cach_km`, `thoi_gian_phut`, `gia_ve_co_ban`, `trang_thai`) VALUES
(1, 'Sài Gòn - Bạc Liêu', 'sai-gon-bac-lieu', 'Đông Nam Bộ', 1, 2, 280.00, 360, 180000.00, 'hoat_dong'),
(2, 'Cần Thơ - Cà Mau', 'can-tho-ca-mau', 'Đồng bằng sông Cửu Long', 3, 4, 180.00, 240, 120000.00, 'hoat_dong'),
(3, 'Sài Gòn - Cần Thơ', 'sai-gon-can-tho', 'Đồng bằng sông Cửu Long', 1, 3, 170.00, 210, 150000.00, 'hoat_dong'),
(4, 'Sài Gòn - Cà Mau', 'sai-gon-ca-mau', 'Đồng bằng sông Cửu Long', 1, 4, 350.00, 480, 220000.00, 'hoat_dong'),
(5, 'Sài Gòn - Châu Đốc', 'sai-gon-chau-doc', 'Đồng bằng sông Cửu Long', 1, 5, 250.00, 360, 190000.00, 'hoat_dong'),
(6, 'Sài Gòn - Rạch Giá', 'sai-gon-rach-gia', 'Đồng bằng sông Cửu Long', 1, 6, 280.00, 360, 200000.00, 'hoat_dong'),
(7, 'Bạc Liêu - Sóc Trăng', 'soc-trang', 'Đồng bằng sông Cửu Long', 2, 7, 60.00, 90, 60000.00, 'hoat_dong'),
(8, 'Bạc Liêu - Bình Dương', 'binh-duong', 'Đông Nam Bộ', 2, 8, 320.00, 420, 230000.00, 'hoat_dong'),
(9, 'Bạc Liêu - Vũng Tàu', 'vung-tau', 'Đông Nam Bộ', 2, 9, 360.00, 480, 260000.00, 'hoat_dong'),
(10, 'Bạc Liêu - Đà Lạt', 'da-lat', 'Tây Nguyên và Nam Trung Bộ', 2, 10, 530.00, 720, 330000.00, 'hoat_dong'),
(11, 'Bạc Liêu - Nha Trang', 'nha-trang', 'Tây Nguyên và Nam Trung Bộ', 2, 11, 700.00, 900, 380000.00, 'hoat_dong');

-- Du lieu mau loai xe va xe, ghe cua chinh mot so xe mau.
INSERT INTO `loai_xe` (`ma_loai_xe`, `ten_loai_xe`, `so_ghe`, `mo_ta`) VALUES
(1, 'Xe Giường Nằm 44 Chỗ', 44, 'Xe giường nằm cao cấp có điều hòa, nước uống, wifi.'),
(2, 'Xe Ghế Ngồi 29 Chỗ', 29, 'Xe ghế ngồi êm ái, thích hợp cho các tuyến ngắn.'),
(3, 'Xe Limousine 9 Chỗ', 9, 'Xe Limousine đẳng cấp thương gia, ghế massage.'),
(4, 'Xe Giường Phòng 22 Chỗ', 22, 'Phòng nằm riêng biệt, tivi, rèm che, riêng tư.');

INSERT INTO `xe` (`ma_xe`, `ma_loai_xe`, `bien_so`, `ten_xe`, `trang_thai`) VALUES
(1, 1, '51B-123.45', 'Sleeper Sài Gòn 01', 'hoat_dong'),
(2, 1, '51B-234.56', 'Sleeper Sài Gòn 02', 'hoat_dong'),
(3, 2, '94B-678.90', 'Seat Bạc Liêu 01', 'hoat_dong'),
(4, 3, '51F-111.11', 'Limousine VIP 01', 'hoat_dong'),
(5, 3, '51F-222.22', 'Limousine VIP 02', 'hoat_dong'),
(6, 4, '65B-333.33', 'Phòng Nằm Cần Thơ 1', 'hoat_dong'),
(7, 4, '69B-444.44', 'Phòng Nằm Cà Mau 1', 'hoat_dong'),
(8, 1, '67B-555.55', 'Sleeper Châu Đốc 1', 'hoat_dong'),
(9, 2, '68B-666.66', 'Ghế Ngồi Rạch Giá 1', 'hoat_dong'),
(10, 1, '51B-999.99', 'Sleeper Sài Gòn 03', 'hoat_dong');

-- So do ghe mau; can bo sung day du so ghe khi dua vao nghiep vu dat ve that.
INSERT INTO `ghe_xe` (`ma_ghe`, `ma_xe`, `so_ghe`, `tang`) VALUES
(1, 1, 'A01', 1), (2, 1, 'A02', 1), (3, 1, 'B01', 2), (4, 1, 'B02', 2),
(5, 4, 'VIP1', 1), (6, 4, 'VIP2', 1), (7, 7, 'P01', 1), (8, 7, 'P02', 1);

-- Gio chay lap lai theo tuyen de tham khao/lap lich.
INSERT INTO `gio_khoi_hanh_tuyen` (`ma_gio_khoi_hanh`, `ma_tuyen`, `gio_chay`) VALUES
(1, 1, '05:00:00'), (2, 1, '07:30:00'), (3, 1, '10:00:00'), (4, 1, '13:00:00'),
(5, 1, '17:00:00'), (6, 1, '20:30:00'), (7, 1, '22:00:00'),
(8, 6, '05:30:00'), (9, 6, '08:00:00'), (10, 6, '11:00:00'), (11, 6, '14:00:00'),
(12, 2, '05:30:00'), (13, 2, '07:30:00'), (14, 2, '09:30:00'), (15, 2, '11:30:00'),
(16, 2, '13:30:00'), (17, 2, '15:30:00'), (18, 2, '17:30:00'),
(19, 3, '05:00:00'), (20, 7, '05:00:00'), (21, 3, '07:00:00'), (22, 7, '07:00:00'),
(23, 3, '09:00:00'), (24, 7, '09:00:00'), (25, 3, '11:00:00'), (26, 7, '11:00:00'),
(27, 3, '13:00:00'), (28, 7, '13:00:00'), (29, 3, '15:00:00'), (30, 7, '15:00:00'),
(31, 3, '17:00:00'), (32, 7, '17:00:00'),
(33, 8, '06:00:00'), (34, 8, '09:00:00'), (35, 8, '13:30:00'), (36, 8, '19:00:00'), (37, 8, '21:30:00'),
(38, 9, '05:30:00'), (39, 9, '09:30:00'), (40, 9, '18:00:00'), (41, 9, '21:00:00'),
(42, 10, '17:00:00'), (43, 10, '19:00:00'), (44, 10, '20:30:00'),
(45, 11, '15:00:00'), (46, 11, '17:00:00');

-- Chuyen xe mau theo ngay. NULL tai xe vi khong tao tai khoan mau o migration 001.
INSERT INTO `chuyen_xe` (`ma_chuyen`, `ma_tuyen`, `ma_xe`, `ma_tai_xe`, `gio_khoi_hanh`, `gio_den_du_kien`, `gia_ve`, `trang_thai`) VALUES
(1, 1, 1, NULL, '2026-11-08 20:00:00', '2026-11-09 02:00:00', 180000.00, 'mo_ban'),
(2, 3, 4, NULL, '2026-11-09 07:00:00', '2026-11-09 10:30:00', 250000.00, 'mo_ban'),
(3, 4, 7, NULL, '2026-11-09 21:00:00', '2026-11-10 05:00:00', 350000.00, 'mo_ban'),
(4, 5, 8, NULL, '2026-11-10 08:00:00', '2026-11-10 14:00:00', 190000.00, 'mo_ban'),
(5, 6, 9, NULL, '2026-11-10 09:00:00', '2026-11-10 15:00:00', 200000.00, 'mo_ban');
