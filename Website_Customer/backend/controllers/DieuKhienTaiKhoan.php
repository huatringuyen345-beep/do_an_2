<?php
/*
 * Xu ly tai khoan khach hang va xac thuc nhan vien:
 * dang nhap/dang ky, dang xuat qua POST, cap nhat ho so va doi mat khau.
 * Duoc nap tu pages/login.php va pages/tai-khoan.php.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errors = [];
$successMessage = '';
$errorMessage = '';
$activeForm = $activeForm ?? ($_GET['mode'] ?? 'login');
if (!in_array($activeForm, ['login', 'register'], true)) {
    $activeForm = 'login';
}
$authNext = customer_auth_next($_POST['next'] ?? $_GET['next'] ?? '');
$staffLogin = (($_POST['area'] ?? $_GET['area'] ?? '') === 'staff');

$account = $_SESSION['account'] ?? null;

// Doc va xoa thong bao tam trong phien.
if (!empty($_SESSION['account_flash'])) {
    if (!empty($_SESSION['account_flash']['success'])) {
        $successMessage = $_SESSION['account_flash']['success'];
    }
    if (!empty($_SESSION['account_flash']['error'])) {
        $errorMessage = $_SESSION['account_flash']['error'];
    }
    unset($_SESSION['account_flash']);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Nap cau hinh ket noi co so du lieu.
require_once __DIR__ . '/../database/config.php';

$phuongThucYeuCau = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($phuongThucYeuCau === 'POST') {
    $hanhDong = is_string($_POST['action'] ?? null) ? trim($_POST['action']) : '';
    $maBaoMat = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!hash_equals($_SESSION['csrf_token'], $maBaoMat)) {
        $errors[] = 'Phiên làm việc đã hết hạn hoặc mã bảo mật không khớp. Vui lòng thử lại.';
    } elseif ($hanhDong === 'logout') {
        unset($_SESSION['account']);
        if (!headers_sent()) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['account_flash'] = ['success' => 'Bạn đã đăng xuất tài khoản an toàn thành công.'];
            header('Location: index.php', true, 303);
            exit;
        } else {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['account_flash'] = ['success' => 'Bạn đã đăng xuất tài khoản an toàn thành công.'];
        }
    } elseif ($hanhDong === 'login') {
        $activeForm = 'login';
        $emailNhap = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $matKhau = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($emailNhap === '' || !filter_var($emailNhap, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if ($matKhau === '') {
            $errors[] = 'Vui lòng nhập mật khẩu của bạn.';
        }

        if (!$errors) {
            $nguoiDungTimThay = null;
            if (isset($conn) && $conn instanceof mysqli) {
                try {
                    $cauLenh = $conn->prepare(
                        "SELECT nd.ma_nguoi_dung, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.dia_chi, nd.mat_khau, nd.trang_thai, vt.ten_vai_tro
                         FROM nguoi_dung nd
                         INNER JOIN vai_tro vt ON vt.ma_vai_tro = nd.ma_vai_tro
                         WHERE nd.email = ? AND vt.ten_vai_tro IN ('quan_tri', 'nhan_vien', 'khach_hang')
                         LIMIT 1"
                    );
                    $cauLenh->bind_param('s', $emailNhap);
                    $cauLenh->execute();
                    $nguoiDungTimThay = $cauLenh->get_result()->fetch_assoc();
                } catch (Throwable $loi) {
                    error_log('Loi co so du lieu khi dang nhap: ' . $loi->getMessage());
                }
            }

            $matKhauHopLe = false;
            if ($nguoiDungTimThay) {
                $matKhauDaLuu = (string) $nguoiDungTimThay['mat_khau'];
                $matKhauHopLe = password_verify($matKhau, $matKhauDaLuu);

                if (!$matKhauHopLe && hash_equals($matKhauDaLuu, $matKhau)) {
                    try {
                        $maBamMatKhau = password_hash($matKhau, PASSWORD_DEFAULT);
                        $cauLenhCapNhat = $conn->prepare(
                            'UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ? AND mat_khau = ?'
                        );
                        $maNguoiDung = (int) $nguoiDungTimThay['ma_nguoi_dung'];
                        $cauLenhCapNhat->bind_param('sis', $maBamMatKhau, $maNguoiDung, $matKhauDaLuu);
                        $cauLenhCapNhat->execute();
                        $cauLenhCapNhat->close();
                        $matKhauHopLe = true;
                    } catch (Throwable $loi) {
                        error_log('Loi nang cap mat khau cu: ' . $loi->getMessage());
                        $errors[] = 'Không thể cập nhật bảo mật tài khoản lúc này. Vui lòng thử lại sau.';
                    }
                }
            }

            $laNhanVien = $nguoiDungTimThay && in_array($nguoiDungTimThay['ten_vai_tro'], ['quan_tri', 'nhan_vien'], true);
            if (!$nguoiDungTimThay || !$matKhauHopLe) {
                if (!$errors) {
                    $errors[] = 'Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.';
                }
            } elseif ($nguoiDungTimThay['trang_thai'] !== 'hoat_dong') {
                $errors[] = 'Tài khoản của bạn hiện đang bị tạm khóa. Vui lòng liên hệ hotline để được hỗ trợ.';
            } elseif ($staffLogin && !$laNhanVien) {
                $errors[] = 'Tài khoản này không có quyền truy cập khu vực nhân viên.';
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
                    $trangDich = $laNhanVien ? '../../Website_Manage/admin/modules/bookings/bookings.php' : $authNext;
                    header('Location: ' . $trangDich, true, 303);
                    exit;
                }
            }
        }
    } elseif ($hanhDong === 'register') {
        if ($staffLogin) {
            $errors[] = 'Nhân viên không thể tự đăng ký tài khoản khu vực quản lý.';
        }
        $activeForm = 'register';
        $hoTen = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        $emailNhap = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $soDienThoai = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
        $soDienThoai = preg_replace('/[\s.\-]/', '', $soDienThoai);
        $matKhau = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $matKhauXacNhan = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100) {
            $errors[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
        }
        if ($emailNhap === '' || !filter_var($emailNhap, FILTER_VALIDATE_EMAIL) || strlen($emailNhap) > 150) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ (tối đa 150 ký tự).';
        }
        if ($soDienThoai !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) {
            $errors[] = 'Số điện thoại không hợp lệ. Ví dụ: 0912345678.';
        }
        if (strlen($matKhau) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự để đảm bảo an toàn.';
        }
        if ($matKhau !== $matKhauXacNhan) {
            $errors[] = 'Mật khẩu xác nhận không khớp với mật khẩu đã nhập.';
        }

        if (!$errors) {
            if (isset($conn) && $conn instanceof mysqli) {
                try {
                    // Kiem tra email da duoc su dung chua.
                    $cauLenhKiemTra = $conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1");
                    $cauLenhKiemTra->bind_param('s', $emailNhap);
                    $cauLenhKiemTra->execute();
                    if ($cauLenhKiemTra->get_result()->fetch_assoc()) {
                        $errors[] = 'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.';
                    } else {
                        // Lay ma vai tro khach hang.
                        $cauLenhVaiTro = $conn->prepare("SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = 'khach_hang' LIMIT 1");
                        $cauLenhVaiTro->execute();
                        $vaiTro = $cauLenhVaiTro->get_result()->fetch_assoc();
                        $maVaiTro = $vaiTro ? (int)$vaiTro['ma_vai_tro'] : 3;

                        $maBamMatKhau = password_hash($matKhau, PASSWORD_DEFAULT);
                        $cauLenh = $conn->prepare(
                            'INSERT INTO nguoi_dung (ma_vai_tro, ho_ten, email, so_dien_thoai, mat_khau, trang_thai) 
                             VALUES (?, ?, ?, ?, ?, "hoat_dong")'
                        );
                        $cauLenh->bind_param('issss', $maVaiTro, $hoTen, $emailNhap, $soDienThoai, $maBamMatKhau);
                        $cauLenh->execute();
                        $maNguoiDungMoi = (int)$conn->insert_id;

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
                            header('Location: ' . $authNext, true, 303);
                            exit;
                        }
                    }
                } catch (Throwable $loi) {
                    error_log('Loi co so du lieu khi dang ky: ' . $loi->getMessage());
                    $errors[] = 'Không thể đăng ký lúc này. Vui lòng thử lại sau.';
                }
            } else {
                $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng khởi động MySQL trong XAMPP.';
            }
        }
    } elseif ($hanhDong === 'update_profile') {
        if (!$account) {
            $errors[] = 'Bạn cần đăng nhập để cập nhật thông tin.';
        } else {
            $hoTen = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
            $soDienThoai = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
            $soDienThoai = preg_replace('/[\s.\-]/', '', $soDienThoai);
            $diaChi = trim(is_string($_POST['address'] ?? null) ? $_POST['address'] : '');

            if (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 100) {
                $errors[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
            }
            if ($soDienThoai !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $soDienThoai)) {
                $errors[] = 'Số điện thoại không hợp lệ.';
            }

            if (!$errors) {
                if (isset($conn) && $conn instanceof mysqli) {
                    try {
                        $cauLenh = $conn->prepare(
                            'UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE ma_nguoi_dung = ?'
                        );
                        $maNguoiDung = (int)$account['id'];
                        $cauLenh->bind_param('sssi', $hoTen, $soDienThoai, $diaChi, $maNguoiDung);
                        $cauLenh->execute();

                        $_SESSION['account']['name'] = $hoTen;
                        $_SESSION['account']['phone'] = $soDienThoai;
                        $_SESSION['account']['address'] = $diaChi;
                        $_SESSION['account_flash'] = ['success' => 'Thông tin cá nhân đã được cập nhật thành công!'];
                        if (!headers_sent()) {
                            header('Location: tai-khoan.php', true, 303);
                            exit;
                        }
                    } catch (Throwable $loi) {
                        error_log('Loi cap nhat ho so: ' . $loi->getMessage());
                        $errors[] = 'Lỗi cập nhật dữ liệu. Vui lòng thử lại.';
                    }
                }
            }
        }
    } elseif ($hanhDong === 'change_password') {
        if (!$account) {
            $errors[] = 'Bạn cần đăng nhập để đổi mật khẩu.';
        } else {
            $matKhauCu = is_string($_POST['old_password'] ?? null) ? $_POST['old_password'] : '';
            $matKhauMoi = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
            $matKhauXacNhan = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

            if (strlen($matKhauMoi) < 8) {
                $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            }
            if ($matKhauMoi !== $matKhauXacNhan) {
                $errors[] = 'Mật khẩu mới xác nhận không khớp.';
            }

            if (!$errors && isset($conn) && $conn instanceof mysqli) {
                try {
                    $maNguoiDung = (int)$account['id'];
                    $cauLenh = $conn->prepare('SELECT mat_khau FROM nguoi_dung WHERE ma_nguoi_dung = ? LIMIT 1');
                    $cauLenh->bind_param('i', $maNguoiDung);
                    $cauLenh->execute();
                    $nguoiDung = $cauLenh->get_result()->fetch_assoc();

                    if (!$nguoiDung || !password_verify($matKhauCu, $nguoiDung['mat_khau'])) {
                        $errors[] = 'Mật khẩu hiện tại không chính xác.';
                    } else {
                        $maBamMatKhauMoi = password_hash($matKhauMoi, PASSWORD_DEFAULT);
                        $cauLenhCapNhat = $conn->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?');
                        $cauLenhCapNhat->bind_param('si', $maBamMatKhauMoi, $maNguoiDung);
                        $cauLenhCapNhat->execute();

                        $_SESSION['account_flash'] = ['success' => 'Đổi mật khẩu thành công!'];
                        if (!headers_sent()) {
                            header('Location: tai-khoan.php?tab=mat-khau', true, 303);
                            exit;
                        }
                    }
                } catch (Throwable $loi) {
                    error_log('Loi he thong khi doi mat khau: ' . $loi->getMessage());
                    $errors[] = 'Lỗi hệ thống khi đổi mật khẩu. Vui lòng thử lại.';
                }
            }
        }
    }

    if ($errors) {
        $errorMessage = implode(' ', $errors);
    }
}

$account = $_SESSION['account'] ?? null;
