<?php
/*
 * Luồng thanh toán mô phỏng cho đặt vé nhà xe.
 * - Kiểm tra quyền sở hữu đơn của tài khoản khách hàng
 * - Tạo / xử lý thanh toán giả lập
 * - Cập nhật trạng thái đơn và giao dịch thanh toán
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../database/config.php';

customer_require_account('TaiKhoan.php');

$paymentErrorMessage = '';
$paymentData = null;
$orderCode = trim((string)($_GET['order'] ?? $_POST['order'] ?? ''));
$action = trim((string)($_GET['action'] ?? $_POST['action'] ?? 'view'));
$selectedMethod = trim((string)($_POST['phuong_thuc'] ?? 'quay'));
$allowedMethods = ['quay', 'ck', 'the', 'vi'];
if (!in_array($selectedMethod, $allowedMethods, true)) {
    $selectedMethod = 'quay';
}

if ($orderCode === '') {
    $_SESSION['account_flash'] = ['error' => 'Không tìm thấy mã đặt vé cần thanh toán.'];
    header('Location: ../../pages/TaiKhoan.php?tab=ve', true, 303);
    exit;
}

if (isset($conn) && $conn instanceof mysqli) {
    try {
        $stmt = $conn->prepare(
            "SELECT d.ma_dat_ve_id, d.ma_dat_ve, d.ma_nguoi_dung, d.ten_khach_hang, d.sdt_khach_hang,
                    d.email_khach_hang, d.tong_tien, d.thanh_tien, d.trang_thai,
                    t.ma_thanh_toan, t.ma_giao_dich, t.phuong_thuc, t.trang_thai AS payment_status,
                    t.so_tien, t.ghi_chu
             FROM dat_ve d
             LEFT JOIN thanh_toan t ON t.ma_dat_ve_id = d.ma_dat_ve_id
             WHERE d.ma_dat_ve = ?
             ORDER BY t.ma_thanh_toan DESC
             LIMIT 1"
        );
        $stmt->bind_param('s', $orderCode);
        $stmt->execute();
        $paymentData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('Payment order lookup failed: ' . $e->getMessage());
        $paymentErrorMessage = 'Không thể tải đơn thanh toán. Vui lòng thử lại sau.';
    }
}

if ($paymentData === null) {
    $_SESSION['account_flash'] = ['error' => 'Không tìm thấy đơn đặt vé hoặc bạn không có quyền truy cập.'];
    header('Location: ../../pages/TaiKhoan.php?tab=ve', true, 303);
    exit;
}

$currentUserId = (int)($_SESSION['account']['id'] ?? 0);
$currentEmail = (string)($_SESSION['account']['email'] ?? '');
if ((int)$paymentData['ma_nguoi_dung'] !== $currentUserId && $paymentData['email_khach_hang'] !== $currentEmail) {
    $_SESSION['account_flash'] = ['error' => 'Bạn không có quyền xử lý giao dịch của đơn này.'];
    header('Location: ../../pages/TaiKhoan.php?tab=ve', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && in_array($action, ['confirm', 'cancel'], true) && isset($conn) && $conn instanceof mysqli) {
    $csrfToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $csrfToken)) {
        $paymentErrorMessage = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
    } else {
    try {
        $conn->begin_transaction();
        $paymentStatus = $action === 'confirm' ? 'thanh_cong' : 'that_bai';
        $transactionCode = 'MOCK-' . strtoupper(substr(md5(uniqid((string)$orderCode, true)), 0, 10));

        if ($action === 'confirm') {
            $newStatus = 'da_thanh_toan';
            $upOrder = $conn->prepare('UPDATE dat_ve SET trang_thai = ? WHERE ma_dat_ve_id = ?');
            $upOrder->bind_param('si', $newStatus, $paymentData['ma_dat_ve_id']);
            $upOrder->execute();
            $upOrder->close();
        }

        $phuongThucLuu = customer_payment_method_for_storage($conn, $selectedMethod);
        $upPayment = $conn->prepare(
            'UPDATE thanh_toan SET ma_giao_dich = ?, phuong_thuc = ?, so_tien = ?, trang_thai = ?, ghi_chu = ?, thoi_gian = NOW() WHERE ma_dat_ve_id = ? ORDER BY ma_thanh_toan DESC LIMIT 1'
        );
        $note = $action === 'confirm'
            ? 'Thanh toán mock thành công cho đơn ' . $orderCode
            : 'Khách hàng hủy thanh toán mock cho đơn ' . $orderCode;
        $upPayment->bind_param('ssdssi', $transactionCode, $phuongThucLuu, $paymentData['thanh_tien'], $paymentStatus, $note, $paymentData['ma_dat_ve_id']);
        $upPayment->execute();
        $upPayment->close();

        $statusExists = $conn->query("SHOW TABLES LIKE 'lich_su_thanh_toan'")->num_rows > 0;
        if ($statusExists) {
            $logStmt = $conn->prepare(
                'INSERT INTO lich_su_thanh_toan (ma_dat_ve_id, ma_giao_dich, phuong_thuc, trang_thai_cu, trang_thai_moi, so_tien, ghi_chu) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $oldStatus = $paymentData['payment_status'] ?? 'cho_thanh_toan';
            $logStmt->bind_param('issssds', $paymentData['ma_dat_ve_id'], $transactionCode, $selectedMethod, $oldStatus, $paymentStatus, $paymentData['thanh_tien'], $note);
            $logStmt->execute();
            $logStmt->close();
        }

        $conn->commit();
        $_SESSION['account_flash'] = [
            'success' => $action === 'confirm'
                ? 'Thanh toán mock thành công cho đơn ' . $orderCode . '. Đơn đã được cập nhật sang trạng thái đã thanh toán.'
                : 'Bạn đã hủy giao dịch thanh toán mock cho đơn ' . $orderCode . '.',
        ];
        header('Location: ../../pages/TaiKhoan.php?tab=ve', true, 303);
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('Mock payment update failed: ' . $e->getMessage());
        $paymentErrorMessage = 'Không thể xử lý thanh toán mock. Vui lòng thử lại sau.';
    }
    }
}

$paymentData['payment_status'] = $paymentData['payment_status'] ?? 'cho_thanh_toan';
$paymentData['method_label'] = [
    'quay' => 'Thanh toán tại quầy',
    'tien_mat' => 'Thanh toán tại quầy',
    'ck' => 'Chuyển khoản ngân hàng',
    'chuyen_khoan' => 'Chuyển khoản ngân hàng',
    'the' => 'Thẻ ATM / Visa / Mastercard',
    'the_ngan_hang' => 'Thẻ ATM / Visa / Mastercard',
    'vi' => 'Ví điện tử (MoMo, ZaloPay)',
    'vi_dien_tu' => 'Ví điện tử (MoMo, ZaloPay)',
][$paymentData['phuong_thuc'] ?? 'quay'] ?? 'Thanh toán theo lựa chọn';
