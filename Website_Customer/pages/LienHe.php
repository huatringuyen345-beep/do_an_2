<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('LienHe.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyHoTro.php';

$pageTitle = 'Liên hệ';
$pageDesc  = 'Liên hệ nhà xe Tài Nguyên tại Bạc Liêu: địa chỉ, số điện thoại, email và form gửi tin nhắn.';
include __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/../includes/lien-he-content.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
