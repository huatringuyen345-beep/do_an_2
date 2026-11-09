-- Migration 001: roles and user accounts
-- Run once on a new database before migrations 002-005.

CREATE TABLE `vai_tro` (
  `ma_vai_tro` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_vai_tro` varchar(50) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ma_vai_tro`),
  UNIQUE KEY `uk_vai_tro_ten` (`ten_vai_tro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_vai_tro` int(10) UNSIGNED NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_khoa') NOT NULL DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_nguoi_dung`),
  UNIQUE KEY `uk_nguoi_dung_email` (`email`),
  KEY `idx_nguoi_dung_vai_tro` (`ma_vai_tro`),
  CONSTRAINT `fk_nguoi_dung_vai_tro_001`
    FOREIGN KEY (`ma_vai_tro`) REFERENCES `vai_tro` (`ma_vai_tro`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `vai_tro` (`ma_vai_tro`, `ten_vai_tro`, `mo_ta`) VALUES
(1, 'quan_tri', 'Quản trị toàn bộ website'),
(2, 'nhan_vien', 'Xử lý đặt vé, đơn gửi hàng và liên hệ'),
(3, 'khach_hang', 'Đặt vé và sử dụng dịch vụ'),
(4, 'tai_xe', 'Tài xế lái xe');

-- Create user accounts through the application so passwords are hashed with password_hash().
