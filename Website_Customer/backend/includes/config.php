<?php
/**
 * config.php - Thong tin chung cua website va cac ham tien ich.
 * Sua thong tin cong ty tai day, tat ca cac trang se tu cap nhat.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('SITE_NAME', 'Tài Nguyên');

$company = [
    'ten'      => 'Công ty vận tải Tài Nguyên',
    'slogan'   => 'Vận tải hành khách và hàng hóa từ Bạc Liêu',
    'dia_chi'  => 'Số 00, đường ..., Bạc Liêu',          // TODO: thay dia chi that
    'hotline'  => '0291 3 000 000',                       // TODO: thay so that
    'hotline2' => '1900 0000',
    'email'    => 'lienhe@tainguyen.vn',
    'gio_mo'   => '04:30 – 22:00 hằng ngày',
];

/** Ma hoa HTML de tranh loi XSS khi hien thi du lieu do nguoi dung nhap. */
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Dinh dang 250000 thanh 250.000d. */
function money($n)
{
    return number_format((int)$n, 0, ',', '.') . 'đ';
}

/** Doi 2.5 gio thanh "2 gio 30 phut". */
function duration($h)
{
    $tongSoPhut = (int)round($h * 60);
    $soGio = intdiv($tongSoPhut, 60);
    $soPhutLe = $tongSoPhut % 60;
    return $soGio . ' giờ' . ($soPhutLe ? ' ' . $soPhutLe . ' phút' : '');
}

/** Giu lai gia tri nguoi dung da nhap khi bieu mau bao loi. */
function old($key, $default = '')
{
    return isset($_POST[$key]) && is_scalar($_POST[$key]) ? e($_POST[$key]) : e($default);
}

function customer_auth_next($page)
{
    $cacTrangDuocPhep = ['DatVe.php', 'LienHe.php', 'TuyenDung.php', 'TaiKhoan.php'];
    return is_string($page) && in_array($page, $cacTrangDuocPhep, true) ? $page : 'TaiKhoan.php';
}

function customer_require_account($next)
{
    if (!empty($_SESSION['account']['id'])) {
        return;
    }

    $_SESSION['account_flash'] = ['error' => 'Vui lòng đăng nhập hoặc tạo tài khoản để thực hiện thao tác này.'];
    header('Location: Login.php?next=' . rawurlencode(customer_auth_next($next)), true, 303);
    exit;
}

/** Bo bieu tuong SVG dang net dung chung. */
function icon($name, $size = 24)
{
    $cacDuongDanBieuTuong = [
        'bus'          => '<path d="M8 6v6M15 6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3M9 18h5"/><circle cx="7" cy="18" r="2"/><circle cx="16" cy="18" r="2"/>',
        'box'          => '<path d="M16.5 9.4 7.5 4.21M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16zM3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"/>',
        'car'          => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2M7 17h10"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'bed'          => '<path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/>',
        'coffee'       => '<path d="M10 2v2M14 2v2M6 2v2M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/>',
        'phone'        => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'clock'        => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'shield'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'pin'          => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'card'         => '<rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>',
        'mail'         => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM22 6l-10 7L2 6"/>',
        'check'        => '<path d="M20 6 9 17l-5-5"/>',
        'menu'         => '<path d="M3 12h18M3 6h18M3 18h18"/>',
        'close'        => '<path d="M18 6 6 18M6 6l12 12"/>',
        'bank'         => '<path d="M3 21h18M5 21V10M9 21V10M15 21V10M19 21V10M2 10l10-7 10 7"/>',
        'wallet'       => '<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4M4 6v12a2 2 0 0 0 2 2h14v-4M18 12a2 2 0 0 0 0 4h4v-4z"/>',
        'users'        => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/>',
        'user'         => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'lock'         => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'eye'          => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'      => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>',
        'ticket'       => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/><path d="M7 15h.01"/><path d="M17 15h.01"/>',
        'calendar'     => '<rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
        'logout'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'edit'         => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
    ];
    $noiDungBieuTuong = $cacDuongDanBieuTuong[$name] ?? '';
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $noiDungBieuTuong . '</svg>';
}
