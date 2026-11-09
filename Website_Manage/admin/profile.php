<?php
/*
 * Trang thông tin cá nhân dành cho admin/nhân viên đang đăng nhập.
 * Chỉ đọc hồ sơ của tài khoản hiện tại; không cho xem hồ sơ người khác.
 */

require_once __DIR__ . '/_bootstrap.php';
admin_require_staff();

$userId = (int) $_SESSION['account']['id'];
$stmt = $conn->prepare(
    "SELECT nd.ho_ten, nd.email, nd.so_dien_thoai, nd.dia_chi, nd.trang_thai, nd.ngay_tao, vt.ten_vai_tro
     FROM nguoi_dung nd
     INNER JOIN vai_tro vt ON vt.ma_vai_tro = nd.ma_vai_tro
     WHERE nd.ma_nguoi_dung = ? AND nd.trang_thai = 'hoat_dong'
     LIMIT 1"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    http_response_code(404);
    exit('Không tìm thấy thông tin tài khoản.');
}

$roleLabels = [
    'quan_tri' => 'Quản trị viên',
    'nhan_vien' => 'Nhân viên',
];
$activePage = 'profile';
$pageTitle = 'Thông tin cá nhân';
require __DIR__ . '/_header.php';
?>
<div class="page-intro">
    <div>
        <h2>Thông tin tài khoản</h2>
        <p class="muted">Thông tin của tài khoản quản lý đang đăng nhập.</p>
    </div>
</div>

<section class="card profile-card">
    <div class="profile-heading">
        <span class="avatar profile-avatar"><?= admin_e(mb_substr($profile['ho_ten'], 0, 1, 'UTF-8')) ?></span>
        <div>
            <h2><?= admin_e($profile['ho_ten']) ?></h2>
            <span class="status status-success"><?= admin_e($roleLabels[$profile['ten_vai_tro']] ?? $profile['ten_vai_tro']) ?></span>
        </div>
    </div>
    <dl class="profile-details">
        <div><dt>Email</dt><dd><?= admin_e($profile['email'] ?: 'Chưa cập nhật') ?></dd></div>
        <div><dt>Số điện thoại</dt><dd><?= admin_e($profile['so_dien_thoai'] ?: 'Chưa cập nhật') ?></dd></div>
        <div><dt>Địa chỉ</dt><dd><?= admin_e($profile['dia_chi'] ?: 'Chưa cập nhật') ?></dd></div>
        <div><dt>Ngày tạo tài khoản</dt><dd><?= admin_e(date('d/m/Y H:i', strtotime($profile['ngay_tao']))) ?></dd></div>
    </dl>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
