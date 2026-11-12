<?php
/*
 * Điểm đăng xuất dùng chung cho khách hàng và nhân viên:
 * chỉ nhận POST, kiểm tra CSRF, xóa phiên tài khoản và tạo lại session ID.
 */

require_once __DIR__ . '/../backend/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    exit('Phiên làm việc không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang.');
}

unset($_SESSION['account']);
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['account_flash'] = ['success' => 'Bạn đã đăng xuất tài khoản an toàn thành công!'];

header('Location: Index.php', true, 303);
exit;
