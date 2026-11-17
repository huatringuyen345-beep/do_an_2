-- Migration 007: payment flow and transaction history
-- Tăng rõ ràng luồng thanh toán cho đơn đặt vé.

CREATE TABLE IF NOT EXISTS `lich_su_thanh_toan` (
  `ma_lich_su` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `phuong_thuc` varchar(30) NOT NULL DEFAULT 'quay',
  `trang_thai_cu` enum('cho_thanh_toan','thanh_cong','that_bai','hoan_tien') DEFAULT NULL,
  `trang_thai_moi` enum('cho_thanh_toan','thanh_cong','that_bai','hoan_tien') NOT NULL,
  `so_tien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ghi_chu` text DEFAULT NULL,
  `thoi_gian` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_lich_su`),
  KEY `idx_lich_su_thanh_toan_dat_ve` (`ma_dat_ve_id`),
  KEY `idx_lich_su_thanh_toan_time` (`thoi_gian`),
  CONSTRAINT `fk_lich_su_thanh_toan_dat_ve_007`
    FOREIGN KEY (`ma_dat_ve_id`) REFERENCES `dat_ve` (`ma_dat_ve_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gợi ý sử dụng trong triển khai thực tế:
-- 1. Đơn đặt vé có trạng thái: cho_xac_nhan / da_xac_nhan / da_thanh_toan / da_huy / hoan_tien
-- 2. Giao dịch thanh toán có trạng thái: cho_thanh_toan / thanh_cong / that_bai / hoan_tien
-- 3. Lịch sử thanh toán lưu tất cả sự kiện thay đổi để audit và hỗ trợ khi cần đối soát.
