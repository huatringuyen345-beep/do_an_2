<?php
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('tuyen-dung.php');
}
//tuyen dung nhan vien
$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? '');
    $sdt = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $mail = trim($_POST['email'] ?? '');
    $vt = $_POST['vi_tri'] ?? '';
    $validJobs = array_column($jobs, 'ma');

    if (mb_strlen($ten) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $sdt)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (!in_array($vt, $validJobs, true)) $errors[] = 'Vui lòng chọn vị trí ứng tuyển.';

    if (!$errors) {
        $sent = true;
    }
}
