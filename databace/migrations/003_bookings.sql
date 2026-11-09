-- Migration 003: promotions, payment methods and ticket bookings
-- Requires 001_accounts.sql and 002_routes_and_fleet.sql.

CREATE TABLE `khuyen_mai` (
  `ma_khuyen_mai_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_code` varchar(30) NOT NULL,
  `ten_khuyen_mai` varchar(150) NOT NULL,
  `loai_giam` enum('phan_tram','so_tien') NOT NULL,
  `gia_tri` decimal(12,2) NOT NULL,
  `giam_toi_da` decimal(12,2) DEFAULT NULL,
  `don_hang_toi_thieu` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `so_luong` int(10) UNSIGNED DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong',
  PRIMARY KEY (`ma_khuyen_mai_id`),
  UNIQUE KEY `uk_khuyen_mai_code` (`ma_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `phuong_thuc_thanh_toan` (
  `ma_phuong_thuc` varchar(30) NOT NULL,
  `ten_phuong_thuc` varchar(100) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong',
  PRIMARY KEY (`ma_phuong_thuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dat_ve` (
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_ve` varchar(30) NOT NULL,
  `ma_nguoi_dung` bigint(20) UNSIGNED DEFAULT NULL,
  `ma_khuyen_mai_id` int(10) UNSIGNED DEFAULT NULL,
  `ten_khach_hang` varchar(100) NOT NULL,
  `sdt_khach_hang` varchar(20) NOT NULL,
  `email_khach_hang` varchar(150) DEFAULT NULL,
  `tong_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tien_giam` decimal(12,2) NOT NULL DEFAULT 0.00,
  `thanh_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai` enum('cho_xac_nhan','da_xac_nhan','da_thanh_toan','da_huy','hoan_tien') NOT NULL DEFAULT 'cho_xac_nhan',
  `ghi_chu` text DEFAULT NULL,
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_dat_ve_id`),
  UNIQUE KEY `uk_dat_ve_ma` (`ma_dat_ve`),
  KEY `idx_dat_ve_nguoi_dung` (`ma_nguoi_dung`),
  KEY `idx_dat_ve_khuyen_mai` (`ma_khuyen_mai_id`),
  CONSTRAINT `fk_dat_ve_nguoi_dung_003`
    FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_dat_ve_khuyen_mai_003`
    FOREIGN KEY (`ma_khuyen_mai_id`) REFERENCES `khuyen_mai` (`ma_khuyen_mai_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chi_tiet_ve` (
  `ma_chi_tiet_ve` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
  `ma_chuyen` bigint(20) UNSIGNED NOT NULL,
  `ma_ghe` int(10) UNSIGNED NOT NULL,
  `diem_don_id` int(10) UNSIGNED DEFAULT NULL,
  `diem_tra_id` int(10) UNSIGNED DEFAULT NULL,
  `gia_ve` decimal(12,2) NOT NULL,
  `trang_thai` enum('giu_cho','da_xac_nhan','da_huy') NOT NULL DEFAULT 'giu_cho',
  `ghe_dang_dat` int(10) UNSIGNED GENERATED ALWAYS AS (CASE WHEN `trang_thai` IN ('giu_cho','da_xac_nhan') THEN `ma_ghe` ELSE NULL END) STORED,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_chi_tiet_ve`),
  UNIQUE KEY `uk_chuyen_ghe_dang_dat` (`ma_chuyen`, `ghe_dang_dat`),
  KEY `idx_ctv_dat_ve` (`ma_dat_ve_id`),
  KEY `idx_ctv_ghe` (`ma_ghe`),
  KEY `idx_ctv_diem_don` (`diem_don_id`),
  KEY `idx_ctv_diem_tra` (`diem_tra_id`),
  CONSTRAINT `fk_ctv_dat_ve_003`
    FOREIGN KEY (`ma_dat_ve_id`) REFERENCES `dat_ve` (`ma_dat_ve_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ctv_chuyen_003`
    FOREIGN KEY (`ma_chuyen`) REFERENCES `chuyen_xe` (`ma_chuyen`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ctv_ghe_003`
    FOREIGN KEY (`ma_ghe`) REFERENCES `ghe_xe` (`ma_ghe`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ctv_diem_don_003`
    FOREIGN KEY (`diem_don_id`) REFERENCES `diem_dung` (`ma_diem_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ctv_diem_tra_003`
    FOREIGN KEY (`diem_tra_id`) REFERENCES `diem_dung` (`ma_diem_dung`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thanh_toan` (
  `ma_thanh_toan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `phuong_thuc` varchar(30) NOT NULL,
  `so_tien` decimal(12,2) NOT NULL,
  `trang_thai` enum('cho_thanh_toan','thanh_cong','that_bai','hoan_tien') NOT NULL DEFAULT 'cho_thanh_toan',
  `thoi_gian` datetime NOT NULL DEFAULT current_timestamp(),
  `ghi_chu` text DEFAULT NULL,
  PRIMARY KEY (`ma_thanh_toan`),
  KEY `idx_thanh_toan_dat_ve` (`ma_dat_ve_id`),
  KEY `idx_thanh_toan_phuong_thuc` (`phuong_thuc`),
  CONSTRAINT `fk_thanh_toan_dat_ve_003`
    FOREIGN KEY (`ma_dat_ve_id`) REFERENCES `dat_ve` (`ma_dat_ve_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_thanh_toan_phuong_thuc_003`
    FOREIGN KEY (`phuong_thuc`) REFERENCES `phuong_thuc_thanh_toan` (`ma_phuong_thuc`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `khuyen_mai` (`ma_code`, `ten_khuyen_mai`, `loai_giam`, `gia_tri`, `giam_toi_da`, `don_hang_toi_thieu`, `ngay_bat_dau`, `ngay_ket_thuc`, `so_luong`, `trang_thai`) VALUES
('MIENNAM2026', 'Ưu đãi Miền Nam 2026', 'so_tien', 30000.00, 30000.00, 100000.00, '2026-11-07 00:00:00', '2026-11-30 23:59:59', 500, 'hoat_dong');

INSERT INTO `phuong_thuc_thanh_toan` (`ma_phuong_thuc`, `ten_phuong_thuc`, `mo_ta`, `icon`, `trang_thai`) VALUES
('ck', 'Chuyển khoản ngân hàng', 'Nhân viên xác nhận và cung cấp thông tin nhận tiền.', 'bank', 'hoat_dong'),
('quay', 'Thanh toán tại quầy vé', 'Nhân viên sẽ xác nhận thông tin và hướng dẫn thanh toán.', 'wallet', 'hoat_dong'),
('the', 'Thẻ ATM / Visa / Mastercard', 'Nhân viên sẽ xác nhận cách thanh toán.', 'card', 'hoat_dong'),
('vi', 'Ví điện tử (MoMo, ZaloPay)', 'Nhân viên sẽ xác nhận phương thức thanh toán.', 'phone', 'hoat_dong');

INSERT INTO `dat_ve` (`ma_dat_ve_id`, `ma_dat_ve`, `ten_khach_hang`, `sdt_khach_hang`, `email_khach_hang`, `tong_tien`, `tien_giam`, `thanh_tien`, `trang_thai`, `ghi_chu`) VALUES
(1, 'DV-DEMO-0001', 'Khách hàng mẫu', '0900000001', NULL, 180000.00, 0.00, 180000.00, 'cho_xac_nhan', 'Dữ liệu mẫu; chỗ A01 trên chuyến 1.');

INSERT INTO `chi_tiet_ve` (`ma_dat_ve_id`, `ma_chuyen`, `ma_ghe`, `diem_don_id`, `diem_tra_id`, `gia_ve`, `trang_thai`) VALUES
(1, 1, 1, 1, 2, 180000.00, 'giu_cho');

INSERT INTO `thanh_toan` (`ma_dat_ve_id`, `ma_giao_dich`, `phuong_thuc`, `so_tien`, `trang_thai`, `ghi_chu`) VALUES
(1, NULL, 'quay', 180000.00, 'cho_thanh_toan', 'Thanh toán tại quầy sau khi nhân viên xác nhận.');
