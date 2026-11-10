<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('dat-ve.php');
}

$src = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

$route = find_route($src['tuyen'] ?? '') ?? find_route('tp-hcm');
$chieu = (($src['chieu'] ?? 'di') === 've') ? 've' : 'di';

$ngay = $src['ngay'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngay) || strtotime($ngay) === false) $ngay = date('Y-m-d');
if ($ngay < date('Y-m-d')) $ngay = date('Y-m-d');

$loai = $src['loai'] ?? '';
if (!in_array($loai, ['ghe', 'giuong'], true) || empty($route[$loai])) {
    $loai = !empty($route['giuong']) ? 'giuong' : 'ghe';
}
$gio = $src['gio'] ?? $route['khoi_hanh'][0];
if (!in_array($gio, $route['khoi_hanh'], true)) $gio = $route['khoi_hanh'][0];
$want = max(1, min(10, (int)($src['sl'] ?? 1)));

function route_label($route, $direction)
{
    return $direction === 've' ? $route['ten'] . ' – Bạc Liêu' : 'Bạc Liêu – ' . $route['ten'];
}

$errors = [];
$done = null;
$seatsPosted = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? '');
    $sdt = preg_replace('/[\s.\-]/', '', $_POST['sdt'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tt = $_POST['thanh_toan'] ?? '';
    $trungChuyen = !empty($_POST['trung_chuyen']);
    $diaChi = trim($_POST['dia_chi_don'] ?? '');

    $seats = array_values(array_unique(array_filter(array_map('trim', explode(',', $_POST['ghe'] ?? '')))));
    $seatsPosted = implode(',', $seats);
    $seatPrefixes = $loai === 'ghe' ? ['G'] : ['D', 'T'];
    $validSeats = [];
    foreach ($seatPrefixes as $prefix) {
        $seatCount = $loai === 'ghe' ? 32 : 18;
        for ($number = 1; $number <= $seatCount; $number++) {
            $validSeats[] = $prefix . str_pad((string)$number, 2, '0', STR_PAD_LEFT);
        }
    }
    foreach ($seats as $seat) {
        if (!in_array($seat, $validSeats, true)) {
            $errors[] = 'Mã chỗ ngồi không hợp lệ.';
            break;
        }
    }

    if ($ten === '' || mb_strlen($ten) < 2) $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $sdt)) $errors[] = 'Số điện thoại chưa đúng. Ví dụ: 0912345678.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email chưa đúng định dạng.';
    if (count($seats) < 1) $errors[] = 'Vui lòng chọn chỗ trên sơ đồ xe.';
    elseif (count($seats) !== $want) $errors[] = 'Vui lòng chọn đủ ' . $want . ' chỗ theo số hành khách.';
    if (count($seats) > 10) $errors[] = 'Mỗi lần đặt tối đa 10 chỗ.';
    if (!isset($payments[$tt])) $errors[] = 'Vui lòng chọn hình thức thanh toán.';
    if ($trungChuyen && $diaChi === '') $errors[] = 'Bạn đã chọn xe trung chuyển, vui lòng nhập địa chỉ đón.';

    if (!$errors) {
        require_once __DIR__ . '/../database/config.php';
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $errors[] = 'Hệ thống chưa thể ghi nhận yêu cầu đặt vé. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
        } else {
            $gia = $route[$loai];
            $tong = $gia * count($seats);
            $ma = 'TN' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $userId = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;
            $seatsStr = implode(', ', $seats);
            $note = "Tuyến: " . route_label($route, $chieu) . " | Ngày: " . date('d/m/Y', strtotime($ngay)) . " lúc $gio | Loại: " . ($loai === 'ghe' ? 'Ghế ngồi' : 'Giường nằm') . " | Ghế: $seatsStr | TT dự kiến: " . $payments[$tt]['ten'];
            if ($trungChuyen && $diaChi) {
                $note .= " | Đón: $diaChi";
            }

            try {
                $stmt = $conn->prepare(
                    "INSERT INTO dat_ve (ma_dat_ve, ma_nguoi_dung, ten_khach_hang, sdt_khach_hang, email_khach_hang, tong_tien, thanh_tien, trang_thai, ghi_chu)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan', ?)"
                );
                $stmt->bind_param('sisssdds', $ma, $userId, $ten, $sdt, $email, $tong, $tong, $note);
                $stmt->execute();
                $stmt->close();

                $done = [
                    'ma' => $ma,
                    'tuyen' => route_label($route, $chieu),
                    'ngay' => $ngay,
                    'gio' => $gio,
                    'loai' => $loai === 'ghe' ? 'Ghế ngồi' : 'Giường nằm',
                    'seats' => $seats,
                    'tong' => $tong,
                    'ten' => $ten,
                    'sdt' => $sdt,
                    'email' => $email,
                    'tt' => $payments[$tt]['ten'],
                    'tc' => $trungChuyen ? $diaChi : '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if (!isset($_SESSION['my_bookings']) || !is_array($_SESSION['my_bookings'])) {
                    $_SESSION['my_bookings'] = [];
                }
                array_unshift($_SESSION['my_bookings'], $done);
            } catch (Throwable $e) {
                error_log("Failed to insert booking into MySQL: " . $e->getMessage());
                $errors[] = 'Chưa thể lưu yêu cầu đặt vé lúc này. Vui lòng thử lại sau hoặc liên hệ nhà xe.';
            }
        }
    }
}
