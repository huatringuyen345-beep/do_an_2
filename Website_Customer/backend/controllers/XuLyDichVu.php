<?php
/*
 * Xu ly va luu yeu cau dat dich vu cua khach hang.
 * Dung cho don van chuyen hang hoa va xe trung chuyen toi nha.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    customer_require_account('DatDichVu.php');
}

$loi = [];
$thongBaoThanhCong = '';

// Xử lý đặt dịch vụ:
// - kiểm tra dữ liệu đầu vào
// - xác minh mã bảo mật CSRF
// - lưu đơn dịch vụ và chi tiết hàng hóa vào DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maBaoMat = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $maBaoMat)) {
        $loi[] = 'Phiên làm việc đã hết hạn hoặc mã bảo mật không khớp. Vui lòng thử lại.';
    }

    $maDichVu = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $slugDichVu = strtolower(trim((string)($_POST['loai_dich_vu'] ?? '')));
    $bangDichVu = [
        'van_chuyen_hang' => ['id' => 1, 'ten' => 'Vận chuyển hàng hóa'],
        'trung_chuyen' => ['id' => 2, 'ten' => 'Trung chuyển tận nơi'],
    ];

    if ($maDichVu === false && !isset($bangDichVu[$slugDichVu])) {
        $loi[] = 'Vui lòng chọn loại dịch vụ cần đặt.';
    }

    $hoTen = trim((string)($_POST['ho_ten'] ?? ''));
    $soDienThoai = preg_replace('/[\s.\-]/', '', (string)($_POST['so_dien_thoai'] ?? ''));
    $ngaySuDung = trim((string)($_POST['ngay_su_dung'] ?? ''));
    $soLuong = max(1, (int)($_POST['so_luong'] ?? 1));
    $loaiHang = trim((string)($_POST['loai_hang'] ?? ''));
    $khoiLuongKg = (float)($_POST['khoi_luong_kg'] ?? 0);
    $giaTriKhaiBao = (float)($_POST['gia_tri_khai_bao'] ?? 0);
    $diaChiDon = trim((string)($_POST['dia_chi_don'] ?? ''));
    $tenNguoiNhan = trim((string)($_POST['ten_nguoi_nhan'] ?? ''));
    $sdtNguoiNhan = preg_replace('/[\s.\-]/', '', (string)($_POST['sdt_nguoi_nhan'] ?? ''));
    $diaChiNhan = trim((string)($_POST['dia_chi_nhan'] ?? ''));
    $ghiChu = trim((string)($_POST['ghi_chu'] ?? ''));

    if ($hoTen === '' || mb_strlen($hoTen) < 2) {
        $loi[] = 'Vui lòng nhập họ và tên khách hàng.';
    }
    if (!preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) {
        $loi[] = 'Số điện thoại khách hàng không hợp lệ. Ví dụ: 0912345678.';
    }
    if ($ngaySuDung === '' || strtotime($ngaySuDung) === false) {
        $loi[] = 'Vui lòng chọn ngày sử dụng hợp lệ.';
    }
    if ($soLuong < 1 || $soLuong > 20) {
        $loi[] = 'Số lượng phải nằm trong khoảng từ 1 đến 20.';
    }
    if ($loaiHang === '') {
        $loi[] = 'Vui lòng nhập loại hàng hoặc vật dụng cần chuyển.';
    }
    if ($khoiLuongKg <= 0) {
        $loi[] = 'Khối lượng hàng phải lớn hơn 0 kg.';
    }
    if ($diaChiDon === '') {
        $loi[] = 'Vui lòng nhập địa chỉ lấy hàng hoặc địa chỉ đón.';
    }
    if ($slugDichVu === 'van_chuyen_hang' && ($tenNguoiNhan === '' || !preg_match('/^(0|\+84)\d{9,10}$/', $sdtNguoiNhan) || $diaChiNhan === '')) {
        $loi[] = 'Với dịch vụ vận chuyển hàng hóa, vui lòng nhập đầy đủ thông tin người nhận và địa chỉ giao hàng.';
    }

    if (!$loi) {
        require_once __DIR__ . '/../database/config.php';

        if (!isset($conn) || !($conn instanceof mysqli)) {
            $loi[] = 'Không thể kết nối cơ sở dữ liệu để đăng ký dịch vụ.';
        } else {
            $maDichVu = $maDichVu ?: ($bangDichVu[$slugDichVu]['id'] ?? 1);
            $giaThamKhao = 0.0;
            $tenDichVu = $bangDichVu[$slugDichVu]['ten'] ?? 'Dịch vụ';

            $query = $conn->prepare('SELECT ma_dich_vu, ten_dich_vu, gia_tham_khao FROM dich_vu WHERE ma_dich_vu = ? AND trang_thai = "hoat_dong" LIMIT 1');
            $query->bind_param('i', $maDichVu);
            $query->execute();
            $ketQua = $query->get_result();
            if ($ketQua && $ketQua->num_rows > 0) {
                $dong = $ketQua->fetch_assoc();
                $giaThamKhao = (float)($dong['gia_tham_khao'] ?? 0);
                $tenDichVu = (string)($dong['ten_dich_vu'] ?? $tenDichVu);
            }
            $query->close();

            $tongTien = round($giaThamKhao * $soLuong, 2);
            $maDatDichVu = 'DDV-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid((string)($_SESSION['account']['id'] ?? 'guest'), true)), 0, 6));
            $ngaySuDungSql = date('Y-m-d H:i:s', strtotime($ngaySuDung));
            $maNguoiDung = isset($_SESSION['account']['id']) ? (int)$_SESSION['account']['id'] : null;

            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare(
                    'INSERT INTO dat_dich_vu (ma_dat_dich_vu, ma_nguoi_dung, ma_dich_vu, ho_ten, so_dien_thoai, ngay_su_dung, so_luong, tong_tien, dia_chi_don, dia_chi_nhan, trang_thai, ghi_chu)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "cho_xu_ly", ?)'
                );
                $stmt->bind_param('siisssidsss', $maDatDichVu, $maNguoiDung, $maDichVu, $hoTen, $soDienThoai, $ngaySuDungSql, $soLuong, $tongTien, $diaChiDon, $diaChiNhan, $ghiChu);
                $stmt->execute();
                $maDatDichVuId = (int) $conn->insert_id;
                $stmt->close();

                $maVanDon = 'VD-' . strtoupper(substr(md5(uniqid($maDatDichVu . '-' . $maDatDichVuId, true)), 0, 8));
                $parcelStmt = $conn->prepare(
                    'INSERT INTO chi_tiet_hang (ma_dat_dich_vu_id, ten_nguoi_gui, sdt_nguoi_gui, ten_nguoi_nhan, sdt_nguoi_nhan, loai_hang, mo_ta_hang, khoi_luong_kg, gia_tri_khai_bao, ma_van_don, trang_thai_van_don)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "cho_lay_hang")'
                );
                $moTaHang = $ghiChu !== '' ? $ghiChu : ('Yêu cầu ' . $tenDichVu);
                $parcelStmt->bind_param('issssssdds', $maDatDichVuId, $hoTen, $soDienThoai, $tenNguoiNhan, $sdtNguoiNhan, $loaiHang, $moTaHang, $khoiLuongKg, $giaTriKhaiBao, $maVanDon);
                $parcelStmt->execute();
                $parcelStmt->close();

                $conn->commit();
                $thongBaoThanhCong = 'Đơn dịch vụ ' . $maDatDichVu . ' đã được ghi nhận. Nhân viên sẽ liên hệ xác nhận trong thời gian sớm nhất.';
                $ketQuaDatDichVu = [
                    'ma_don' => $maDatDichVu,
                    'service' => $tenDichVu,
                    'price' => $tongTien,
                ];
            } catch (Throwable $loiDuLieu) {
                $conn->rollback();
                error_log('Loi khi luu dat dich vu: ' . $loiDuLieu->getMessage());
                $loi[] = 'Không thể lưu đơn dịch vụ lúc này. Vui lòng thử lại sau.';
            }
        }
    }
}
