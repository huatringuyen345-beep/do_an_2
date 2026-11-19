<?php
/*
 * Header dùng chung của website khách hàng: dựng menu trang, nạp CSS theo trang
 * và hiển thị liên kết đăng nhập/tài khoản/khu quản lý theo phiên và vai trò.
 */

require_once __DIR__ . '/../backend/bootstrap.php';

$cur = basename($_SERVER['SCRIPT_NAME']);
$taiKhoan = $_SESSION['account'] ?? null;

$pageStyles = [
    'Index.php'      => 'pages/home.css',
    'DatVe.php'      => 'pages/booking.css',
    'DatDichVu.php'  => 'pages/booking.css',
    'LichTrinh.php'  => 'pages/routes.css',
    'GioiThieu.php'  => 'pages/gioi-thieu.css',
    'DichVu.php'     => 'pages/dich-vu.css',
    'TinTuc.php'     => 'pages/tin-tuc.css',
    'TuyenDung.php'  => 'pages/tuyen-dung.css',
    'HoTro.php'      => 'pages/ho-tro.css',
    'LienHe.php'     => 'pages/lien-he.css',
    'Login.php'      => 'pages/account.css',
    'TaiKhoan.php'   => 'pages/account.css',
    'ThanhToan.php'  => 'pages/booking.css',
];

$nav = [
    'Index.php'     => 'Trang chủ',
    'LichTrinh.php' => 'Lịch trình',
    'DatVe.php'     => 'Đặt vé',
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
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Lexend:wght@500;600;700;800&display=swap&subset=vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if (isset($pageStyles[$cur])): ?>
    <link rel="stylesheet" href="../assets/css/<?= e($pageStyles[$cur]) ?>">
    <?php endif; ?>
    <script>document.documentElement.classList.add('js');</script>
</head>
<body>
<a class="skip" href="#noi-dung">Bỏ qua menu</a>

<!-- ================= TOP BAR ================= -->
<div class="top-bar">
    <div class="wrap top-bar-in">
        <div class="top-bar-left">
            <div class="top-bar-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span>Tổng đài đặt vé: <a href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>" class="hotline-number"><?= e($company['hotline']) ?></a></span>
            </div>
            <div class="top-bar-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                <span>Gửi hàng Express: <strong><?= e($company['hotline2']) ?></strong></span>
            </div>
        </div>
        <div class="top-bar-right">
            <a href="GioiThieu.php" class="top-bar-link">Giới thiệu</a>
            <a href="LichTrinh.php" class="top-bar-link">Lịch trình & Giá vé</a>
            <a href="DichVu.php" class="top-bar-link">Tài Nguyên Express</a>
            <a href="TinTuc.php?loai=khuyen-mai" class="top-bar-link">Ưu đãi & Khuyến mãi</a>
            <a href="LienHe.php" class="top-bar-link">Hệ thống văn phòng</a>
        </div>
    </div>
</div>

<!-- ================= MAIN HEADER & NAVBAR ================= -->
<header class="site-header">
    <div class="wrap header-in">
        <a class="brand" href="Index.php" aria-label="<?= e(SITE_NAME) ?> - Trang chủ">
            <div class="brand-icon-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 6v6M15 6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3M9 18h5"/>
                    <circle cx="7" cy="18" r="2"/><circle cx="16" cy="18" r="2"/>
                </svg>
            </div>
            <span class="brand-text">
                <strong><?= e(SITE_NAME) ?> <span>BUS</span></strong>
                <small>Vận tải Bạc Liêu</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="Mở menu">
            <?= icon('menu', 26) ?>
        </button>

        <nav id="menu" class="nav" aria-label="Menu chính">
            <ul class="nav-links">
                <?php foreach ($nav as $file => $label): ?>
                    <?php
                    $isActive = ($cur === $file)
                        || ($file === 'DichVu.php' && $cur === 'DatDichVu.php');
                    ?>
                    <li><a href="<?= $file ?>"<?= $isActive ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="header-actions">
                <a href="TaiKhoan.php?tab=ve" class="btn-lookup" title="Tra cứu mã vé nhanh">
                    <?= icon('search', 16) ?>
                    <span>Tra cứu vé</span>
                </a>

                <?php if ($taiKhoan && ($taiKhoan['role'] ?? '') === 'khach_hang'): ?>
                    <!-- Trạng thái ĐÃ ĐĂNG NHẬP (Khách hàng) -->
                    <div class="user-menu-wrap" id="userMenuWrap">
                        <button class="user-pill-btn" type="button" id="userMenuBtn" aria-expanded="false" aria-haspopup="true">
                            <span class="user-avatar"><?= mb_strtoupper(mb_substr($taiKhoan['name'], 0, 1)) ?></span>
                            <span class="user-pill-info">
                                <span class="user-pill-name"><?= e($taiKhoan['name']) ?></span>
                                <span class="user-pill-role"><?= e($roleNames[$taiKhoan['role'] ?? 'khach_hang'] ?? 'Khách hàng') ?></span>
                            </span>
                            <span class="user-pill-caret"><?= icon('chevron-down', 16) ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown" role="menu" aria-labelledby="userMenuBtn">
                            <div class="user-dropdown-header">
                                <span class="user-avatar-lg"><?= mb_strtoupper(mb_substr($taiKhoan['name'], 0, 1)) ?></span>
                                <div class="user-dropdown-meta">
                                    <strong class="user-dropdown-name"><?= e($taiKhoan['name']) ?></strong>
                                    <span class="user-dropdown-email"><?= e($taiKhoan['email']) ?></span>
                                    <span class="user-role-badge badge-<?= e($taiKhoan['role'] ?? 'khach_hang') ?>"><?= e($roleNames[$taiKhoan['role'] ?? 'khach_hang'] ?? 'Khách hàng') ?></span>
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
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button class="user-dropdown-item user-dropdown-item-logout" type="submit">
                                    <?= icon('logout', 18) ?>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($taiKhoan): ?>
                    <!-- Trạng thái ĐÃ ĐĂNG NHẬP (Nhân viên / Quản trị) -->
                    <div class="guest-auth-group">
                        <a class="btn-auth-link" href="../../Website_Manage/admin/modules/bookings/bookings.php">
                            <?= icon('user', 16) ?> <span>Khu vực nhân viên</span>
                        </a>
                        <form method="post" action="Logout.php">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button class="btn btn-line btn-sm" type="submit">Đăng xuất</button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Trạng thái CHƯA ĐĂNG NHẬP -->
                    <div class="guest-auth-group">
                        <a class="btn-auth-link" href="Login.php"<?= $cur === 'Login.php' ? ' aria-current="page"' : '' ?>>
                            <?= icon('user', 16) ?> <span>Đăng nhập</span>
                        </a>
                        <?php if (empty($dangNhapNhanVien)): ?>
                            <a class="btn btn-line btn-sm" href="Login.php?mode=register"<?= $cur === 'Login.php' && ($formHienTai ?? '') === 'register' ? ' aria-current="page"' : '' ?>>Đăng ký</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <a class="btn btn-primary btn-sm btn-book" href="DatVe.php">
                    <?= icon('ticket', 16) ?> <span>Đặt vé</span>
                </a>
            </div>
        </nav>
    </div>
</header>

<main id="noi-dung">
