<?php
/*
 * Xu ly bieu mau ung tuyen cua khach hang.
 * Kiem tra thong tin lien he va vi tri ung tuyen duoc chon.
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('TuyenDung.php');
}
// Xử lý hồ sơ ứng tuyển:
// - validate thông tin cá nhân
// - kiểm tra vị trí ứng tuyển có hợp lệ hay không
// - xác nhận hồ sơ đã được nhận
$loi = [];
$daGui = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim(
        $_POST['ten'] ?? ''
        );
    $soDienThoai = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $emailUngTuyen = trim($_POST['email'] ?? '');
    $maViTriUngTuyen = $_POST['vi_tri'] ?? '';
    $danhSachViTriHopLe = array_column($jobs, 'ma');

    if (mb_strlen($hoTen) < 2)
        $loi[] = 'Vui lòng nhập họ và tên.';

    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai))
        $loi[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';

    if ($emailUngTuyen !== '' && !filter_var($emailUngTuyen, FILTER_VALIDATE_EMAIL))
        $loi[] = 'Email chưa đúng định dạng.';

    if (!in_array($maViTriUngTuyen, $danhSachViTriHopLe, true))
        $loi[] = 'Vui lòng chọn vị trí ứng tuyển.';

    if (!$loi) {
        $daGui = true;
    }
}
