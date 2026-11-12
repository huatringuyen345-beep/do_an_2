<?php
/*
 * Xu ly bieu mau ung tuyen cua khach hang.
 * Kiem tra thong tin lien he va vi tri ung tuyen duoc chon.
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('TuyenDung.php');
}
// Xu ly gui ho so ung tuyen.
$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ten'] ?? '');
    $soDienThoai = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $emailUngTuyen = trim($_POST['email'] ?? '');
    $maViTri = $_POST['vi_tri'] ?? '';
    $maCacViTriHopLe = array_column($jobs, 'ma');

    if (mb_strlen($hoTen) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($emailUngTuyen !== '' && !filter_var($emailUngTuyen, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (!in_array($maViTri, $maCacViTriHopLe, true)) $errors[] = 'Vui lòng chọn vị trí ứng tuyển.';

    if (!$errors) {
        $sent = true;
    }
}
