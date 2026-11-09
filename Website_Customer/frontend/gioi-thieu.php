<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Giới thiệu';
$pageDesc  = 'Tài Nguyên là công ty vận tải hành khách và hàng hóa tại Bạc Liêu, chạy các tuyến đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt, Nha Trang và các tỉnh miền Tây.';
include __DIR__ . '/includes/header.php';

$byGroup = [];
foreach ($routes as $r) { $byGroup[$r['nhom']][] = $r['ten']; }
?>

<?php include __DIR__ . '/includes/gioi-thieu-content.php'; ?>
