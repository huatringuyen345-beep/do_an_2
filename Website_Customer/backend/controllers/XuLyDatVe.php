<?php
/*
 * Xu ly va kiem tra yeu cau dat ve cua khach hang.
 * Tao ban ghi dat ve trong co so du lieu va luu thong tin vua dat vao phien.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('dat-ve.php');
}

$duLieuYeuCau = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

$route = find_route($duLieuYeuCau['tuyen'] ?? '') ?? find_route('tp-hcm');
$chieu = (($duLieuYeuCau['chieu'] ?? 'di') === 've') ? 've' : 'di';

$ngay = $duLieuYeuCau['ngay'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngay) || strtotime($ngay) === false) $ngay = date('Y-m-d');
if ($ngay < date('Y-m-d')) $ngay = date('Y-m-d');

$loai = $duLieuYeuCau['loai'] ?? '';
if (!in_array($loai, ['ghe', 'giuong'], true) || empty($route[$loai])) {
    $loai = !empty($route['giuong']) ? 'giuong' : 'ghe';
}
$gio = $duLieuYeuCau['gio'] ?? $route['khoi_hanh'][0];
if (!in_array($gio, $route['khoi_hanh'], true)) $gio = $route['khoi_hanh'][0];
$want = max(1, min(10, (int)($duLieuYeuCau['sl'] ?? 1)));

function route_label($route, $direction)
{
    return $direction === 've' ? $route['ten'] . ' – Bạc Liêu' : 'Bạc Liêu – ' . $route['ten'];
}

$errors = [];
$done = null;
$seatsPosted = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ten'] ?? '');
    $soDienThoai = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $emailLienHe = trim($_POST['email'] ?? '');
    $phuongThucThanhToan = $_POST['thanh_toan'] ?? '';
    $coXeTrungChuyen = !empty($_POST['trung_chuyen']);
    $diaChiDon = trim($_POST['dia_chi_don'] ?? '');

    $danhSachGhe = array_values(array_unique(array_filter(array_map('trim', explode(',', $_POST['ghe'] ?? '')))));
    $seatsPosted = implode(',', $danhSachGhe);
    $tienToGhe = $loai === 'ghe' ? ['G'] : ['D', 'T'];
    $maGheHopLe = [];
    foreach ($tienToGhe as $tienTo) {
        $soLuongCho = $loai === 'ghe' ? 32 : 18;
        for ($soThuTu = 1; $soThuTu <= $soLuongCho; $soThuTu++) {
            $maGheHopLe[] = $tienTo . str_pad((string)$soThuTu, 2, '0', STR_PAD_LEFT);
        }
    }
    foreach ($danhSachGhe as $maGhe) {
        if (!in_array($maGhe, $maGheHopLe, true)) {
            $errors[] = 'Mã chỗ ngồi không hợp lệ.';
            break;
        }
    }

    if ($hoTen === '' || mb_strlen($hoTen) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($emailLienHe !== '' && !filter_var($emailLienHe, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (count($danhSachGhe) < 1) $errors[] = 'Vui lòng chọn chỗ trên sơ đồ xe.';
    elseif (count($danhSachGhe) !== $want) $errors[] = 'Vui lòng chọn đủ ' . $want . ' chỗ theo số hành khách.';
    if (count($danhSachGhe) > 10) $errors[] = 'Mỗi lần đặt tối đa 10 chỗ.';
    if (!isset($payments[$phuongThucThanhToan])) $errors[] = 'Vui lòng chọn hình thức thanh toán.';
    if ($coXeTrungChuyen && $diaChiDon === '') $errors[] = 'Bạn đã chọn xe trung chuyển, vui lòng nhập địa chỉ đón.';

    if (!$errors) {
        require_once __DIR__ . '/../database/config.php';
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $errors[] = 'Hệ thống chưa thể ghi nhận yêu cầu đặt vé. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
        } else {
            $giaVe = $route[$loai];
            $tongTien = $giaVe * count($danhSachGhe);
            $maDatVe = 'TN' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $maNguoiDung = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
            $danhSachGheText = implode(', ', $danhSachGhe);
            $ghiChu = "Tuyến: " . route_label($route, $chieu) . " | Ngày: " . date('d/m/Y', strtotime($ngay)) . " lúc $gio | Loại: " . ($loai === 'ghe' ? 'Ghế ngồi' : 'Giường nằm') . " | Ghế: $danhSachGheText | TT dự kiến: " . $payments[$phuongThucThanhToan]['ten'];
            if ($coXeTrungChuyen && $diaChiDon) {
                $ghiChu .= " | Đón: $diaChiDon";
            }

            try {
                $cauLenh = $conn->prepare(
                    "INSERT INTO dat_ve (ma_dat_ve, ma_nguoi_dung, ten_khach_hang, sdt_khach_hang, email_khach_hang, tong_tien, thanh_tien, trang_thai, ghi_chu)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan', ?)"
                );
                $cauLenh->bind_param('sisssdds', $maDatVe, $maNguoiDung, $hoTen, $soDienThoai, $emailLienHe, $tongTien, $tongTien, $ghiChu);
                $cauLenh->execute();
                $cauLenh->close();

                $done = [
                    'ma' => $maDatVe,
                    'tuyen' => route_label($route, $chieu),
                    'ngay' => $ngay,
                    'gio' => $gio,
                    'loai' => $loai === 'ghe' ? 'Ghế ngồi' : 'Giường nằm',
                    'seats' => $danhSachGhe,
                    'tong' => $tongTien,
                    'ten' => $hoTen,
                    'sdt' => $soDienThoai,
                    'email' => $emailLienHe,
                    'tt' => $payments[$phuongThucThanhToan]['ten'],
                    'tc' => $coXeTrungChuyen ? $diaChiDon : '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if (!isset($_SESSION['my_bookings']) || !is_array($_SESSION['my_bookings'])) {
                    $_SESSION['my_bookings'] = [];
                }
                array_unshift($_SESSION['my_bookings'], $done);
            } catch (Throwable $loi) {
                error_log("Loi khi luu dat ve vao MySQL: " . $loi->getMessage());
                $errors[] = 'Chưa thể lưu yêu cầu đặt vé lúc này. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
            }
        }
    }
}
