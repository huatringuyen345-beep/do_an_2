-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 07, 2026 at 02:14 PM
-- Server version: 11.8.6-MariaDB-5ubuntu0.1 from Ubuntu
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `website_tainguyen`
--

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_hang`
--

CREATE TABLE `chi_tiet_hang` (
  `ma_chi_tiet_hang` bigint(20) UNSIGNED NOT NULL,
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
  `trang_thai_van_don` enum('cho_lay_hang','da_nhan_hang','dang_van_chuyen','da_den_noi','da_giao','that_bai','da_huy') NOT NULL DEFAULT 'cho_lay_hang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chi_tiet_hang`
--

INSERT INTO `chi_tiet_hang` (`ma_chi_tiet_hang`, `ma_dat_dich_vu_id`, `ten_nguoi_gui`, `sdt_nguoi_gui`, `ten_nguoi_nhan`, `sdt_nguoi_nhan`, `loai_hang`, `mo_ta_hang`, `khoi_luong_kg`, `gia_tri_khai_bao`, `ma_van_don`, `trang_thai_van_don`) VALUES
(1, 1, 'Trần Đức Thắng', '0966777888', 'Người Nhận Thắng', '0911223355', 'Tài liệu', 'Hồ sơ', 1.00, 100000.00, 'VD99998888', 'cho_lay_hang');

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_ve`
--

CREATE TABLE `chi_tiet_ve` (
  `ma_chi_tiet_ve` bigint(20) UNSIGNED NOT NULL,
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
  `ma_chuyen` bigint(20) UNSIGNED NOT NULL,
  `ma_ghe` int(10) UNSIGNED NOT NULL,
  `diem_don_id` int(10) UNSIGNED DEFAULT NULL,
  `diem_tra_id` int(10) UNSIGNED DEFAULT NULL,
  `gia_ve` decimal(12,2) NOT NULL,
  `trang_thai` enum('giu_cho','da_xac_nhan','da_huy') NOT NULL DEFAULT 'giu_cho',
  `ghe_dang_dat` int(10) UNSIGNED GENERATED ALWAYS AS (case when `trang_thai` in ('giu_cho','da_xac_nhan') then `ma_ghe` else NULL end) STORED,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chi_tiet_ve`
--

INSERT INTO `chi_tiet_ve` (`ma_chi_tiet_ve`, `ma_dat_ve_id`, `ma_chuyen`, `ma_ghe`, `diem_don_id`, `diem_tra_id`, `gia_ve`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 1, 1, 1, 2, 180000.00, 'da_xac_nhan', '2026-10-24 12:21:00'),
(2, 2, 2, 5, 1, 3, 250000.00, 'giu_cho', '2026-10-24 12:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `chuyen_xe`
--

CREATE TABLE `chuyen_xe` (
  `ma_chuyen` bigint(20) UNSIGNED NOT NULL,
  `ma_tuyen` int(10) UNSIGNED NOT NULL,
  `ma_xe` int(10) UNSIGNED NOT NULL,
  `ma_tai_xe` bigint(20) UNSIGNED DEFAULT NULL,
  `gio_khoi_hanh` datetime NOT NULL,
  `gio_den_du_kien` datetime DEFAULT NULL,
  `gia_ve` decimal(12,2) NOT NULL,
  `trang_thai` enum('mo_ban','het_cho','dang_chay','hoan_thanh','da_huy') NOT NULL DEFAULT 'mo_ban'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chuyen_xe`
--

INSERT INTO `chuyen_xe` (`ma_chuyen`, `ma_tuyen`, `ma_xe`, `ma_tai_xe`, `gio_khoi_hanh`, `gio_den_du_kien`, `gia_ve`, `trang_thai`) VALUES
(1, 1, 1, 9, '2026-10-24 20:00:00', '2026-10-25 02:00:00', 180000.00, 'mo_ban'),
(2, 3, 4, 10, '2026-10-25 07:00:00', '2026-10-25 10:30:00', 250000.00, 'mo_ban'),
(3, 4, 7, 9, '2026-10-25 21:00:00', '2026-10-26 05:00:00', 350000.00, 'mo_ban'),
(4, 5, 8, 10, '2026-10-25 08:00:00', '2026-10-25 14:00:00', 190000.00, 'mo_ban'),
(5, 6, 9, 9, '2026-10-26 09:00:00', '2026-10-26 15:00:00', 200000.00, 'mo_ban');

-- --------------------------------------------------------

--
-- Table structure for table `dat_dich_vu`
--

CREATE TABLE `dat_dich_vu` (
  `ma_dat_dich_vu_id` bigint(20) UNSIGNED NOT NULL,
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
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dat_dich_vu`
--

INSERT INTO `dat_dich_vu` (`ma_dat_dich_vu_id`, `ma_dat_dich_vu`, `ma_nguoi_dung`, `ma_dich_vu`, `ho_ten`, `so_dien_thoai`, `ngay_su_dung`, `so_luong`, `tong_tien`, `dia_chi_don`, `dia_chi_nhan`, `trang_thai`, `ghi_chu`, `ngay_dat`) VALUES
(1, 'DDV20261024_01', 7, 1, 'Trần Đức Thắng', '0966777888', '2026-10-25 08:00:00', 1, 50000.00, 'Quận 1, HCM', 'Thành phố Vĩnh Châu', 'da_xac_nhan', 'Gửi tài liệu', '2026-10-24 12:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `dat_ve`
--

CREATE TABLE `dat_ve` (
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
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
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dat_ve`
--

INSERT INTO `dat_ve` (`ma_dat_ve_id`, `ma_dat_ve`, `ma_nguoi_dung`, `ma_khuyen_mai_id`, `ten_khach_hang`, `sdt_khach_hang`, `email_khach_hang`, `tong_tien`, `tien_giam`, `thanh_tien`, `trang_thai`, `ghi_chu`, `ngay_dat`) VALUES
(1, 'DV20261024_01', 5, 1, 'Lưu Thạnh Mậu', '0944555666', 'mau.khachhang@example.com', 180000.00, 30000.00, 150000.00, 'da_thanh_toan', 'Đón tại BX Miền Tây', '2026-10-24 12:21:00'),
(2, 'DV20261024_02', 6, NULL, 'Lê Huỳnh Thái', '0955666777', 'thai.khachhang@example.com', 250000.00, 0.00, 250000.00, 'cho_xac_nhan', 'Cho ghế đầu', '2026-10-24 12:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `dich_vu`
--

CREATE TABLE `dich_vu` (
  `ma_dich_vu` int(10) UNSIGNED NOT NULL,
  `ten_dich_vu` varchar(150) NOT NULL,
  `loai_dich_vu` enum('van_chuyen_hang','trung_chuyen','khach_san','tram_dung','khac') NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `gia_tham_khao` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dich_vu`
--

INSERT INTO `dich_vu` (`ma_dich_vu`, `ten_dich_vu`, `loai_dich_vu`, `mo_ta`, `hinh_anh`, `gia_tham_khao`, `trang_thai`) VALUES
(1, 'Vận chuyển hàng hóa nhanh', 'van_chuyen_hang', 'Nhận gửi bưu phẩm, hàng hóa dọc tuyến miền Nam.', NULL, 50000.00, 'hoat_dong'),
(2, 'Trung chuyển tận nơi', 'trung_chuyen', 'Đưa đón khách tận nơi trong nội thành Sài Gòn, Cần Thơ, Bạc Liêu.', NULL, 30000.00, 'hoat_dong');

-- --------------------------------------------------------

--
-- Table structure for table `diem_dung`
--

CREATE TABLE `diem_dung` (
  `ma_diem_dung` int(10) UNSIGNED NOT NULL,
  `ten_diem_dung` varchar(120) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `tinh_thanh` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diem_dung`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `ghe_xe`
--

CREATE TABLE `ghe_xe` (
  `ma_ghe` int(10) UNSIGNED NOT NULL,
  `ma_xe` int(10) UNSIGNED NOT NULL,
  `so_ghe` varchar(10) NOT NULL,
  `tang` tinyint(3) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ghe_xe`
--

INSERT INTO `ghe_xe` (`ma_ghe`, `ma_xe`, `so_ghe`, `tang`) VALUES
(1, 1, 'A01', 1),
(2, 1, 'A02', 1),
(3, 1, 'B01', 2),
(4, 1, 'B02', 2),
(5, 4, 'VIP1', 1),
(6, 4, 'VIP2', 1),
(7, 7, 'P01', 1),
(8, 7, 'P02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gio_khoi_hanh_tuyen`
--

CREATE TABLE `gio_khoi_hanh_tuyen` (
  `ma_gio_khoi_hanh` int(10) UNSIGNED NOT NULL,
  `ma_tuyen` int(10) UNSIGNED NOT NULL,
  `gio_chay` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gio_khoi_hanh_tuyen`
--

INSERT INTO `gio_khoi_hanh_tuyen` (`ma_gio_khoi_hanh`, `ma_tuyen`, `gio_chay`) VALUES
(1, 1, '05:00:00'),
(2, 1, '07:30:00'),
(3, 1, '10:00:00'),
(4, 1, '13:00:00'),
(5, 1, '17:00:00'),
(6, 1, '20:30:00'),
(7, 1, '22:00:00'),
(8, 6, '05:30:00'),
(9, 6, '08:00:00'),
(10, 6, '11:00:00'),
(11, 6, '14:00:00'),
(12, 2, '05:30:00'),
(13, 2, '07:30:00'),
(14, 2, '09:30:00'),
(15, 2, '11:30:00'),
(16, 2, '13:30:00'),
(17, 2, '15:30:00'),
(18, 2, '17:30:00'),
(19, 3, '05:00:00'),
(20, 7, '05:00:00'),
(22, 3, '07:00:00'),
(23, 7, '07:00:00'),
(25, 3, '09:00:00'),
(26, 7, '09:00:00'),
(28, 3, '11:00:00'),
(29, 7, '11:00:00'),
(31, 3, '13:00:00'),
(32, 7, '13:00:00'),
(34, 3, '15:00:00'),
(35, 7, '15:00:00'),
(37, 3, '17:00:00'),
(38, 7, '17:00:00'),
(40, 8, '06:00:00'),
(41, 8, '09:00:00'),
(42, 8, '13:30:00'),
(43, 8, '19:00:00'),
(44, 8, '21:30:00'),
(45, 9, '05:30:00'),
(46, 9, '09:30:00'),
(47, 9, '18:00:00'),
(48, 9, '21:00:00'),
(49, 10, '17:00:00'),
(50, 10, '19:00:00'),
(51, 10, '20:30:00'),
(52, 11, '15:00:00'),
(53, 11, '17:00:00'),
(54, 12, '05:30:00'), (55, 12, '07:30:00'), (56, 12, '09:30:00'),
(57, 12, '11:30:00'), (58, 12, '13:30:00'), (59, 12, '15:30:00'), (60, 12, '17:30:00'),
(61, 13, '05:00:00'), (62, 13, '07:00:00'), (63, 13, '09:00:00'),
(64, 13, '11:00:00'), (65, 13, '13:00:00'), (66, 13, '15:00:00'), (67, 13, '17:00:00'),
(68, 14, '05:30:00'), (69, 14, '08:00:00'), (70, 14, '11:00:00'), (71, 14, '14:00:00'),
(72, 15, '05:00:00'), (73, 15, '07:30:00'), (74, 15, '10:00:00'),
(75, 15, '13:00:00'), (76, 15, '17:00:00'), (77, 15, '20:30:00'), (78, 15, '22:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hoi_dap`
--

CREATE TABLE `hoi_dap` (
  `ma_faq` int(10) UNSIGNED NOT NULL,
  `cau_hoi` varchar(255) NOT NULL,
  `cau_tra_loi` text NOT NULL,
  `thu_tu` int(11) NOT NULL DEFAULT 0,
  `trang_thai` enum('hien_thi','an') NOT NULL DEFAULT 'hien_thi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hoi_dap`
--

INSERT INTO `hoi_dap` (`ma_faq`, `cau_hoi`, `cau_tra_loi`, `thu_tu`, `trang_thai`) VALUES
(1, 'Tôi cần đến sớm bao lâu trước giờ xe chạy?', 'Quý khách nên có mặt tại điểm đón trước giờ khởi hành 20 đến 30 phút để làm thủ tục và gửi hành lý.', 1, 'hien_thi'),
(2, 'Xe ghế ngồi và giường nằm khác nhau thế nào?', 'Xe ghế ngồi phù hợp các tuyến ngắn trong khu vực miền Tây. Xe giường nằm có hai tầng, phù hợp các tuyến dài đi TP. Hồ Chí Minh, Đà Lạt, Nha Trang.', 2, 'hien_thi'),
(3, 'Tôi có thể đổi hoặc hủy vé không?', 'Quý khách có thể đổi hoặc hủy vé trước giờ xe chạy. Mức phí phụ thuộc thời điểm hủy, xem bảng bên dưới hoặc gọi hotline.', 3, 'hien_thi'),
(4, 'Trẻ em có phải mua vé không?', 'Trẻ em dưới 5 tuổi ngồi cùng người lớn được miễn vé. Từ 5 tuổi trở lên mua vé như người lớn.', 4, 'hien_thi'),
(5, 'Hành lý được mang theo bao nhiêu?', 'Mỗi khách được mang theo hành lý tối đa 20 kg miễn cước. Phần vượt quá tính theo bảng cước gửi hàng.', 5, 'hien_thi'),
(6, 'Tôi muốn gửi hàng thì làm thế nào?', 'Mang hàng đến văn phòng Tài Nguyên hoặc gọi hotline để xe trung chuyển đến lấy. Xem thêm tại trang Dịch vụ.', 6, 'hien_thi');

-- --------------------------------------------------------

--
-- Table structure for table `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `ma_khuyen_mai_id` int(10) UNSIGNED NOT NULL,
  `ma_code` varchar(30) NOT NULL,
  `ten_khuyen_mai` varchar(150) NOT NULL,
  `loai_giam` enum('phan_tram','so_tien') NOT NULL,
  `gia_tri` decimal(12,2) NOT NULL,
  `giam_toi_da` decimal(12,2) DEFAULT NULL,
  `don_hang_toi_thieu` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `so_luong` int(10) UNSIGNED DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`ma_khuyen_mai_id`, `ma_code`, `ten_khuyen_mai`, `loai_giam`, `gia_tri`, `giam_toi_da`, `don_hang_toi_thieu`, `ngay_bat_dau`, `ngay_ket_thuc`, `so_luong`, `trang_thai`) VALUES
(1, 'MIENNAM2026', 'Ưu đãi Miền Nam 2026', 'so_tien', 30000.00, 30000.00, 100000.00, '2026-10-01 00:00:00', '2026-11-30 23:59:59', 500, 'hoat_dong');

-- --------------------------------------------------------

--
-- Table structure for table `lien_he`
--

CREATE TABLE `lien_he` (
  `ma_lien_he` bigint(20) UNSIGNED NOT NULL,
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
  `ngay_phan_hoi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lien_he`
--

INSERT INTO `lien_he` (`ma_lien_he`, `ma_nguoi_dung`, `ho_ten`, `email`, `so_dien_thoai`, `tieu_de`, `noi_dung`, `trang_thai`, `noi_dung_phan_hoi`, `ma_nguoi_xu_ly`, `ngay_gui`, `ngay_phan_hoi`) VALUES
(1, 8, 'Nguyễn Song Nhân', 'nhan.khachhang@example.com', '0977888999', 'Hỏi về xe đi Bạc Liêu', 'Bên mình có xe giường phòng đi Bạc Liêu không ạ?', 'moi', NULL, NULL, '2026-10-24 12:21:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `loai_xe`
--

CREATE TABLE `loai_xe` (
  `ma_loai_xe` int(10) UNSIGNED NOT NULL,
  `ten_loai_xe` varchar(100) NOT NULL,
  `so_ghe` int(10) UNSIGNED NOT NULL,
  `mo_ta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loai_xe`
--

INSERT INTO `loai_xe` (`ma_loai_xe`, `ten_loai_xe`, `so_ghe`, `mo_ta`) VALUES
(1, 'Xe Giường Nằm 44 Chỗ', 44, 'Xe giường nằm cao cấp có điều hòa, nước uống, wifi.'),
(2, 'Xe Ghế Ngồi 29 Chỗ', 29, 'Xe ghế ngồi êm ái, thích hợp cho các tuyến ngắn.'),
(3, 'Xe Limousine 9 Chỗ', 9, 'Xe Limousine đẳng cấp thương gia, ghế massage.'),
(4, 'Xe Giường Phòng 22 Chỗ', 22, 'Phòng nằm riêng biệt, tivi, rèm che, riêng tư.');

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` bigint(20) UNSIGNED NOT NULL,
  `ma_vai_tro` int(10) UNSIGNED NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_khoa') NOT NULL DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ma_vai_tro`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `dia_chi`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 'Demo Admin', 'admin@example.com', '0900000000', '$2y$10$EZmE9W.tRPuME/OnpShVHeyphVCWtRUK2i4jhUR2wnk0ULz4rdXm6', NULL, 'hoat_dong', '2026-10-24 12:21:00'),
(2, 1, 'Châu Văn Tài', 'tai.admin@example.com', '0912111222', '$2y$10$abcdef1234567890abcdef', 'Quận 1, TP Hồ Chí Minh', 'hoat_dong', '2026-10-24 12:21:00'),
(3, 2, 'Ngô Nhật Duy', 'duy.nhanvien@example.com', '0922333444', '$2y$10$abcdef1234567890abcdef', 'Bình Thủy, Cần Thơ', 'hoat_dong', '2026-10-24 12:21:00'),
(4, 2, 'Nguyễn Hữu Đạt', 'dat.nhanvien@example.com', '0933444555', '$2y$10$abcdef1234567890abcdef', 'Thành phố Cà Mau, Cà Mau', 'hoat_dong', '2026-10-24 12:21:00'),
(5, 3, 'Lưu Thạnh Mậu', 'mau.khachhang@example.com', '0944555666', '$2y$10$abcdef1234567890abcdef', 'Rạch Giá, Kiên Giang', 'hoat_dong', '2026-10-24 12:21:00'),
(6, 3, 'Lê Huỳnh Thái', 'thai.khachhang@example.com', '0955666777', '$2y$10$abcdef1234567890abcdef', 'Châu Đốc, An Giang', 'hoat_dong', '２０２６－１０－２４ １２：２１：００'),
(7, 3, 'Trần Đức Thắng', 'thang.khachhang@example.com', '0966777888', '$2y$10$abcdef1234567890abcdef', 'Vĩnh Châu, Sóc Trăng', 'hoat_dong', '2026-10-24 12:21:00'),
(8, 3, 'Nguyễn Song Nhân', 'nhan.khachhang@example.com', '0977888999', '$2y$10$abcdef1234567890abcdef', 'Hòa Bình, Bạc Liêu', 'hoat_dong', '2026-10-24 12:21:00'),
(9, 4, 'Nguyễn Văn Tài Xế 1', 'taixe1@example.com', '0988999000', '$2y$10$abcdef1234567890abcdef', 'Quận 7, TP Hồ Chí Minh', 'hoat_dong', '2026-10-24 12:21:00'),
(10, 4, 'Lê Văn Tài Xế 2', 'taixe2@example.com', '0999000111', '$2y$10$abcdef1234567890abcdef', 'Ninh Kiều, Cần Thơ', 'hoat_dong', '2026-10-24 12:21:00'),
(11, 1, 'Demo Admin', 'demo-admin-disabled@example.com', '0900000000', 'DISABLED_DEMO_PASSWORD_HASH', NULL, 'tam_khoa', '2026-10-25 05:19:05');

-- --------------------------------------------------------

--
-- Table structure for table `phuong_thuc_thanh_toan`
--

CREATE TABLE `phuong_thuc_thanh_toan` (
  `ma_phuong_thuc` varchar(30) NOT NULL,
  `ten_phuong_thuc` varchar(100) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phuong_thuc_thanh_toan`
--

INSERT INTO `phuong_thuc_thanh_toan` (`ma_phuong_thuc`, `ten_phuong_thuc`, `mo_ta`, `icon`, `trang_thai`) VALUES
('ck', 'Chuyển khoản ngân hàng', 'Chỉ chuyển khoản sau khi nhân viên xác nhận và cung cấp thông tin nhận tiền.', 'bank', 'hoat_dong'),
('quay', 'Thanh toán tại quầy vé', 'Nhân viên sẽ xác nhận thông tin và hướng dẫn thanh toán.', 'wallet', 'hoat_dong'),
('the', 'Thẻ ATM / Visa / Mastercard', 'Website chưa thu tiền trực tuyến; nhân viên sẽ xác nhận cách thanh toán.', 'card', 'hoat_dong'),
('vi', 'Ví điện tử (MoMo, ZaloPay)', 'Nhân viên sẽ xác nhận phương thức thanh toán trước khi bạn thanh toán.', 'phone', 'hoat_dong');

-- --------------------------------------------------------

--
-- Table structure for table `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `ma_thanh_toan` bigint(20) UNSIGNED NOT NULL,
  `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `phuong_thuc` enum('tien_mat','chuyen_khoan','vi_dien_tu','the_ngan_hang') NOT NULL,
  `so_tien` decimal(12,2) NOT NULL,
  `trang_thai` enum('cho_thanh_toan','thanh_cong','that_bai','hoan_tien') NOT NULL DEFAULT 'cho_thanh_toan',
  `thoi_gian` datetime NOT NULL DEFAULT current_timestamp(),
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `thanh_toan`
--

INSERT INTO `thanh_toan` (`ma_thanh_toan`, `ma_dat_ve_id`, `ma_giao_dich`, `phuong_thuc`, `so_tien`, `trang_thai`, `thoi_gian`, `ghi_chu`) VALUES
(1, 1, 'MOMO12345678', 'vi_dien_tu', 150000.00, 'thanh_cong', '2026-10-24 19:25:00', 'Thanh toán thành công MoMo');

-- --------------------------------------------------------

--
-- Table structure for table `tin_tuc`
--

CREATE TABLE `tin_tuc` (
  `ma_tin_tuc` bigint(20) UNSIGNED NOT NULL,
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
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tin_tuc`
--

INSERT INTO `tin_tuc` (`ma_tin_tuc`, `ma_nguoi_dang`, `tieu_de`, `slug`, `tom_tat`, `noi_dung`, `hinh_anh`, `loai_tin`, `trang_thai`, `luot_xem`, `ngay_dang`, `ngay_ket_thuc`, `ngay_tao`) VALUES
(1, 1, 'Mở rộng tuyến xe Miền Nam 2026', 'mo-rong-tuyen-xe-mien-nam', 'Tăng cường xe chất lượng cao các tỉnh Tây Nam Bộ.', '<p>Phục vụ bà con các tỉnh ĐBSCL với 10 dòng xe mới ra mắt.</p>', NULL, 'tin_tuc', 'da_dang', 50, '2026-10-24 08:00:00', NULL, '2026-10-24 12:21:00'),
(2, 1, 'Giảm 10% vé giường nằm đi TP. Hồ Chí Minh khi đặt online', 'giam-10-ve-giuong-nam-di-tp-ho-chi-minh', 'Áp dụng cho các chuyến khởi hành từ thứ Hai đến thứ Năm, đặt trước ít nhất 24 giờ.', '<p>Từ nay đến hết tháng 11, khách đặt vé giường nằm tuyến Bạc Liêu – TP. Hồ Chí Minh trên website được giảm 10% giá vé. Chương trình áp dụng cho các chuyến khởi hành từ thứ Hai đến thứ Năm. Vé cần được đặt trước giờ xe chạy ít nhất 24 giờ. Chương trình không cộng dồn với các ưu đãi khác. Liên hệ hotline để được hướng dẫn chi tiết.</p>', NULL, 'khuyen_mai', 'da_dang', 0, '2026-10-05 08:00:00', '2026-11-30', '2026-10-25 06:42:01'),
(3, 1, 'Tăng chuyến đi Đà Lạt và Nha Trang dịp cuối năm', 'tang-chuyen-di-da-lat-nha-trang', 'Bổ sung thêm giờ chạy buổi tối cho hai tuyến đường dài từ giữa tháng 12.', '<p>Để đáp ứng nhu cầu đi lại dịp cuối năm, Tài Nguyên bổ sung thêm chuyến buổi tối cho tuyến Đà Lạt và Nha Trang. Lịch chạy chi tiết sẽ được cập nhật trên trang Lịch trình. Quý khách nên đặt vé sớm để chọn được chỗ ngồi mong muốn.</p>', NULL, 'thong_bao', 'da_dang', 0, '2026-10-03 08:00:00', NULL, '2026-10-25 06:42:01'),
(4, 1, 'Xe trung chuyển đón khách tận nơi trong nội ô Bạc Liêu', 'xe-trung-chuyen-don-khach-tan-noi', 'Khách đặt vé online có thể đăng ký đón tại nhà ngay trong bước điền thông tin.', '<p>Từ nay, khi đặt vé trên website, quý khách chỉ cần đánh dấu \"Đăng ký xe trung chuyển\" và nhập địa chỉ đón. Nhân viên sẽ gọi xác nhận thời gian đón trước giờ xe chạy. Dịch vụ áp dụng trong khu vực nội ô và các xã lân cận.</p>', NULL, 'tin_tuc', 'da_dang', 0, '2026-09-28 08:00:00', NULL, '2026-10-25 06:42:01'),
(5, 1, 'Tuyển lái xe và nhân viên phòng vé', 'tuyen-lai-xe-nhan-vien-phong-ve', 'Tài Nguyên đang cần bổ sung nhân sự cho các tuyến đi TP. Hồ Chí Minh và Đà Lạt.', '<p>Công ty đang tuyển lái xe khách hạng D và nhân viên phòng vé làm việc tại Bạc Liêu. Xem chi tiết vị trí và nộp hồ sơ tại trang Tuyển dụng.</p>', NULL, 'thong_bao', 'da_dang', 0, '2026-09-20 08:00:00', NULL, '2026-10-25 06:42:01'),
(6, 1, 'Ưu đãi cước gửi hàng cho khách thường xuyên', 'uu-dai-cuoc-gui-hang', 'Giảm cước cho khách gửi hàng từ 4 lần mỗi tháng trên cùng một tuyến.', '<p>Khách hàng gửi hàng thường xuyên trên cùng một tuyến được áp dụng bảng cước ưu đãi riêng. Vui lòng liên hệ phòng điều hành để đăng ký và nhận bảng giá.</p>', NULL, 'khuyen_mai', 'da_dang', 0, '2026-09-12 08:00:00', NULL, '2026-10-25 06:42:01'),
(7, 1, 'Hướng dẫn đặt vé online trong 3 bước', 'huong-dan-dat-ve-online', 'Chọn chuyến, chọn chỗ, điền thông tin. Không cần tạo tài khoản.', '<p>Vào trang Đặt vé, chọn tuyến, ngày đi và giờ khởi hành. Chọn chỗ ngồi hoặc giường trên sơ đồ xe, sau đó điền họ tên và số điện thoại. Chọn hình thức thanh toán và xác nhận. Mã vé sẽ hiển thị ngay trên màn hình.</p>', NULL, 'su_kien', 'da_dang', 0, '2026-08-30 08:00:00', NULL, '2026-10-25 06:42:01');

-- --------------------------------------------------------

--
-- Table structure for table `tuyen_dung`
--

CREATE TABLE `tuyen_dung` (
  `ma_tuyen_dung` int(10) UNSIGNED NOT NULL,
  `slug` varchar(150) NOT NULL,
  `ten_vi_tri` varchar(150) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `dia_diem` varchar(150) NOT NULL,
  `muc_luong` varchar(100) DEFAULT NULL,
  `mo_ta` text NOT NULL,
  `yeu_cau` text NOT NULL,
  `trang_thai` enum('mo_tuyen','dong_tuyen') NOT NULL DEFAULT 'mo_tuyen',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tuyen_dung`
--

INSERT INTO `tuyen_dung` (`ma_tuyen_dung`, `slug`, `ten_vi_tri`, `so_luong`, `dia_diem`, `muc_luong`, `mo_ta`, `yeu_cau`, `trang_thai`, `ngay_tao`) VALUES
(1, 'lai-xe', 'Lái xe khách', 4, 'Bạc Liêu', 'Từ 12 – 18 triệu', 'Điều khiển xe khách các tuyến đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu và Đà Lạt.', '[\"Bằng lái hạng D còn hạn\", \"Từ 3 năm kinh nghiệm lái xe khách\", \"Sức khỏe tốt, có thể đi tuyến đường dài\"]', 'mo_tuyen', '2026-10-25 06:42:01'),
(2, 'phu-xe', 'Phụ xe', 3, 'Bạc Liêu', 'Từ 7 – 10 triệu', 'Hỗ trợ khách lên xuống, sắp xếp hành lý và hàng hóa gửi theo xe.', '[\"Nhanh nhẹn, giao tiếp lịch sự\", \"Chấp nhận đi tuyến dài ngày\", \"Ưu tiên có kinh nghiệm\"]', 'mo_tuyen', '2026-10-25 06:42:01'),
(3, 'phong-ve', 'Nhân viên phòng vé', 2, 'Bạc Liêu', 'Từ 8 – 10 triệu', 'Bán vé, tư vấn tuyến và giờ chạy, tiếp nhận hàng gửi tại văn phòng.', '[\"Tốt nghiệp trung cấp trở lên\", \"Sử dụng thành thạo máy tính\", \"Có thể làm theo ca\"]', 'mo_tuyen', '2026-10-25 06:42:01'),
(4, 'web', 'Nhân viên chăm sóc khách hàng online', 1, 'Bạc Liêu', 'Thỏa thuận', 'Trả lời khách qua điện thoại và website, xác nhận vé đặt online.', '[\"Giọng nói rõ ràng, kiên nhẫn\", \"Biết sử dụng máy tính văn phòng\", \"Ưu tiên người biết quản trị website\"]', 'mo_tuyen', '2026-10-25 06:42:01');

-- --------------------------------------------------------

--
-- Table structure for table `tuyen_duong`
--

CREATE TABLE `tuyen_duong` (
  `ma_tuyen` int(10) UNSIGNED NOT NULL,
  `ten_tuyen` varchar(150) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `nhom_tuyen` varchar(100) DEFAULT NULL,
  `ma_diem_di` int(10) UNSIGNED NOT NULL,
  `ma_diem_den` int(10) UNSIGNED NOT NULL,
  `khoang_cach_km` decimal(8,2) DEFAULT NULL,
  `thoi_gian_phut` int(10) UNSIGNED DEFAULT NULL,
  `gia_ve_co_ban` decimal(12,2) NOT NULL,
  `gia_ve_giuong_nam` decimal(12,2) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','tam_ngung') NOT NULL DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tuyen_duong`
--

INSERT INTO `tuyen_duong` (`ma_tuyen`, `ten_tuyen`, `slug`, `nhom_tuyen`, `ma_diem_di`, `ma_diem_den`, `khoang_cach_km`, `thoi_gian_phut`, `gia_ve_co_ban`, `gia_ve_giuong_nam`, `trang_thai`) VALUES
(1, 'Sài Gòn - Bạc Liêu', 'sai-gon-bac-lieu', 'Đông Nam Bộ', 1, 2, 280.00, 360, 180000.00, NULL, 'hoat_dong'),
(2, 'Cần Thơ - Cà Mau', 'can-tho-ca-mau', 'Đồng bằng sông Cửu Long', 3, 4, 180.00, 240, 120000.00, NULL, 'hoat_dong'),
(3, 'Sài Gòn - Cần Thơ', 'sai-gon-can-tho', 'Đồng bằng sông Cửu Long', 1, 3, 170.00, 210, 150000.00, NULL, 'hoat_dong'),
(4, 'Sài Gòn - Cà Mau', 'sai-gon-ca-mau', 'Đồng bằng sông Cửu Long', 1, 4, 350.00, 480, 220000.00, NULL, 'hoat_dong'),
(5, 'Sài Gòn - Châu Đốc', 'sai-gon-chau-doc', 'Đồng bằng sông Cửu Long', 1, 5, 250.00, 360, 190000.00, NULL, 'hoat_dong'),
(6, 'Sài Gòn - Rạch Giá', 'sai-gon-rach-gia', 'Đồng bằng sông Cửu Long', 1, 6, 280.00, 360, 200000.00, NULL, 'hoat_dong'),
(7, 'Bạc Liêu - Sóc Trăng', 'soc-trang', 'Đồng bằng sông Cửu Long', 2, 7, 60.00, 90, 60000.00, NULL, 'hoat_dong'),
(8, 'Bạc Liêu - Bình Dương', 'binh-duong', 'Đông Nam Bộ', 2, 8, 320.00, 420, 230000.00, 280000.00, 'hoat_dong'),
(9, 'Bạc Liêu - Vũng Tàu', 'vung-tau', 'Đông Nam Bộ', 2, 9, 360.00, 480, 260000.00, 310000.00, 'hoat_dong'),
(10, 'Bạc Liêu - Đà Lạt', 'da-lat', 'Tây Nguyên và Nam Trung Bộ', 2, 10, 530.00, 720, 330000.00, 400000.00, 'hoat_dong'),
(11, 'Bạc Liêu - Nha Trang', 'nha-trang', 'Tây Nguyên và Nam Trung Bộ', 2, 11, 700.00, 900, 380000.00, 450000.00, 'hoat_dong'),
(12, 'Bạc Liêu - Cà Mau', 'ca-mau', 'Đồng bằng sông Cửu Long', 2, 4, 65.00, 90, 60000.00, NULL, 'hoat_dong'),
(13, 'Bạc Liêu - Cần Thơ', 'can-tho', 'Đồng bằng sông Cửu Long', 2, 3, 120.00, 150, 110000.00, NULL, 'hoat_dong'),
(14, 'Bạc Liêu - Rạch Giá', 'rach-gia', 'Đồng bằng sông Cửu Long', 2, 6, 190.00, 240, 140000.00, NULL, 'hoat_dong'),
(15, 'Bạc Liêu - TP. Hồ Chí Minh', 'tp-hcm', 'Đông Nam Bộ', 2, 1, 280.00, 360, 200000.00, 250000.00, 'hoat_dong');

-- --------------------------------------------------------

--
-- Table structure for table `vai_tro`
--

CREATE TABLE `vai_tro` (
  `ma_vai_tro` int(10) UNSIGNED NOT NULL,
  `ten_vai_tro` varchar(50) NOT NULL,
  `mo_ta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vai_tro`
--

INSERT INTO `vai_tro` (`ma_vai_tro`, `ten_vai_tro`, `mo_ta`) VALUES
(1, 'quan_tri', 'Quản trị toàn bộ website'),
(2, 'nhan_vien', 'Xử lý đặt vé, đơn gửi hàng'),
(3, 'khach_hang', 'Đặt vé và sử dụng dịch vụ'),
(4, 'tai_xe', 'Tài xế lái xe'),
(5, 'dieu_hanh', 'Quản lý tuyến đường, đội xe và lịch chạy'),
(6, 'phong_ve', 'Xử lý đặt vé, thanh toán và chăm sóc khách hàng'),
(7, 'logistics', 'Xử lý đơn dịch vụ và vận đơn hàng hóa'),
(8, 'marketing', 'Quản lý tin tức, tuyển dụng, khuyến mãi và hỏi đáp');

-- --------------------------------------------------------

--
-- Table structure for table `xe`
--

CREATE TABLE `xe` (
  `ma_xe` int(10) UNSIGNED NOT NULL,
  `ma_loai_xe` int(10) UNSIGNED NOT NULL,
  `bien_so` varchar(20) NOT NULL,
  `ten_xe` varchar(100) DEFAULT NULL,
  `trang_thai` enum('hoat_dong','bao_tri','ngung_hoat_dong') NOT NULL DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `xe`
--

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chi_tiet_hang`
--
ALTER TABLE `chi_tiet_hang`
  ADD PRIMARY KEY (`ma_chi_tiet_hang`),
  ADD KEY `fk_chi_tiet_hang_dat_dv` (`ma_dat_dich_vu_id`);

--
-- Indexes for table `chi_tiet_ve`
--
ALTER TABLE `chi_tiet_ve`
  ADD PRIMARY KEY (`ma_chi_tiet_ve`),
  ADD KEY `fk_ctv_chuyen` (`ma_chuyen`),
  ADD KEY `fk_ctv_dat_ve` (`ma_dat_ve_id`),
  ADD KEY `fk_ctv_diem_don` (`diem_don_id`),
  ADD KEY `fk_ctv_diem_tra` (`diem_tra_id`),
  ADD KEY `fk_ctv_ghe` (`ma_ghe`);

--
-- Indexes for table `chuyen_xe`
--
ALTER TABLE `chuyen_xe`
  ADD PRIMARY KEY (`ma_chuyen`),
  ADD KEY `fk_chuyen_tai_xe` (`ma_tai_xe`),
  ADD KEY `fk_chuyen_tuyen` (`ma_tuyen`),
  ADD KEY `fk_chuyen_xe` (`ma_xe`);

--
-- Indexes for table `dat_dich_vu`
--
ALTER TABLE `dat_dich_vu`
  ADD PRIMARY KEY (`ma_dat_dich_vu_id`),
  ADD KEY `fk_dat_dv_dich_vu` (`ma_dich_vu`),
  ADD KEY `fk_dat_dv_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `dat_ve`
--
ALTER TABLE `dat_ve`
  ADD PRIMARY KEY (`ma_dat_ve_id`),
  ADD KEY `fk_dat_ve_khuyen_mai` (`ma_khuyen_mai_id`),
  ADD KEY `fk_dat_ve_nguoi_dung` (`ma_nguoi_dung`);

--
-- Indexes for table `dich_vu`
--
ALTER TABLE `dich_vu`
  ADD PRIMARY KEY (`ma_dich_vu`);

--
-- Indexes for table `diem_dung`
--
ALTER TABLE `diem_dung`
  ADD PRIMARY KEY (`ma_diem_dung`);

--
-- Indexes for table `ghe_xe`
--
ALTER TABLE `ghe_xe`
  ADD PRIMARY KEY (`ma_ghe`),
  ADD KEY `fk_ghe_xe` (`ma_xe`);

--
-- Indexes for table `gio_khoi_hanh_tuyen`
--
ALTER TABLE `gio_khoi_hanh_tuyen`
  ADD PRIMARY KEY (`ma_gio_khoi_hanh`),
  ADD KEY `fk_gio_chay_tuyen` (`ma_tuyen`);

--
-- Indexes for table `hoi_dap`
--
ALTER TABLE `hoi_dap`
  ADD PRIMARY KEY (`ma_faq`);

--
-- Indexes for table `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`ma_khuyen_mai_id`);

--
-- Indexes for table `lien_he`
--
ALTER TABLE `lien_he`
  ADD PRIMARY KEY (`ma_lien_he`),
  ADD KEY `fk_lien_he_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `fk_lien_he_nguoi_xu_ly` (`ma_nguoi_xu_ly`);

--
-- Indexes for table `loai_xe`
--
ALTER TABLE `loai_xe`
  ADD PRIMARY KEY (`ma_loai_xe`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`ma_nguoi_dung`),
  ADD KEY `fk_nguoi_dung_vai_tro` (`ma_vai_tro`);

--
-- Indexes for table `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  ADD PRIMARY KEY (`ma_phuong_thuc`);

--
-- Indexes for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`ma_thanh_toan`),
  ADD KEY `fk_thanh_toan_dat_ve` (`ma_dat_ve_id`);

--
-- Indexes for table `tin_tuc`
--
ALTER TABLE `tin_tuc`
  ADD PRIMARY KEY (`ma_tin_tuc`),
  ADD KEY `fk_tin_tuc_nguoi_dang` (`ma_nguoi_dang`);

--
-- Indexes for table `tuyen_dung`
--
ALTER TABLE `tuyen_dung`
  ADD PRIMARY KEY (`ma_tuyen_dung`),
  ADD UNIQUE KEY `uk_tuyen_dung_slug` (`slug`);

--
-- Indexes for table `tuyen_duong`
--
ALTER TABLE `tuyen_duong`
  ADD PRIMARY KEY (`ma_tuyen`),
  ADD UNIQUE KEY `uk_tuyen_slug` (`slug`),
  ADD KEY `fk_tuyen_diem_den` (`ma_diem_den`),
  ADD KEY `fk_tuyen_diem_di` (`ma_diem_di`);

--
-- Indexes for table `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`ma_vai_tro`),
  ADD UNIQUE KEY `uk_vai_tro_ten` (`ten_vai_tro`);

--
-- Indexes for table `xe`
--
ALTER TABLE `xe`
  ADD PRIMARY KEY (`ma_xe`),
  ADD KEY `fk_xe_loai` (`ma_loai_xe`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chi_tiet_hang`
--
ALTER TABLE `chi_tiet_hang`
  MODIFY `ma_chi_tiet_hang` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chi_tiet_ve`
--
ALTER TABLE `chi_tiet_ve`
  MODIFY `ma_chi_tiet_ve` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chuyen_xe`
--
ALTER TABLE `chuyen_xe`
  MODIFY `ma_chuyen` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dat_dich_vu`
--
ALTER TABLE `dat_dich_vu`
  MODIFY `ma_dat_dich_vu_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dat_ve`
--
ALTER TABLE `dat_ve`
  MODIFY `ma_dat_ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dich_vu`
--
ALTER TABLE `dich_vu`
  MODIFY `ma_dich_vu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `diem_dung`
--
ALTER TABLE `diem_dung`
  MODIFY `ma_diem_dung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ghe_xe`
--
ALTER TABLE `ghe_xe`
  MODIFY `ma_ghe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gio_khoi_hanh_tuyen`
--
ALTER TABLE `gio_khoi_hanh_tuyen`
  MODIFY `ma_gio_khoi_hanh` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `hoi_dap`
--
ALTER TABLE `hoi_dap`
  MODIFY `ma_faq` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `ma_khuyen_mai_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lien_he`
--
ALTER TABLE `lien_he`
  MODIFY `ma_lien_he` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loai_xe`
--
ALTER TABLE `loai_xe`
  MODIFY `ma_loai_xe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `ma_nguoi_dung` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `ma_thanh_toan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tin_tuc`
--
ALTER TABLE `tin_tuc`
  MODIFY `ma_tin_tuc` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tuyen_dung`
--
ALTER TABLE `tuyen_dung`
  MODIFY `ma_tuyen_dung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tuyen_duong`
--
ALTER TABLE `tuyen_duong`
  MODIFY `ma_tuyen` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `ma_vai_tro` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `xe`
--
ALTER TABLE `xe`
  MODIFY `ma_xe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chi_tiet_hang`
--
ALTER TABLE `chi_tiet_hang`
  ADD CONSTRAINT `fk_chi_tiet_hang_dat_dv` FOREIGN KEY (`ma_dat_dich_vu_id`) REFERENCES `dat_dich_vu` (`ma_dat_dich_vu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `fk_nguoi_dung_vai_tro` FOREIGN KEY (`ma_vai_tro`) REFERENCES `vai_tro` (`ma_vai_tro`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;