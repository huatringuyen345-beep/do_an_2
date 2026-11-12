<?php
/*
 * Header dùng chung của website khách hàng: dựng menu trang, nạp CSS theo trang
 * và hiển thị liên kết đăng nhập/tài khoản/khu quản lý theo phiên và vai trò.
 */

require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';

$cur = basename($_SERVER['SCRIPT_NAME']);
$account = $_SESSION['account'] ?? null;

$pageStyles = [
    'Index.php'       => 'pages/home.css',
    'DatVe.php'      => 'pages/booking.css',
    'LichTrinh.php'  => 'pages/routes.css',
    'GioiThieu.php'  => 'pages/gioi-thieu.css',
    'DichVu.php'     => 'pages/dich-vu.css',
    'TinTuc.php'     => 'pages/tin-tuc.css',
    'TuyenDung.php'  => 'pages/tuyen-dung.css',
    'HoTro.php'      => 'pages/ho-tro.css',
    'LienHe.php'     => 'pages/lien-he.css',
    'Login.php'       => 'pages/account.css',
    'TaiKhoan.php'   => 'pages/account.css',
];

$nav = [
    'GioiThieu.php' => 'Giới thiệu',
    'LichTrinh.php' => 'Lịch trình',
    'DichVu.php'    => 'Dịch vụ',
    'TinTuc.php'    => 'Tin tức',
    'TuyenDung.php' => 'Tuyển dụng',
    'HoTro.php'     => 'Hỗ trợ',
    'LienHe.php'    => 'Liên hệ',
];

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' | Vận tải hành khách và hàng hóa Bạc Liêu';
$pageDesc  = $pageDesc ?? 'Nhà xe Tài Nguyên: đặt vé online, xe ghế ngồi và giường nằm từ Bạc Liêu đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt, Nha Trang và các tỉnh miền Tây.';

$roleNames = [
    'quan_tri'   => 'Quản trị viên',
    'nhan_vien'  => 'Nhân viên',
    'khach_hang' => 'Khách hàng',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Lexend:wght@500;600;700&display=swap&subset=vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if (isset($pageStyles[$cur])): ?>
    <link rel="stylesheet" href="../assets/css/<?= e($pageStyles[$cur]) ?>">
    <?php endif; ?>
    <script>document.documentElement.classList.add('js');</script>
</head>
<body>
<a class="skip" href="#noi-dung">Bỏ qua menu</a>

<header class="site-header">
    <div class="wrap header-in">
        <a class="brand" href="Index.php" aria-label="<?= e(SITE_NAME) ?> - Trang chủ">
            <svg class="brand-mark" width="42" height="42" viewBox="0 0 42 42" aria-hidden="true">
                <rect width="42" height="42" rx="11" fill="#434777"/>
                <path d="M14 37 18.5 5h5L28 37Z" fill="#405580"/>
                <path d="M21 8v5M21 17v6M21 27v8" stroke="#87DDDD" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
            <span class="brand-text">
                <strong><?= e(SITE_NAME) ?></strong>
                <small>Vận tải Bạc Liêu</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="Mở menu">
            <?= icon('menu', 26) ?>
        </button>

        <nav id="menu" class="nav" aria-label="Menu chính">
            <ul class="nav-links">
                <?php foreach ($nav as $file => $label): ?>
                    <li><a href="<?= $file ?>"<?= $cur === $file ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="header-actions">
                <?php if ($account && ($account['role'] ?? '') === 'khach_hang'): ?>
                    <!-- Trạng thái ĐÃ ĐĂNG NHẬP -->
                    <div class="user-menu-wrap" id="userMenuWrap">
                        <button class="user-pill-btn" type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true">
                            <span class="user-avatar"><?= mb_strtoupper(mb_substr($account['name'], 0, 1)) ?></span>
                            <span class="user-pill-info">
                                <span class="user-pill-name"><?= e($account['name']) ?></span>
                                <span class="user-pill-role"><?= e($roleNames[$account['role'] ?? 'khach_hang'] ?? 'Khách hàng') ?></span>
                            </span>
                            <span class="user-pill-caret"><?= icon('chevron-down', 16) ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown" role="menu" aria-labelledby="userMenuBtn">
                            <div class="user-dropdown-header">
                                <span class="user-avatar-lg"><?= mb_strtoupper(mb_substr($account['name'], 0, 1)) ?></span>
                                <div class="user-dropdown-meta">
                                    <strong class="user-dropdown-name"><?= e($account['name']) ?></strong>
                                    <span class="user-dropdown-email"><?= e($account['email']) ?></span>
                                    <span class="user-role-badge badge-<?= e($account['role'] ?? 'khach_hang') ?>"><?= e($roleNames[$account['role'] ?? 'khach_hang'] ?? 'Khách hàng') ?></span>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a class="user-dropdown-item" href="TaiKhoan.php">
                                <?= icon('user', 18) ?>
                                <span>Thông tin tài khoản</span>
                            </a>
                            <a class="user-dropdown-item" href="TaiKhoan.php?tab=ve">
                                <?= icon('ticket', 18) ?>
                                <span>Vé xe của tôi</span>
                            </a>
                            <a class="user-dropdown-item" href="TaiKhoan.php?tab=mat-khau">
                                <?= icon('lock', 18) ?>
                                <span>Đổi mật khẩu</span>
                            </a>
                            <div class="user-dropdown-divider"></div>
                            <form method="post" action="Logout.php">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button class="user-dropdown-item user-dropdown-item-logout" type="submit">
                                    <?= icon('logout', 18) ?>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($account): ?>
                    <div class="guest-auth-group">
                        <a class="btn-auth-link" href="../../Website_Manage/admin/modules/bookings/bookings.php">
                            <?= icon('user', 16) ?> <span>Khu vực nhân viên</span>
                        </a>
                        <form method="post" action="Logout.php">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <button class="btn btn-line btn-sm" type="submit">Đăng xuất</button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Trạng thái CHƯA ĐĂNG NHẬP -->
                    <div class="guest-auth-group">
                        <a class="btn-auth-link" href="Login.php"<?= $cur === 'Login.php' ? ' aria-current="page"' : '' ?>>
                            <?= icon('user', 16) ?> <span>Đăng nhập</span>
                        </a>
                        <?php if (empty($staffLogin)): ?>
                            <a class="btn btn-line btn-sm" href="Login.php?mode=register"<?= $cur === 'Login.php' && $activeForm === 'register' ? ' aria-current="page"' : '' ?>>Đăng ký</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <a class="btn btn-y btn-sm btn-book" href="DatVe.php">
                    <?= icon('ticket', 16) ?> <span>Đặt vé</span>
                </a>
            </div>
        </nav>
    </div>
</header>

<main id="noi-dung">
