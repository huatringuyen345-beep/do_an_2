<?php
/*
 * Xu ly bieu mau lien he cua khach hang.
 * Kiem tra thong tin gui den va luu tin nhan vao co so du lieu neu co ket noi.
 */
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../models/LienHeModel.php';

$loi = [];
$daGui = false;

// Xử lý form liên hệ:
// - validate họ tên, số điện thoại, email và nội dung
// - lưu tin nhắn nếu dữ liệu hợp lệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ten'] ?? '');
    $soDienThoai = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $emailLienHe = trim($_POST['email'] ?? '');
    $noiDungLienHe = trim($_POST['noi_dung'] ?? '');

    if (mb_strlen($hoTen) < 2) $loi[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) 
        $loi[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($emailLienHe !== '' && !filter_var($emailLienHe, FILTER_VALIDATE_EMAIL)) 
        $loi[] = 'Email chưa đúng định dạng.';
    if (mb_strlen($noiDungLienHe) < 5) 
        $loi[] = 'Vui lòng nhập nội dung cần liên hệ.';

    if (!$loi) {
        if (isset($conn) && $conn instanceof mysqli) {
            try {
                $maNguoiDung = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
            $model = new LienHeModel($conn);
            $model->guiLienHe(
                $maNguoiDung,
                $hoTen,
                $emailLienHe,
                $soDienThoai,
                'Tin nhắn liên hệ từ website',
                $noiDungLienHe
            );
                $daGui = true;
            } catch (Throwable $loiDuLieu) {
                error_log("Loi khi luu lien he vao co so du lieu: " . $loiDuLieu->getMessage());
                $loi[] = 'Hệ thống chưa thể gửi tin nhắn lúc này. Vui lòng thử lại sau hoặc gọi hotline.';
            }
        } else {
            $loi[] = 'Không thể kết nối cơ sở dữ liệu để gửi tin nhắn.';
        }
    }
}
