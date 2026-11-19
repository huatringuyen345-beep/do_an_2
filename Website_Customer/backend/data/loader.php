<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../models/TuyenDuongModel.php';
require_once __DIR__ . '/../models/ThanhToanModel.php';
require_once __DIR__ . '/../models/DichVuModel.php';
require_once __DIR__ . '/../models/TinTucModel.php';
require_once __DIR__ . '/../models/TuyenDungModel.php';
require_once __DIR__ . '/../models/HoiDapModel.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new RuntimeException('Không thể tải dữ liệu vì chưa kết nối được cơ sở dữ liệu.');
}

$routes = [];
$payments = [];
$news = [];
$jobs = [];
$faqs = [];
$services = [];
$news_types = [
    'tin-tuc' => 'Tin tức',
    'su-kien' => 'Sự kiện',
    'khuyen-mai' => 'Khuyến mãi',
    'thong-bao' => 'Thông báo',
];

try {
    $tuyenDuongModel = new TuyenDuongModel($conn);
    $routes = $tuyenDuongModel->layTatCaTuyen('Bạc Liêu');
    if (!$routes) {
        throw new RuntimeException('Không tìm thấy tuyến hoạt động xuất phát từ Bạc Liêu.');
    }

    $thanhToanModel = new ThanhToanModel($conn);
    $dichVuModel = new DichVuModel($conn);
    $tinTucModel = new TinTucModel($conn);
    $tuyenDungModel = new TuyenDungModel($conn);
    $hoiDapModel = new HoiDapModel($conn);

    $payments = $thanhToanModel->layPhuongThuc();
    $news = $tinTucModel->layDanhSach();
    $jobs = $tuyenDungModel->layDanhSach();
    $faqs = $hoiDapModel->layDanhSach();

    foreach ($dichVuModel->layDanhSachDichVuHoatDong() as $row) {
        $services[] = [
            'id' => (int)$row['ma_dich_vu'],
            'ten' => $row['ten_dich_vu'],
            'loai' => $row['loai_dich_vu'],
            'mota' => $row['mo_ta'] ?? '',
            'gia' => (float)$row['gia_tham_khao'],
        ];
    }
} catch (Throwable $error) {
    error_log('Customer data loader failed: ' . $error->getMessage());
    throw new RuntimeException('Không thể tải dữ liệu khách hàng từ cơ sở dữ liệu.', 0, $error);
}
