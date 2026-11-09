-- Migration 004: service catalog, service orders and shipment details
-- Requires 001_accounts.sql.

CREATE TABLE `dich_vu` (
  `ma_dich_vu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_dich_vu` varchar(150) NOT NULL,
  `loai_dich_vu` enum('van_chuyen_hang','trung_chuyen','khach_san','tram_dung','khac') NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `gia_tham_khao` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong',
  PRIMARY KEY (`ma_dich_vu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dat_dich_vu` (
  `ma_dat_dich_vu_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_dich_vu` varchar(30) NOT NULL,
  `ma_nguoi_dung` bigint(20) UNSIGNED DEFAULT NULL,
  `ma_dich_vu` int(10) UNSIGNED NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `ngay_su_dung` datetime DEFAULT NULL,
  `so_luong` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `tong_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dia_chi_don` varchar(255) DEFAULT NULL,
  `dia_chi_nhan` varchar(255) DEFAULT NULL,
  `trang_thai` enum('cho_xu_ly','da_xac_nhan','dang_thuc_hien','hoan_thanh','da_huy') NOT NULL DEFAULT 'cho_xu_ly',
  `ghi_chu` text DEFAULT NULL,
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_dat_dich_vu_id`),
  UNIQUE KEY `uk_dat_dich_vu_ma` (`ma_dat_dich_vu`),
  KEY `idx_dat_dv_nguoi_dung` (`ma_nguoi_dung`),
  KEY `idx_dat_dv_dich_vu` (`ma_dich_vu`),
  CONSTRAINT `fk_dat_dv_nguoi_dung_004`
    FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_dat_dv_dich_vu_004`
    FOREIGN KEY (`ma_dich_vu`) REFERENCES `dich_vu` (`ma_dich_vu`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chi_tiet_hang` (
  `ma_chi_tiet_hang` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_dich_vu_id` bigint(20) UNSIGNED NOT NULL,
  `ten_nguoi_gui` varchar(100) NOT NULL,
  `sdt_nguoi_gui` varchar(20) NOT NULL,
  `ten_nguoi_nhan` varchar(100) NOT NULL,
  `sdt_nguoi_nhan` varchar(20) NOT NULL,
  `loai_hang` varchar(100) NOT NULL,
  `mo_ta_hang` text DEFAULT NULL,
  `khoi_luong_kg` decimal(10,2) DEFAULT NULL,
  `gia_tri_khai_bao` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ma_van_don` varchar(40) NOT NULL,
  `trang_thai_van_don` enum('cho_lay_hang','da_nhan_hang','dang_van_chuyen','da_den_noi','da_giao','that_bai','da_huy') NOT NULL DEFAULT 'cho_lay_hang',
  PRIMARY KEY (`ma_chi_tiet_hang`),
  UNIQUE KEY `uk_chi_tiet_hang_van_don` (`ma_van_don`),
  KEY `idx_chi_tiet_hang_dat_dv` (`ma_dat_dich_vu_id`),
  CONSTRAINT `fk_chi_tiet_hang_dat_dv_004`
    FOREIGN KEY (`ma_dat_dich_vu_id`) REFERENCES `dat_dich_vu` (`ma_dat_dich_vu_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dich_vu` (`ma_dich_vu`, `ten_dich_vu`, `loai_dich_vu`, `mo_ta`, `gia_tham_khao`, `trang_thai`) VALUES
(1, 'Vận chuyển hàng hóa nhanh', 'van_chuyen_hang', 'Nhận gửi bưu phẩm, hàng hóa dọc tuyến miền Nam.', 50000.00, 'hoat_dong'),
(2, 'Trung chuyển tận nơi', 'trung_chuyen', 'Đưa đón khách tận nơi trong khu vực nhà xe phục vụ.', 30000.00, 'hoat_dong');

INSERT INTO `dat_dich_vu` (`ma_dat_dich_vu_id`, `ma_dat_dich_vu`, `ma_dich_vu`, `ho_ten`, `so_dien_thoai`, `ngay_su_dung`, `so_luong`, `tong_tien`, `dia_chi_don`, `dia_chi_nhan`, `trang_thai`, `ghi_chu`) VALUES
(1, 'DDV-DEMO-0001', 1, 'Khách hàng mẫu', '0900000002', '2026-11-09 08:00:00', 1, 50000.00, 'Văn phòng Bạc Liêu', 'Vĩnh Châu, Sóc Trăng', 'cho_xu_ly', 'Dữ liệu mẫu để kiểm tra xử lý đơn gửi hàng.');

INSERT INTO `chi_tiet_hang` (`ma_chi_tiet_hang`, `ma_dat_dich_vu_id`, `ten_nguoi_gui`, `sdt_nguoi_gui`, `ten_nguoi_nhan`, `sdt_nguoi_nhan`, `loai_hang`, `mo_ta_hang`, `khoi_luong_kg`, `gia_tri_khai_bao`, `ma_van_don`, `trang_thai_van_don`) VALUES
(1, 1, 'Khách hàng mẫu', '0900000002', 'Người nhận mẫu', '0900000003', 'Tài liệu', 'Hồ sơ', 1.00, 100000.00, 'VD-DEMO-0001', 'cho_lay_hang');
