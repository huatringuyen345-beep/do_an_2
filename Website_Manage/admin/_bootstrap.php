<?php
declare(strict_types=1);
/*
 * Bootstrap chung cho các trang trong khu vực quản lý:
 * - Khởi tạo session an toàn và kết nối cơ sở dữ liệu.
 * - Cung cấp helper escape HTML, CSRF, flash message, redirect và định dạng.
 * - admin_require_staff() kiểm tra lại tài khoản còn hoạt động và chỉ cho
 *   vai trò quan_tri (1) hoặc nhan_vien (2) truy cập.
 *
 * File này không xử lý đăng ký/đăng nhập khách hàng hay CRUD tài khoản;
 * đăng nhập và hồ sơ khách hàng được xử lý ở Website_Customer.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(503);
    exit('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra MySQL và cấu hình kết nối.');
}

/** Escape giá trị trước khi đưa vào HTML để tránh chèn markup không an toàn. */
function admin_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Lấy hoặc tạo token CSRF riêng cho các biểu mẫu trong khu quản lý. */
function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}

/** Kiểm tra token CSRF của yêu cầu POST; dừng với HTTP 419 nếu không hợp lệ. */
function admin_verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(admin_csrf_token(), $token)) {
        http_response_code(419);
        exit('Phiên làm việc không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang.');
    }
}

/**
 * Yêu cầu phiên đăng nhập hợp lệ và xác minh lại trạng thái/vai trò từ database.
 * Chỉ tài khoản đang hoạt động có vai trò admin (1) hoặc nhân viên (2) được vào.
 */
function admin_require_staff(): void
{
    if (empty($_SESSION['account']['id'])) {
        header('Location: login.php');
        exit;
    }

    global $conn;
    $userId = (int) $_SESSION['account']['id'];
    $stmt = $conn->prepare(
        "SELECT nd.ho_ten, vt.ten_vai_tro
         FROM nguoi_dung nd
         INNER JOIN vai_tro vt ON vt.ma_vai_tro = nd.ma_vai_tro
         WHERE nd.ma_nguoi_dung = ? AND nd.trang_thai = 'hoat_dong' AND nd.ma_vai_tro IN (1, 2)
         LIMIT 1"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$staff) {
        unset($_SESSION['account']);
        $_SESSION['account_flash'] = ['error' => 'Tài khoản không còn quyền truy cập khu vực quản lý.'];
        header('Location: login.php');
        exit;
    }

    $_SESSION['account']['name'] = $staff['ho_ten'];
    $_SESSION['account']['role'] = $staff['ten_vai_tro'];
}

/** Chuyển hướng đến trang khu quản lý rồi kết thúc yêu cầu hiện tại. */
function admin_redirect(string $page): void
{
    header('Location: ' . $page);
    exit;
}

/** Lưu thông báo một lần trong session để hiển thị sau khi chuyển trang. */
function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

/** Đọc rồi xóa thông báo flash; trả về null nếu không có thông báo. */
function admin_take_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    return is_array($flash) ? $flash : null;
}

/** Định dạng số tiền theo cách hiển thị tiếng Việt, không có phần thập phân. */
function admin_money($amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' đ';
}

/** Đổi mã trạng thái trong database thành nhãn tiếng Việt để hiển thị. */
function admin_status_label(string $status): string
{
    $labels = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'da_thanh_toan' => 'Đã thanh toán',
        'da_huy' => 'Đã hủy',
        'hoan_tien' => 'Hoàn tiền',
        'thanh_cong' => 'Thành công',
        'cho_thanh_toan' => 'Chờ thanh toán',
        'tien_mat' => 'Tiền mặt',
        'chuyen_khoan' => 'Chuyển khoản',
        'vi_dien_tu' => 'Ví điện tử',
        'the_ngan_hang' => 'Thẻ ngân hàng',
        'giu_cho' => 'Giữ chỗ',
        'mo_ban' => 'Mở bán',
        'het_cho' => 'Hết chỗ',
        'dang_chay' => 'Đang chạy',
        'hoan_thanh' => 'Hoàn thành',
        'hoat_dong' => 'Hoạt động',
        'tam_ngung' => 'Tạm ngưng',
        'bao_tri' => 'Bảo trì',
        'ngung_hoat_dong' => 'Ngừng hoạt động',
        'cho_xu_ly' => 'Chờ xử lý',
        'dang_thuc_hien' => 'Đang thực hiện',
        'moi' => 'Mới',
        'dang_xu_ly' => 'Đang xử lý',
        'da_phan_hoi' => 'Đã phản hồi',
        'dong' => 'Đã đóng',
        'cho_lay_hang' => 'Chờ lấy hàng',
        'da_nhan_hang' => 'Đã nhận hàng',
        'dang_van_chuyen' => 'Đang vận chuyển',
        'da_den_noi' => 'Đã đến nơi',
        'da_giao' => 'Đã giao',
        'that_bai' => 'Thất bại',
    ];

    return $labels[$status] ?? $status;
}

/** Chọn lớp CSS trạng thái (bình thường/cảnh báo/thành công/nguy hiểm). */
function admin_status_class(string $status): string
{
    if (in_array($status, ['da_huy', 'that_bai', 'tam_khoa', 'ngung_hoat_dong'], true)) {
        return 'status status-danger';
    }
    if (in_array($status, ['cho_xac_nhan', 'cho_xu_ly', 'moi', 'giu_cho', 'cho_lay_hang', 'bao_tri', 'tam_ngung', 'cho_thanh_toan'], true)) {
        return 'status status-warn';
    }
    if (in_array($status, ['da_thanh_toan', 'da_xac_nhan', 'hoan_thanh', 'da_giao', 'da_phan_hoi', 'hoat_dong'], true)) {
        return 'status status-success';
    }

    return 'status';
}

/** Đọc chuỗi POST, giới hạn độ dài và loại khoảng trắng ở hai đầu. */
function admin_post_string(string $key, int $maxLength = 255): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }

    return trim(mb_substr($value, 0, $maxLength, 'UTF-8'));
}

/** Kiểm tra giá trị có nằm chính xác trong danh sách lựa chọn cho phép hay không. */
function admin_valid_enum(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}
