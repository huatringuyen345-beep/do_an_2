<?php
/*
 * Xu ly bieu mau ung tuyen cua khach hang.
 * Kiem tra thong tin lien he va vi tri ung tuyen duoc chon.
 */
require_once __DIR__ . '/../bootstrap.php';
// Xử lý hồ sơ ứng tuyển:
// - validate thông tin cá nhân
// - kiểm tra vị trí ứng tuyển có hợp lệ hay không
// - xác nhận hồ sơ đã được nhận
$loi = [];
$daGui = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $loi[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
    }

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
        try {
            $model = new TuyenDungModel($conn);
            $model->luuHoSoUngTuyen(
                $maViTriUngTuyen,
                $hoTen,
                $soDienThoai,
                $emailUngTuyen,
                trim($_POST['gioi_thieu'] ?? '')
            );
            $daGui = true;
        } catch (Throwable $error) {
            error_log('Recruitment application save failed: ' . $error->getMessage());
            $loi[] = 'Không thể lưu hồ sơ ứng tuyển lúc này. Vui lòng thử lại sau.';
        }
    }
}
