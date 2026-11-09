<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('lien-he.php');
}

$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? '');
    $sdt = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $mail = trim($_POST['email'] ?? '');
    $nd = trim($_POST['noi_dung'] ?? '');

    if (mb_strlen($ten) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $sdt)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (mb_strlen($nd) < 5) $errors[] = 'Vui lòng nhập nội dung cần liên hệ.';

    if (!$errors) {
        $sent = true;
        require_once __DIR__ . '/../database/config.php';
        if (isset($conn) && $conn instanceof mysqli) {
            try {
                $userId = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
                $title = 'Tin nhắn liên hệ từ website';
                $stmt = $conn->prepare(
                    "INSERT INTO lien_he (ma_nguoi_dung, ho_ten, email, so_dien_thoai, tieu_de, noi_dung, trang_thai)
                     VALUES (?, ?, ?, ?, ?, ?, 'moi')"
                );
                $stmt->bind_param('isssss', $userId, $ten, $mail, $sdt, $title, $nd);
                $stmt->execute();
            } catch (Throwable $e) {
                error_log("Failed to save contact in DB: " . $e->getMessage());
            }
        }
    }
}
