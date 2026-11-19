<?php
/*
 * Xu ly tai khoan khach hang va xac thuc nhan vien:
 * dang nhap/dang ky, dang xuat qua POST, cap nhat ho so va doi mat khau.
 * Duoc nap tu pages/Login.php va pages/TaiKhoan.php.
 */

require_once __DIR__ . '/../bootstrap.php';

$loi = [];
$thongBaoThanhCong = '';
$thongBaoLoi = '';
$formHienTai = $formHienTai ?? ($_GET['mode'] ?? 'login');
if (!in_array($formHienTai, ['login', 'register'], true)) {
    $formHienTai = 'login';
}
$duongDanSauDangNhap = customer_auth_next($_POST['next'] ?? $_GET['next'] ?? '');
$dangNhapNhanVien = (($_POST['area'] ?? $_GET['area'] ?? '') === 'staff');

$taiKhoan = $_SESSION['account'] ?? null;

// Doc va xoa thong bao tam trong phien.
if (!empty($_SESSION['account_flash'])) {
    if (!empty($_SESSION['account_flash']['success'])) {
        $thongBaoThanhCong = $_SESSION['account_flash']['success'];
    }
    if (!empty($_SESSION['account_flash']['error'])) {
        $thongBaoLoi = $_SESSION['account_flash']['error'];
    }
    unset($_SESSION['account_flash']);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Nap cau hinh ket noi co so du lieu.
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../models/NguoiDungModel.php';
$nguoiDungModel = isset($conn) && $conn instanceof mysqli
    ? new NguoiDungModel($conn)
    : null;

$phuongThucYeuCau = $_SERVER['REQUEST_METHOD'] ?? 'GET';
// Xử lý toàn bộ hành động xác thực của khách hàng và nhân viên:
// - đăng nhập
// - đăng ký
// - đăng xuất
// - cập nhật hồ sơ
// - đổi mật khẩu
if ($phuongThucYeuCau === 'POST') {
    $hanhDong = is_string($_POST['action'] ?? null) ? trim($_POST['action']) : '';
    $maBaoMat = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!hash_equals($_SESSION['csrf_token'], $maBaoMat)) {
        $loi[] = 'Phiên làm việc đã hết hạn hoặc mã bảo mật không khớp. Vui lòng thử lại.';
    } elseif ($hanhDong === 'logout') {
        unset($_SESSION['account']);
        if (!headers_sent()) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['account_flash'] = ['success' => 'Bạn đã đăng xuất tài khoản an toàn thành công.'];
            header('Location: Index.php', true, 303);
            exit;
        } else {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['account_flash'] = ['success' => 'Bạn đã đăng xuất tài khoản an toàn thành công.'];
        }
    } elseif ($hanhDong === 'login') {
        $formHienTai = 'login';
        $emailNhap = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $matKhau = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($emailNhap === '' || !filter_var($emailNhap, FILTER_VALIDATE_EMAIL)) {
            $loi[] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if ($matKhau === '') {
            $loi[] = 'Vui lòng nhập mật khẩu của bạn.';
        }

        if (!$loi) {
            $nguoiDungTimThay = null;
            if ($nguoiDungModel !== null) {
                try {
                    $nguoiDungTimThay = $nguoiDungModel->findForAuthentication($emailNhap);
                } catch (Throwable $loiDuLieu) {
                    error_log('Loi co so du lieu khi dang nhap: ' . $loiDuLieu->getMessage());
                }
            }

            $matKhauHopLe = false;
            if ($nguoiDungTimThay) {
                $matKhauDaLuu = (string) $nguoiDungTimThay['mat_khau'];
                $matKhauHopLe = password_verify($matKhau, $matKhauDaLuu);

                if (!$matKhauHopLe && hash_equals($matKhauDaLuu, $matKhau)) {
                    try {
                        $maBamMatKhau = password_hash($matKhau, PASSWORD_DEFAULT);
                        $maNguoiDung = (int) $nguoiDungTimThay['ma_nguoi_dung'];
                        $nguoiDungModel->updateLegacyPassword($maNguoiDung, $matKhauDaLuu, $maBamMatKhau);
                        $matKhauHopLe = true;
                    } catch (Throwable $loiDuLieu) {
                        error_log('Loi nang cap mat khau cu: ' . $loiDuLieu->getMessage());
                        $loi[] = 'Không thể cập nhật bảo mật tài khoản lúc này. Vui lòng thử lại sau.';
                    }
                }
            }

            $laNhanVien = $nguoiDungTimThay && in_array($nguoiDungTimThay['ten_vai_tro'], ['quan_tri', 'nhan_vien'], true);
            if (!$nguoiDungTimThay || !$matKhauHopLe) {
                if (!$loi) {
                    $loi[] = 'Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.';
                }
            } elseif ($nguoiDungTimThay['trang_thai'] !== 'hoat_dong') {
                $loi[] = 'Tài khoản của bạn hiện đang bị tạm khóa. Vui lòng liên hệ hotline để được hỗ trợ.';
            } elseif ($dangNhapNhanVien && !$laNhanVien) {
                $loi[] = 'Tài khoản này không có quyền truy cập khu vực nhân viên.';
            } else {
                $_SESSION['account'] = [
                    'id'      => (int)$nguoiDungTimThay['ma_nguoi_dung'],
                    'name'    => $nguoiDungTimThay['ho_ten'],
                    'email'   => $nguoiDungTimThay['email'],
                    'phone'   => $nguoiDungTimThay['so_dien_thoai'] ?? '',
                    'address' => $nguoiDungTimThay['dia_chi'] ?? '',
                    'role'    => $nguoiDungTimThay['ten_vai_tro'],
                ];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['account_flash'] = ['success' => 'Đăng nhập thành công! Chào mừng ' . $nguoiDungTimThay['ho_ten'] . ' quay trở lại.'];
                if (!headers_sent()) {
                    session_regenerate_id(true);
                    $trangDich = $laNhanVien ? '../../Website_Manage/admin/modules/bookings/bookings.php' : $duongDanSauDangNhap;
                    header('Location: ' . $trangDich, true, 303);
                    exit;
                }
            }
        }
    } elseif ($hanhDong === 'register') {
        if ($dangNhapNhanVien) {
            $loi[] = 'Nhân viên không thể tự đăng ký tài khoản khu vực quản lý.';
        }
        $formHienTai = 'register';
        $hoTen = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        $emailNhap = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $soDienThoai = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
        $soDienThoai = preg_replace('/[\s.\-]/', '', $soDienThoai);
        $matKhau = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $matKhauXacNhan = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100) {
            $loi[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
        }
        if ($emailNhap === '' || !filter_var($emailNhap, FILTER_VALIDATE_EMAIL) || strlen($emailNhap) > 150) {
            $loi[] = 'Vui lòng nhập địa chỉ email hợp lệ (tối đa 150 ký tự).';
        }
        if ($soDienThoai !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) {
            $loi[] = 'Số điện thoại không hợp lệ. Ví dụ: 0912345678.';
        }
        if (strlen($matKhau) < 8) {
            $loi[] = 'Mật khẩu phải có ít nhất 8 ký tự để đảm bảo an toàn.';
        }
        if ($matKhau !== $matKhauXacNhan) {
            $loi[] = 'Mật khẩu xác nhận không khớp với mật khẩu đã nhập.';
        }

        if (!$loi) {
            if ($nguoiDungModel !== null) {
                try {
                    if ($nguoiDungModel->emailExists($emailNhap)) {
                        $loi[] = 'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.';
                    } else {
                        $maVaiTro = $nguoiDungModel->findRoleId('khach_hang');
                        $maVaiTro = $maVaiTro === null ? 3 : $maVaiTro;

                        $maBamMatKhau = password_hash($matKhau, PASSWORD_DEFAULT);
                        $maNguoiDungMoi = $nguoiDungModel->createCustomer(
                            $maVaiTro,
                            $hoTen,
                            $emailNhap,
                            $soDienThoai,
                            $maBamMatKhau
                        );

                        $_SESSION['account'] = [
                            'id'      => $maNguoiDungMoi,
                            'name'    => $hoTen,
                            'email'   => $emailNhap,
                            'phone'   => $soDienThoai,
                            'address' => '',
                            'role'    => 'khach_hang',
                        ];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['account_flash'] = ['success' => 'Tạo tài khoản thành công! Bạn đã được đăng nhập tự động.'];
                        if (!headers_sent()) {
                            session_regenerate_id(true);
                            header('Location: ' . $duongDanSauDangNhap, true, 303);
                            exit;
                        }
                    }
                } catch (Throwable $loiDuLieu) {
                    error_log('Loi co so du lieu khi dang ky: ' . $loiDuLieu->getMessage());
                    $loi[] = 'Không thể đăng ký lúc này. Vui lòng thử lại sau.';
                }
            } else {
                $loi[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng khởi động MySQL trong XAMPP.';
            }
        }
    } elseif ($hanhDong === 'update_profile') {
        if (!$taiKhoan) {
            $loi[] = 'Bạn cần đăng nhập để cập nhật thông tin.';
        } else {
            $hoTen = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
            $soDienThoai = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
            $soDienThoai = preg_replace('/[\s.\-]/', '', $soDienThoai);
            $diaChi = trim(is_string($_POST['address'] ?? null) ? $_POST['address'] : '');

            if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100) {
                $loi[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
            }
            if ($soDienThoai !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) {
                $loi[] = 'Số điện thoại không hợp lệ.';
            }

            if (!$loi) {
                if ($nguoiDungModel !== null) {
                    try {
                        $maNguoiDung = (int)$taiKhoan['id'];
                        $nguoiDungModel->updateProfile($maNguoiDung, $hoTen, $soDienThoai, $diaChi);

                        $_SESSION['account']['name'] = $hoTen;
                        $_SESSION['account']['phone'] = $soDienThoai;
                        $_SESSION['account']['address'] = $diaChi;
                        $_SESSION['account_flash'] = ['success' => 'Thông tin cá nhân đã được cập nhật thành công!'];
                        if (!headers_sent()) {
                            header('Location: TaiKhoan.php', true, 303);
                            exit;
                        }
                    } catch (Throwable $loiDuLieu) {
                        error_log('Loi cap nhat ho so: ' . $loiDuLieu->getMessage());
                        $loi[] = 'Lỗi cập nhật dữ liệu. Vui lòng thử lại.';
                    }
                }
            }
        }
    } elseif ($hanhDong === 'change_password') {
        if (!$taiKhoan) {
            $loi[] = 'Bạn cần đăng nhập để đổi mật khẩu.';
        } else {
            $matKhauCu = is_string($_POST['old_password'] ?? null) ? $_POST['old_password'] : '';
            $matKhauMoi = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
            $matKhauXacNhan = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

            if (strlen($matKhauMoi) < 8) {
                $loi[] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            }
            if ($matKhauMoi !== $matKhauXacNhan) {
                $loi[] = 'Mật khẩu mới xác nhận không khớp.';
            }

            if (!$loi && $nguoiDungModel !== null) {
                try {
                    $maNguoiDung = (int)$taiKhoan['id'];
                    $matKhauDaLuu = $nguoiDungModel->findPasswordHash($maNguoiDung);

                    if (!$matKhauDaLuu || !password_verify($matKhauCu, $matKhauDaLuu)) {
                        $loi[] = 'Mật khẩu hiện tại không chính xác.';
                    } else {
                        $maBamMatKhauMoi = password_hash($matKhauMoi, PASSWORD_DEFAULT);
                        $nguoiDungModel->updatePasswordHash($maNguoiDung, $maBamMatKhauMoi);

                        $_SESSION['account_flash'] = ['success' => 'Đổi mật khẩu thành công!'];
                        if (!headers_sent()) {
                            header('Location: TaiKhoan.php?tab=mat-khau', true, 303);
                            exit;
                        }
                    }
                } catch (Throwable $loiDuLieu) {
                    error_log('Loi he thong khi doi mat khau: ' . $loiDuLieu->getMessage());
                    $loi[] = 'Lỗi hệ thống khi đổi mật khẩu. Vui lòng thử lại.';
                }
            }
        }
    }

    if ($loi) {
        $thongBaoLoi = implode(' ', $loi);
    }
}

$taiKhoan = $_SESSION['account'] ?? null;
