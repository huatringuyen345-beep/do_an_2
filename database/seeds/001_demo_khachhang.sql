-- Chỉ dành cho môi trường phát triển cục bộ. Hãy thay đổi mật khẩu sau lần đăng nhập thành công đầu tiên.
INSERT INTO `nguoi_dung`
  (`ma_vai_tro`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `trang_thai`)
SELECT
  `ma_vai_tro`,
  'Customer',
  'customer@example.com',
  '0900000000',
  '$2y$10$EZmE9W.tRPuME/OnpShVHeyphVCWtRUK2i4jhUR2wnk0ULz4rdXm6', -- Mật khẩu: 12345678
  'hoat_dong'  --trang thai
FROM `vai_tro`
WHERE `ten_vai_tro` = 'khach_hang'
  AND NOT EXISTS (
    SELECT 1 FROM `nguoi_dung` WHERE `email` = 'customer@example.com'
  )
LIMIT 1;
