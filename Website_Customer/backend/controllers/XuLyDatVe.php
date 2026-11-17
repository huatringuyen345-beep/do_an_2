<?php
/*
 * Xu ly va kiem tra yeu cau dat ve cua khach hang.
 * Tao ban ghi dat ve trong co so du lieu va luu thong tin vua dat vao phien.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('DatVe.php');
}

$duLieuYeuCau = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

// Hàm định dạng tên tuyến theo hướng đi/đến để hiển thị trên màn hình xác nhận đặt vé.
function route_label($tuyenHienTai, $direction)
{
    return $direction === 've' ? $tuyenHienTai['ten'] . ' – Bạc Liêu' : 'Bạc Liêu – ' . $tuyenHienTai['ten'];
}

$tuyenHienTai = find_route($duLieuYeuCau['tuyen'] ?? '') ?? find_route('tp-hcm');
$chieu = (($duLieuYeuCau['chieu'] ?? 'di') === 've') ? 've' : 'di';

$ngay = $duLieuYeuCau['ngay'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngay) || strtotime($ngay) === false) $ngay = date('Y-m-d');
if ($ngay < date('Y-m-d')) $ngay = date('Y-m-d');

$loai = $duLieuYeuCau['loai'] ?? '';
if (!in_array($loai, ['ghe', 'giuong'], true) || empty($tuyenHienTai[$loai])) {
    $loai = !empty($tuyenHienTai['giuong']) ? 'giuong' : 'ghe';
}
$gio = $duLieuYeuCau['gio'] ?? $tuyenHienTai['khoi_hanh'][0];
if (!in_array($gio, $tuyenHienTai['khoi_hanh'], true)) $gio = $tuyenHienTai['khoi_hanh'][0];
$soLuongMuonDat = max(1, min(10, (int)($duLieuYeuCau['sl'] ?? 1)));

$loi = [];
$ketQuaDatVe = null;
$seatsPosted = '';

// Xử lý yêu cầu đặt vé:
// - kiểm tra thông tin khách hàng
// - validate số ghế và thanh toán
// - lưu thông tin đặt vé vào cơ sở dữ liệu
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
            $loi[] = 'Mã chỗ ngồi không hợp lệ.';
            break;
        }
    }

    if ($hoTen === '' || mb_strlen($hoTen) < 2) $loi[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) $loi[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($emailLienHe !== '' && !filter_var($emailLienHe, FILTER_VALIDATE_EMAIL)) $loi[] = 'Email chưa đúng định dạng.';
    if (count($danhSachGhe) < 1) $loi[] = 'Vui lòng chọn chỗ trên sơ đồ xe.';
    elseif (count($danhSachGhe) !== $soLuongMuonDat) $loi[] = 'Vui lòng chọn đủ ' . $soLuongMuonDat . ' chỗ theo số hành khách.';
    if (count($danhSachGhe) > 10) $loi[] = 'Mỗi lần đặt tối đa 10 chỗ.';
    if (!isset($payments[$phuongThucThanhToan])) $loi[] = 'Vui lòng chọn hình thức thanh toán.';
    if ($coXeTrungChuyen && $diaChiDon === '') $loi[] = 'Bạn đã chọn xe trung chuyển, vui lòng nhập địa chỉ đón.';

    if (!$loi) {
        require_once __DIR__ . '/../database/config.php';
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $loi[] = 'Hệ thống chưa thể ghi nhận yêu cầu đặt vé. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
        } else {
            $giaVe = $tuyenHienTai[$loai];
            $tongTien = $giaVe * count($danhSachGhe);
            $maDatVe = 'TN' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $maNguoiDung = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
            $danhSachGheText = implode(', ', $danhSachGhe);
            $ghiChu = "Tuyến: " . route_label($tuyenHienTai, $chieu) . " | Ngày: " . date('d/m/Y', strtotime($ngay)) . " lúc $gio | Loại: " . ($loai === 'ghe' ? 'Ghế ngồi' : 'Giường nằm') . " | Ghế: $danhSachGheText | TT dự kiến: " . $payments[$phuongThucThanhToan]['ten'];
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
                $maDatVeId = (int) $conn->insert_id;
                $cauLenh->close();

                $maGiaoDich = 'TN-' . $maDatVe . '-' . date('YmdHis');
                $ghiChuThanhToan = 'Đặt vé ' . $maDatVe . ' · ' . ($payments[$phuongThucThanhToan]['ten'] ?? 'Thanh toán tại quầy') . ' · ' . count($danhSachGhe) . ' ghế';
                $phuongThucLuu = customer_payment_method_for_storage($conn, $phuongThucThanhToan);
                $stmtThanhToan = $conn->prepare(
                    "INSERT INTO thanh_toan (ma_dat_ve_id, ma_giao_dich, phuong_thuc, so_tien, trang_thai, ghi_chu)
                     VALUES (?, ?, ?, ?, 'cho_thanh_toan', ?)"
                );
                $stmtThanhToan->bind_param('issds', $maDatVeId, $maGiaoDich, $phuongThucLuu, $tongTien, $ghiChuThanhToan);
                $stmtThanhToan->execute();
                $stmtThanhToan->close();

                $ketQuaDatVe = [
                    'ma' => $maDatVe,
                    'tuyen' => route_label($tuyenHienTai, $chieu),
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
                    'payment_status' => 'cho_thanh_toan',
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if (!isset($_SESSION['my_bookings']) || !is_array($_SESSION['my_bookings'])) {
                    $_SESSION['my_bookings'] = [];
                }
                array_unshift($_SESSION['my_bookings'], $ketQuaDatVe);
            } catch (Throwable $loiDuLieu) {
                error_log("Loi khi luu dat ve vao MySQL: " . $loiDuLieu->getMessage());
                $loi[] = 'Chưa thể lưu yêu cầu đặt vé lúc này. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
            }
        }
    }
}
