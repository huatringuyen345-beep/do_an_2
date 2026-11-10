<?php
/*
 * Bộ điều khiển tài khoản khách hàng và đăng nhập nhân viên:
 * xử lý đăng nhập/đăng ký, đăng xuất qua POST, cập nhật hồ sơ và đổi mật khẩu.
 * Dùng từ pages/login.php và pages/tai-khoan.php.
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

// Xử lý flash message
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

// Kết nối database
require_once __DIR__ . '/../database/config.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? trim($_POST['action']) : '';
    $csrfToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Phiên làm việc đã hết hạn hoặc mã bảo mật không khớp. Vui lòng thử lại.';
    } elseif ($action === 'logout') {
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
    } elseif ($action === 'login') {
        $activeForm = 'login';
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if ($password === '') {
            $errors[] = 'Vui lòng nhập mật khẩu của bạn.';
        }

        if (!$errors) {
            $userFound = null;
            if (isset($conn) && $conn instanceof mysqli) {
                try {
                    $stmt = $conn->prepare(
                        "SELECT nd.ma_nguoi_dung, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.dia_chi, nd.mat_khau, nd.trang_thai, vt.ten_vai_tro
                         FROM nguoi_dung nd
                         INNER JOIN vai_tro vt ON vt.ma_vai_tro = nd.ma_vai_tro
                         WHERE nd.email = ? AND vt.ten_vai_tro IN ('quan_tri', 'nhan_vien', 'khach_hang')
                         LIMIT 1"
                    );
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $userFound = $stmt->get_result()->fetch_assoc();
                } catch (Throwable $e) {
                    error_log('Login DB error: ' . $e->getMessage());
                }
            }

            $isStaff = $userFound && in_array($userFound['ten_vai_tro'], ['quan_tri', 'nhan_vien'], true);
            if (!$userFound || !password_verify($password, $userFound['mat_khau'])) {
                $errors[] = 'Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.';
            } elseif ($userFound['trang_thai'] !== 'hoat_dong') {
                $errors[] = 'Tài khoản của bạn hiện đang bị tạm khóa. Vui lòng liên hệ hotline để được hỗ trợ.';
            } elseif ($staffLogin && !$isStaff) {
                $errors[] = 'Tài khoản này không có quyền truy cập khu vực nhân viên.';
            } else {
                $_SESSION['account'] = [
                    'id'      => (int)$userFound['ma_nguoi_dung'],
                    'name'    => $userFound['ho_ten'],
                    'email'   => $userFound['email'],
                    'phone'   => $userFound['so_dien_thoai'] ?? '',
                    'address' => $userFound['dia_chi'] ?? '',
                    'role'    => $userFound['ten_vai_tro'],
                ];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['account_flash'] = ['success' => 'Đăng nhập thành công! Chào mừng ' . $userFound['ho_ten'] . ' quay trở lại.'];
                if (!headers_sent()) {
                    session_regenerate_id(true);
                    $destination = $isStaff ? '../../Website_Manage/admin/modules/bookings/bookings.php' : $authNext;
                    header('Location: ' . $destination, true, 303);
                    exit;
                }
            }
        }
    } elseif ($action === 'register') {
        if ($staffLogin) {
            $errors[] = 'Nhân viên không thể tự đăng ký tài khoản khu vực quản lý.';
        }
        $activeForm = 'register';
        $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $phone = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
        $phone = preg_replace('/[\s.\-]/', '', $phone);
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Vui lòng nhập địa chỉ email hợp lệ (tối đa 150 ký tự).';
        }
        if ($phone !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $phone)) {
            $errors[] = 'Số điện thoại không hợp lệ. Ví dụ: 0912345678.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự để đảm bảo an toàn.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp với mật khẩu đã nhập.';
        }

        if (!$errors) {
            if (isset($conn) && $conn instanceof mysqli) {
                try {
                    // Kiểm tra email đã có người dùng chưa
                    $checkStmt = $conn->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1");
                    $checkStmt->bind_param('s', $email);
                    $checkStmt->execute();
                    if ($checkStmt->get_result()->fetch_assoc()) {
                        $errors[] = 'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.';
                    } else {
                        // Lấy vai trò khách hàng
                        $roleStmt = $conn->prepare("SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = 'khach_hang' LIMIT 1");
                        $roleStmt->execute();
                        $role = $roleStmt->get_result()->fetch_assoc();
                        $roleId = $role ? (int)$role['ma_vai_tro'] : 3;

                        $pwHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare(
                            'INSERT INTO nguoi_dung (ma_vai_tro, ho_ten, email, so_dien_thoai, mat_khau, trang_thai) 
                             VALUES (?, ?, ?, ?, ?, "hoat_dong")'
                        );
                        $stmt->bind_param('issss', $roleId, $name, $email, $phone, $pwHash);
                        $stmt->execute();
                        $newId = (int)$conn->insert_id;

                        $_SESSION['account'] = [
                            'id'      => $newId,
                            'name'    => $name,
                            'email'   => $email,
                            'phone'   => $phone,
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
                } catch (Throwable $e) {
                    error_log('Register DB error: ' . $e->getMessage());
                    $errors[] = 'Không thể đăng ký lúc này. Vui lòng thử lại sau.';
                }
            } else {
                $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng khởi động MySQL trong XAMPP.';
            }
        }
    } elseif ($action === 'update_profile') {
        if (!$account) {
            $errors[] = 'Bạn cần đăng nhập để cập nhật thông tin.';
        } else {
            $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
            $phone = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
            $phone = preg_replace('/[\s.\-]/', '', $phone);
            $address = trim(is_string($_POST['address'] ?? null) ? $_POST['address'] : '');

            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                $errors[] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
            }
            if ($phone !== '' && !preg_match('/^(0|\+84)\d{9,10}$/', $phone)) {
                $errors[] = 'Số điện thoại không hợp lệ.';
            }

            if (!$errors) {
                if (isset($conn) && $conn instanceof mysqli) {
                    try {
                        $stmt = $conn->prepare(
                            'UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE ma_nguoi_dung = ?'
                        );
                        $userId = (int)$account['id'];
                        $stmt->bind_param('sssi', $name, $phone, $address, $userId);
                        $stmt->execute();

                        $_SESSION['account']['name'] = $name;
                        $_SESSION['account']['phone'] = $phone;
                        $_SESSION['account']['address'] = $address;
                        $_SESSION['account_flash'] = ['success' => 'Thông tin cá nhân đã được cập nhật thành công!'];
                        if (!headers_sent()) {
                            header('Location: tai-khoan.php', true, 303);
                            exit;
                        }
                    } catch (Throwable $e) {
                        error_log('Update profile error: ' . $e->getMessage());
                        $errors[] = 'Lỗi cập nhật dữ liệu. Vui lòng thử lại.';
                    }
                }
            }
        }
    } elseif ($action === 'change_password') {
        if (!$account) {
            $errors[] = 'Bạn cần đăng nhập để đổi mật khẩu.';
        } else {
            $oldPassword = is_string($_POST['old_password'] ?? null) ? $_POST['old_password'] : '';
            $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
            $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

            if (strlen($newPassword) < 8) {
                $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Mật khẩu mới xác nhận không khớp.';
            }

            if (!$errors && isset($conn) && $conn instanceof mysqli) {
                try {
                    $userId = (int)$account['id'];
                    $stmt = $conn->prepare('SELECT mat_khau FROM nguoi_dung WHERE ma_nguoi_dung = ? LIMIT 1');
                    $stmt->bind_param('i', $userId);
                    $stmt->execute();
                    $u = $stmt->get_result()->fetch_assoc();

                    if (!$u || !password_verify($oldPassword, $u['mat_khau'])) {
                        $errors[] = 'Mật khẩu hiện tại không chính xác.';
                    } else {
                        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $upStmt = $conn->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?');
                        $upStmt->bind_param('si', $newHash, $userId);
                        $upStmt->execute();

                        $_SESSION['account_flash'] = ['success' => 'Đổi mật khẩu thành công!'];
                        if (!headers_sent()) {
                            header('Location: tai-khoan.php?tab=mat-khau', true, 303);
                            exit;
                        }
                    }
                } catch (Throwable $e) {
                    error_log('Change password error: ' . $e->getMessage());
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
