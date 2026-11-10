<?php
/*
 * Khung giao diện chung khu quản lý: tiêu đề/trạng thái thông báo,
 * menu nghiệp vụ, thông tin người dùng hiện tại và nút đăng xuất.
 */

$activePage = $activePage ?? '';
$pageTitle = $pageTitle ?? 'Khu vực quản lý';
$flash = admin_take_flash();
$currentUser = $_SESSION['account'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= admin_e($pageTitle) ?> | Tài Nguyên</title>
    <link rel="stylesheet" href="<?= admin_e(admin_asset_url('css/admin.css')) ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= admin_e(admin_url('modules/bookings/bookings.php')) ?>"><span class="brand-mark">TN</span><span>Tài Nguyên<small>QUẢN LÝ VẬN TẢI</small></span></a>
        <p class="nav-caption">NGHIỆP VỤ</p>
        <nav class="side-nav" aria-label="Điều hướng quản lý">
            <a class="<?= $activePage === 'bookings' ? 'active' : '' ?>" href="<?= admin_e(admin_url('modules/bookings/bookings.php')) ?>"><span class="nav-icon">▣</span> Đặt vé</a>
            <a class="<?= $activePage === 'trips' ? 'active' : '' ?>" href="<?= admin_e(admin_url('modules/trips/trips.php')) ?>"><span class="nav-icon">◷</span> Chuyến xe</a>
            <a class="<?= $activePage === 'fleet' ? 'active' : '' ?>" href="<?= admin_e(admin_url('modules/fleet/fleet.php')) ?>"><span class="nav-icon">▤</span> Tuyến &amp; đội xe</a>
            <a class="<?= $activePage === 'services' ? 'active' : '' ?>" href="<?= admin_e(admin_url('modules/services/services.php')) ?>"><span class="nav-icon">▧</span> Đơn dịch vụ</a>
            <a class="<?= $activePage === 'contacts' ? 'active' : '' ?>" href="<?= admin_e(admin_url('modules/support/contacts.php')) ?>"><span class="nav-icon">✉</span> Liên hệ</a>
        </nav>
        <div class="sidebar-bottom">
            <div class="user-chip"><span class="avatar"><?= admin_e(mb_substr($currentUser['name'], 0, 1, 'UTF-8')) ?></span><span><?= admin_e($currentUser['name']) ?><small><?= admin_e($currentUser['role']) ?></small></span></div>
            <form method="post" action="<?= admin_e(admin_project_url('Website_Customer/pages/logout.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= admin_e($_SESSION['csrf_token']) ?>">
                <button class="logout-button" type="submit">Đăng xuất</button>
            </form>
        </div>
    </aside>
    <main class="main-panel">
        <header class="topbar">
            <div><p class="eyebrow">TÀI NGUYÊN / QUẢN LÝ</p><h1><?= admin_e($pageTitle) ?></h1></div>
            <div class="topbar-user">
                <span class="avatar avatar-small"><?= admin_e(mb_substr($currentUser['name'], 0, 1, 'UTF-8')) ?></span>
                <span><?= admin_e($currentUser['name']) ?></span>
                <a class="button button-light topbar-profile-link" href="<?= admin_e(admin_url('modules/users/profile.php')) ?>">Thông tin cá nhân</a>
                <form method="post" action="<?= admin_e(admin_project_url('Website_Customer/pages/logout.php')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= admin_e($_SESSION['csrf_token']) ?>">
                    <button class="topbar-logout" type="submit">Đăng xuất</button>
                </form>
            </div>
        </header>
        <div class="content">
            <?php if ($flash): ?>
                <div class="notice <?= $flash['type'] === 'error' ? 'notice-error' : 'notice-success' ?>" role="status"><?= admin_e($flash['message']) ?></div>
            <?php endif; ?>
