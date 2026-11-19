CREATE TABLE IF NOT EXISTS `ho_so_ung_tuyen` (
  `ma_ho_so` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug_vi_tri` varchar(150) NOT NULL,
  `ho_ten` varchar(150) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `gioi_thieu` text DEFAULT NULL,
  `trang_thai` enum('moi','dang_xem','da_lien_he','tu_choi') NOT NULL DEFAULT 'moi',
  `ngay_nop` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_ho_so`),
  KEY `idx_ho_so_vi_tri_ngay_nop` (`slug_vi_tri`, `ngay_nop`),
  CONSTRAINT `fk_ho_so_ung_tuyen_vi_tri`
    FOREIGN KEY (`slug_vi_tri`) REFERENCES `tuyen_dung` (`slug`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
