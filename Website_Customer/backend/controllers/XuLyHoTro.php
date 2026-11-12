<?php
/*
 * Xu ly bieu mau lien he cua khach hang.
 * Kiem tra thong tin gui den va luu tin nhan vao co so du lieu neu co ket noi.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('LienHe.php');
}

$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ten'] ?? '');
    $soDienThoai = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $emailLienHe = trim($_POST['email'] ?? '');
    $noiDungLienHe = trim($_POST['noi_dung'] ?? '');

    if (mb_strlen($hoTen) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($emailLienHe !== '' && !filter_var($emailLienHe, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (mb_strlen($noiDungLienHe) < 5) $errors[] = 'Vui lòng nhập nội dung cần liên hệ.';

    if (!$errors) {
        $sent = true;
        require_once __DIR__ . '/../database/config.php';
        if (isset($conn) && $conn instanceof mysqli) {
            try {
                $maNguoiDung = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
                $tieuDe = 'Tin nhắn liên hệ từ website';
                $cauLenh = $conn->prepare(
                    "INSERT INTO lien_he (ma_nguoi_dung, ho_ten, email, so_dien_thoai, tieu_de, noi_dung, trang_thai)
                     VALUES (?, ?, ?, ?, ?, ?, 'moi')"
                );
                $cauLenh->bind_param('isssss', $maNguoiDung, $hoTen, $emailLienHe, $soDienThoai, $tieuDe, $noiDungLienHe);
                $cauLenh->execute();
            } catch (Throwable $loi) {
                error_log("Loi khi luu lien he vao co so du lieu: " . $loi->getMessage());
            }
        }
    }
}
