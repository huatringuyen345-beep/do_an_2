<?php
/*
 * Trang hồ sơ khách hàng đã đăng nhập: hiển thị thông tin tài khoản,
 * lịch sử đặt vé và các thao tác cập nhật hồ sơ/đổi mật khẩu.
 * Tài khoản admin và nhân viên được chuyển sang khu vực quản lý.
 */

require_once __DIR__ . '/../backend/includes/config.php';

// Khu vực hồ sơ chỉ dành cho tài khoản khách hàng.
if (in_array($_SESSION['account']['role'] ?? '', ['quan_tri', 'nhan_vien'], true)) {
    header('Location: ../../Website_Manage/admin/bookings.php', true, 302);
    exit;
}
if (empty($_SESSION['account']['id'])) {
    $_SESSION['account_flash'] = ['error' => 'Vui lòng đăng nhập để truy cập trang quản lý tài khoản.'];
    header('Location: login.php?next=tai-khoan.php', true, 302);
    exit;
}

require_once __DIR__ . '/../backend/controllers/account.php';
require_once __DIR__ . '/../backend/database/config.php';

$userId = (int)$account['id'];

// Lấy thông tin mới nhất của người dùng từ database
$userData = $account;
$createdAt = null;
if (isset($conn) && $conn instanceof mysqli) {
    try {
        $uStmt = $conn->prepare('SELECT ho_ten, email, so_dien_thoai, dia_chi, ngay_tao, trang_thai FROM nguoi_dung WHERE ma_nguoi_dung = ? LIMIT 1');
        $uStmt->bind_param('i', $userId);
        $uStmt->execute();
        $fresh = $uStmt->get_result()->fetch_assoc();
        if ($fresh) {
            $userData['name'] = $fresh['ho_ten'];
            $userData['email'] = $fresh['email'];
            $userData['phone'] = $fresh['so_dien_thoai'] ?? '';
            $userData['address'] = $fresh['dia_chi'] ?? '';
            $createdAt = $fresh['ngay_tao'];
        }
    } catch (Throwable $e) {
        error_log('User refresh error: ' . $e->getMessage());
    }
}

// Lấy danh sách vé đã đặt của người dùng
$myTickets = [];

// 1. Lấy từ cơ sở dữ liệu
if (isset($conn) && $conn instanceof mysqli) {
    try {
        $tStmt = $conn->prepare(
            'SELECT ma_dat_ve, ten_khach_hang, sdt_khach_hang, email_khach_hang, tong_tien, thanh_tien, trang_thai, ghi_chu, ngay_dat
             FROM dat_ve 
             WHERE ma_nguoi_dung = ? OR (email_khach_hang = ? AND email_khach_hang != "")
             ORDER BY ngay_dat DESC'
        );
        $userEmail = $userData['email'];
        $tStmt->bind_param('is', $userId, $userEmail);
        $tStmt->execute();
        $res = $tStmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $myTickets[$row['ma_dat_ve']] = [
                'ma'         => $row['ma_dat_ve'],
                'ten'        => $row['ten_khach_hang'],
                'sdt'        => $row['sdt_khach_hang'],
                'email'      => $row['email_khach_hang'],
                'tong'       => (float)$row['thanh_tien'],
                'trang_thai' => $row['trang_thai'],
                'ghi_chu'    => $row['ghi_chu'],
                'ngay_dat'   => $row['ngay_dat'],
            ];
        }
    } catch (Throwable $e) {
        error_log('Tickets query error: ' . $e->getMessage());
    }
}

// 2. Kết hợp với các vé vừa đặt trong session (nếu chưa có trong DB)
if (!empty($_SESSION['my_bookings']) && is_array($_SESSION['my_bookings'])) {
    foreach ($_SESSION['my_bookings'] as $b) {
        if (!isset($myTickets[$b['ma']])) {
            $myTickets[$b['ma']] = [
                'ma'         => $b['ma'],
                'ten'        => $b['ten'],
                'sdt'        => $b['sdt'],
                'email'      => $b['email'] ?? $userData['email'],
                'tong'       => (float)$b['tong'],
                'trang_thai' => 'cho_xac_nhan',
                'ghi_chu'    => "Tuyến: {$b['tuyen']} | Ngày: " . date('d/m/Y', strtotime($b['ngay'])) . " {$b['gio']} | Loại: {$b['loai']} | Ghế: " . implode(', ', $b['seats']) . ($b['tc'] ? " | Đón: {$b['tc']}" : ''),
                'ngay_dat'   => $b['created_at'] ?? date('Y-m-d H:i:s'),
            ];
        }
    }
}

$tab = $_GET['tab'] ?? 'thong-tin';
if (!in_array($tab, ['thong-tin', 've', 'mat-khau'], true)) {
    $tab = 'thong-tin';
}

$statusLabels = [
    'cho_xac_nhan' => ['Chờ xác nhận', 'badge-warning'],
    'da_xac_nhan'  => ['Đã xác nhận', 'badge-info'],
    'da_thanh_toan' => ['Đã thanh toán', 'badge-success'],
    'da_huy'       => ['Đã hủy', 'badge-danger'],
    'hoan_tien'    => ['Hoàn tiền', 'badge-muted'],
];

$pageTitle = 'Trang cá nhân - ' . $userData['name'];
$pageDesc  = 'Quản lý thông tin tài khoản, xem lịch sử đặt vé xe của nhà xe Tài Nguyên.';
include __DIR__ . '/includes/header.php';
?>

<section class="page-head profile-head">
    <div class="wrap">
        <div class="profile-hero">
            <div class="profile-avatar">
                <?= mb_strtoupper(mb_substr($userData['name'], 0, 1)) ?>
            </div>
            <div class="profile-meta">
                <div class="profile-name-row">
                    <h1><?= e($userData['name']) ?></h1>
                    <span class="user-role-badge badge-<?= e($userData['role'] ?? 'khach_hang') ?>">
                        <?= e($roleNames[$userData['role'] ?? 'khach_hang'] ?? 'Khách hàng') ?>
                    </span>
                </div>
                <p class="profile-sub">
                    <span><?= icon('mail', 16) ?> <?= e($userData['email']) ?></span>
                    <?php if (!empty($userData['phone'])): ?>
                        <span><?= icon('phone', 16) ?> <?= e($userData['phone']) ?></span>
                    <?php endif; ?>
                    <?php if ($createdAt): ?>
                        <span><?= icon('calendar', 16) ?> Thành viên từ <?= date('d/m/Y', strtotime($createdAt)) ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="profile-actions">
                <form id="logoutForm" method="post" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <button class="btn btn-ghost btn-sm" type="button" id="btnLogoutProfile">
                        <?= icon('logout', 16) ?> <span>Đăng xuất</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section profile-section">
    <div class="wrap">
        <?php if (!empty($errorMessage)): ?>
            <div class="auth-alert auth-alert-error" role="alert">
                <span class="auth-alert-icon"><?= icon('alert-circle', 20) ?></span>
                <div class="auth-alert-content"><strong>Lỗi:</strong> <?= e($errorMessage) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="auth-alert auth-alert-success" role="status">
                <span class="auth-alert-icon"><?= icon('check-circle', 20) ?></span>
                <div class="auth-alert-content"><strong>Thành công:</strong> <?= e($successMessage) ?></div>
            </div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Sidebar điều hướng tabs -->
            <aside class="profile-nav-card">
                <ul class="profile-nav-list" role="tablist">
                    <li>
                        <a href="tai-khoan.php?tab=thong-tin" class="profile-nav-item<?= $tab === 'thong-tin' ? ' is-active' : '' ?>">
                            <?= icon('user', 20) ?>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </li>
                    <li>
                        <a href="tai-khoan.php?tab=ve" class="profile-nav-item<?= $tab === 've' ? ' is-active' : '' ?>">
                            <?= icon('ticket', 20) ?>
                            <span>Vé xe của tôi</span>
                            <span class="nav-count"><?= count($myTickets) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="tai-khoan.php?tab=mat-khau" class="profile-nav-item<?= $tab === 'mat-khau' ? ' is-active' : '' ?>">
                            <?= icon('lock', 20) ?>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>
                </ul>

                <div class="profile-nav-footer">
                    <a class="btn btn-y btn-sm btn-block" href="dat-ve.php">
                        <?= icon('ticket', 16) ?> <span>Đặt thêm vé mới</span>
                    </a>
                </div>
            </aside>

            <!-- Nội dung chính của từng Tab -->
            <main class="profile-main-content">
                <!-- ================= TAB 1: THÔNG TIN CÁ NHÂN ================= -->
                <?php if ($tab === 'thong-tin'): ?>
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>Hồ sơ cá nhân</h2>
                            <p>Cập nhật thông tin để tiện cho nhân viên đón và liên hệ khi đi xe.</p>
                        </div>
                        <form class="auth-form" method="post" action="tai-khoan.php?tab=thong-tin">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="row2">
                                <div class="auth-input-group">
                                    <label for="prof-name">Họ và tên <span class="required">*</span></label>
                                    <div class="auth-input-box">
                                        <span class="input-icon"><?= icon('user', 20) ?></span>
                                        <input type="text" id="prof-name" name="name" value="<?= e($userData['name']) ?>" required maxlength="100">
                                    </div>
                                </div>
                                <div class="auth-input-group">
                                    <label for="prof-phone">Số điện thoại</label>
                                    <div class="auth-input-box">
                                        <span class="input-icon"><?= icon('phone', 20) ?></span>
                                        <input type="tel" id="prof-phone" name="phone" value="<?= e($userData['phone']) ?>" placeholder="Ví dụ: 0912345678">
                                    </div>
                                </div>
                            </div>

                            <div class="auth-input-group">
                                <label for="prof-email">Email đăng nhập (Cố định)</label>
                                <div class="auth-input-box auth-input-disabled">
                                    <span class="input-icon"><?= icon('mail', 20) ?></span>
                                    <input type="email" id="prof-email" value="<?= e($userData['email']) ?>" disabled readonly>
                                </div>
                                <span class="hint">Địa chỉ email là định danh tài khoản và không thể tự thay đổi.</span>
                            </div>

                            <div class="auth-input-group">
                                <label for="prof-address">Địa chỉ thường trú / Điểm đón quen thuộc</label>
                                <div class="auth-input-box">
                                    <span class="input-icon"><?= icon('pin', 20) ?></span>
                                    <input type="text" id="prof-address" name="address" value="<?= e($userData['address']) ?>" placeholder="Số nhà, đường, phường/xã, Bạc Liêu...">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-g" type="submit">
                                    <?= icon('check-circle', 18) ?> <span>Lưu thông tin thay đổi</span>
                                </button>
                            </div>
                        </form>
                    </div>

                <!-- ================= TAB 2: VÉ XE CỦA TÔI ================= -->
                <?php elseif ($tab === 've'): ?>
                    <div class="profile-card">
                        <div class="profile-card-header profile-card-header-flex">
                            <div>
                                <h2>Vé xe đã đặt</h2>
                                <p>Danh sách các chuyến xe bạn đã đặt cùng Tài Nguyên.</p>
                            </div>
                            <a class="btn btn-y btn-sm" href="dat-ve.php">
                                <?= icon('ticket', 16) ?> <span>Đặt vé mới</span>
                            </a>
                        </div>

                        <?php if (empty($myTickets)): ?>
                            <div class="empty-tickets">
                                <div class="empty-icon"><?= icon('ticket', 48) ?></div>
                                <h3>Bạn chưa có vé xe nào</h3>
                                <p>Hãy chọn tuyến đường và đặt vé ngay hôm nay để nhận nhiều ưu đãi từ Tài Nguyên.</p>
                                <a class="btn btn-g" href="dat-ve.php">Đặt vé online ngay</a>
                            </div>
                        <?php else: ?>
                            <div class="tickets-list">
                                <?php foreach ($myTickets as $t): ?>
                                    <?php 
                                        $st = $statusLabels[$t['trang_thai']] ?? ['Chờ xác nhận', 'badge-warning'];
                                    ?>
                                    <div class="ticket-item">
                                        <div class="ticket-item-top">
                                            <div class="ticket-code-wrap">
                                                <span class="ticket-code-label">Mã vé:</span>
                                                <strong class="ticket-code-val"><?= e($t['ma']) ?></strong>
                                            </div>
                                            <div class="ticket-time-wrap">
                                                <span class="ticket-date"><?= date('d/m/Y H:i', strtotime($t['ngay_dat'])) ?></span>
                                                <span class="ticket-status-badge <?= $st[1] ?>"><?= e($st[0]) ?></span>
                                            </div>
                                        </div>

                                        <div class="ticket-item-body">
                                            <div class="ticket-item-desc">
                                                <p class="ticket-notes"><?= e($t['ghi_chu']) ?></p>
                                                <p class="ticket-passenger">
                                                    <span><strong>Hành khách:</strong> <?= e($t['ten']) ?></span>
                                                    <span><strong>SĐT:</strong> <?= e($t['sdt']) ?></span>
                                                </p>
                                            </div>
                                            <div class="ticket-item-price">
                                                <span class="price-label">Tổng tiền</span>
                                                <strong class="price-val"><?= money($t['tong']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <!-- ================= TAB 3: ĐỔI MẬT KHẨU ================= -->
                <?php elseif ($tab === 'mat-khau'): ?>
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>Đổi mật khẩu tài khoản</h2>
                            <p>Để bảo mật, vui lòng chọn mật khẩu mạnh có tối thiểu 8 ký tự.</p>
                        </div>

                        <form class="auth-form" method="post" action="tai-khoan.php?tab=mat-khau" style="max-width: 580px;" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="change_password">

                            <div class="auth-input-group">
                                <label for="chg-old-pw">Mật khẩu hiện tại <span class="required">*</span></label>
                                <div class="auth-input-box">
                                    <span class="input-icon"><?= icon('lock', 20) ?></span>
                                    <input type="password" id="chg-old-pw" name="old_password" placeholder="Nhập mật khẩu cũ" required>
                                    <button class="btn-toggle-pw" type="button" data-toggle-target="chg-old-pw" aria-label="Hiện hoặc ẩn mật khẩu">
                                        <span class="eye-show"><?= icon('eye', 20) ?></span>
                                        <span class="eye-hide" hidden><?= icon('eye-off', 20) ?></span>
                                    </button>
                                </div>
                            </div>

                            <div class="auth-input-group">
                                <label for="chg-new-pw">Mật khẩu mới <span class="required">*</span></label>
                                <div class="auth-input-box">
                                    <span class="input-icon"><?= icon('lock', 20) ?></span>
                                    <input type="password" id="chg-new-pw" name="new_password" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                                    <button class="btn-toggle-pw" type="button" data-toggle-target="chg-new-pw" aria-label="Hiện hoặc ẩn mật khẩu">
                                        <span class="eye-show"><?= icon('eye', 20) ?></span>
                                        <span class="eye-hide" hidden><?= icon('eye-off', 20) ?></span>
                                    </button>
                                </div>
                            </div>

                            <div class="auth-input-group">
                                <label for="chg-confirm-pw">Xác nhận mật khẩu mới <span class="required">*</span></label>
                                <div class="auth-input-box">
                                    <span class="input-icon"><?= icon('lock', 20) ?></span>
                                    <input type="password" id="chg-confirm-pw" name="confirm_password" placeholder="Nhập lại mật khẩu mới" minlength="8" required>
                                    <button class="btn-toggle-pw" type="button" data-toggle-target="chg-confirm-pw" aria-label="Hiện hoặc ẩn mật khẩu">
                                        <span class="eye-show"><?= icon('eye', 20) ?></span>
                                        <span class="eye-hide" hidden><?= icon('eye-off', 20) ?></span>
                                    </button>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-g" type="submit">
                                    <?= icon('shield-check', 18) ?> <span>Cập nhật mật khẩu mới</span>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</section>

<!-- Hộp thoại xác nhận đăng xuất -->
<div class="auth-modal" id="logoutModal" hidden>
    <div class="auth-modal-backdrop"></div>
    <div class="auth-modal-dialog">
        <div class="auth-modal-header">
            <h3>Xác nhận đăng xuất</h3>
            <button class="auth-modal-close" id="closeLogoutModal" type="button" aria-label="Đóng">&times;</button>
        </div>
        <div class="auth-modal-body">
            <p>Bạn có chắc chắn muốn đăng xuất khỏi tài khoản <strong><?= e($userData['name']) ?></strong>?</p>
        </div>
        <div class="auth-modal-footer">
            <button class="btn btn-line btn-sm" id="cancelLogoutBtn" type="button">Ở lại</button>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <button class="btn btn-g btn-sm" type="submit" form="logoutForm">Đăng xuất ngay</button>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = ['assets/js/taotaikhoan.js'];
include __DIR__ . '/includes/footer.php';
?>
