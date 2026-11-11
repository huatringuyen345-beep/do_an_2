-- Migration 006: customer-facing Bạc Liêu routes and bed fares.
-- Run once after 005_content_and_support.sql on an existing database.

ALTER TABLE `tuyen_duong`
  ADD COLUMN `gia_ve_giuong_nam` decimal(12,2) DEFAULT NULL
  AFTER `gia_ve_co_ban`;

UPDATE `tuyen_duong`
SET `gia_ve_giuong_nam` = CASE `slug`
  WHEN 'binh-duong' THEN 280000.00
  WHEN 'vung-tau' THEN 310000.00
  WHEN 'da-lat' THEN 400000.00
  WHEN 'nha-trang' THEN 450000.00
  WHEN 'tp-hcm' THEN 250000.00
  ELSE `gia_ve_giuong_nam`
END
WHERE `slug` IN ('binh-duong', 'vung-tau', 'da-lat', 'nha-trang', 'tp-hcm');

INSERT INTO `tuyen_duong`
  (`ten_tuyen`, `slug`, `nhom_tuyen`, `ma_diem_di`, `ma_diem_den`,
   `khoang_cach_km`, `thoi_gian_phut`, `gia_ve_co_ban`, `gia_ve_giuong_nam`, `trang_thai`)
VALUES
  ('Bạc Liêu - Cà Mau', 'ca-mau', 'Đồng bằng sông Cửu Long', 2, 4, 65.00, 90, 60000.00, NULL, 'hoat_dong'),
  ('Bạc Liêu - Cần Thơ', 'can-tho', 'Đồng bằng sông Cửu Long', 2, 3, 120.00, 150, 110000.00, NULL, 'hoat_dong'),
  ('Bạc Liêu - Rạch Giá', 'rach-gia', 'Đồng bằng sông Cửu Long', 2, 6, 190.00, 240, 140000.00, NULL, 'hoat_dong'),
  ('Bạc Liêu - TP. Hồ Chí Minh', 'tp-hcm', 'Đông Nam Bộ', 2, 1, 280.00, 360, 200000.00, 250000.00, 'hoat_dong')
ON DUPLICATE KEY UPDATE
  `ten_tuyen` = VALUES(`ten_tuyen`),
  `nhom_tuyen` = VALUES(`nhom_tuyen`),
  `ma_diem_di` = VALUES(`ma_diem_di`),
  `ma_diem_den` = VALUES(`ma_diem_den`),
  `khoang_cach_km` = VALUES(`khoang_cach_km`),
  `thoi_gian_phut` = VALUES(`thoi_gian_phut`),
  `gia_ve_co_ban` = VALUES(`gia_ve_co_ban`),
  `gia_ve_giuong_nam` = VALUES(`gia_ve_giuong_nam`),
  `trang_thai` = VALUES(`trang_thai`);

INSERT INTO `gio_khoi_hanh_tuyen` (`ma_tuyen`, `gio_chay`)
SELECT td.`ma_tuyen`, lich.`gio_chay`
FROM (
  SELECT 'ca-mau' AS `slug`, '05:30:00' AS `gio_chay`
  UNION ALL SELECT 'ca-mau', '07:30:00'
  UNION ALL SELECT 'ca-mau', '09:30:00'
  UNION ALL SELECT 'ca-mau', '11:30:00'
  UNION ALL SELECT 'ca-mau', '13:30:00'
  UNION ALL SELECT 'ca-mau', '15:30:00'
  UNION ALL SELECT 'ca-mau', '17:30:00'
  UNION ALL SELECT 'can-tho', '05:00:00'
  UNION ALL SELECT 'can-tho', '07:00:00'
  UNION ALL SELECT 'can-tho', '09:00:00'
  UNION ALL SELECT 'can-tho', '11:00:00'
  UNION ALL SELECT 'can-tho', '13:00:00'
  UNION ALL SELECT 'can-tho', '15:00:00'
  UNION ALL SELECT 'can-tho', '17:00:00'
  UNION ALL SELECT 'rach-gia', '05:30:00'
  UNION ALL SELECT 'rach-gia', '08:00:00'
  UNION ALL SELECT 'rach-gia', '11:00:00'
  UNION ALL SELECT 'rach-gia', '14:00:00'
  UNION ALL SELECT 'tp-hcm', '05:00:00'
  UNION ALL SELECT 'tp-hcm', '07:30:00'
  UNION ALL SELECT 'tp-hcm', '10:00:00'
  UNION ALL SELECT 'tp-hcm', '13:00:00'
  UNION ALL SELECT 'tp-hcm', '17:00:00'
  UNION ALL SELECT 'tp-hcm', '20:30:00'
  UNION ALL SELECT 'tp-hcm', '22:00:00'
) AS lich
INNER JOIN `tuyen_duong` td ON td.`slug` = lich.`slug`
LEFT JOIN `gio_khoi_hanh_tuyen` da_co
  ON da_co.`ma_tuyen` = td.`ma_tuyen` AND da_co.`gio_chay` = lich.`gio_chay`
WHERE da_co.`ma_gio_khoi_hanh` IS NULL;
