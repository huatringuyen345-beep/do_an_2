<?php
/*
 * Chăm sóc khách hàng: xem liên hệ, phản hồi và cập nhật trạng thái xử lý.
 */

require_once __DIR__ . '/../../_bootstrap.php';
admin_require_staff();

$cacTrangThaiLienHe = ['moi', 'dang_xu_ly', 'da_phan_hoi', 'dong'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $maLienHe = filter_var($_POST['contact_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $trangThai = admin_post_string('status', 30);
    $noiDungPhanHoi = admin_post_string('reply', 5000);

    if (!$maLienHe || !admin_valid_enum($trangThai, $cacTrangThaiLienHe)) {
        admin_flash('error', 'Thông tin phản hồi không hợp lệ.');
        admin_redirect('contacts.php');
    }
    if ($trangThai === 'da_phan_hoi' && $noiDungPhanHoi === '') {
        admin_flash('error', 'Nhập nội dung phản hồi trước khi đánh dấu đã phản hồi.');
        admin_redirect('contacts.php');
    }

    try {
        $maNhanVien = (int) $_SESSION['account']['id'];
        $thoiGianPhanHoi = in_array($trangThai, ['da_phan_hoi', 'dong'], true) ? date('Y-m-d H:i:s') : null;
        $cauLenh = $conn->prepare(
            'UPDATE lien_he SET trang_thai = ?, noi_dung_phan_hoi = ?, ma_nguoi_xu_ly = ?, ngay_phan_hoi = ? WHERE ma_lien_he = ?'
        );
        $cauLenh->bind_param('ssisi', $trangThai, $noiDungPhanHoi, $maNhanVien, $thoiGianPhanHoi, $maLienHe);
        $cauLenh->execute();
        $cauLenh->close();
        admin_flash('success', 'Đã lưu trạng thái và nội dung xử lý liên hệ.');
    } catch (mysqli_sql_exception $loiCSDL) {
        error_log('Admin contact update failed: ' . $loiCSDL->getMessage());
        admin_flash('error', 'Không thể cập nhật liên hệ. Vui lòng thử lại.');
    }
    admin_redirect('contacts.php');
}

$trangThaiLoc = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if ($trangThaiLoc !== '' && !admin_valid_enum($trangThaiLoc, $cacTrangThaiLienHe)) {
    $trangThaiLoc = '';
}
$cauLenh = $conn->prepare(
    "SELECT lh.*, nd.ho_ten AS nhan_vien
     FROM lien_he lh LEFT JOIN nguoi_dung nd ON nd.ma_nguoi_dung = lh.ma_nguoi_xu_ly
     WHERE (? = '' OR lh.trang_thai = ?)
     ORDER BY FIELD(lh.trang_thai, 'moi', 'dang_xu_ly', 'da_phan_hoi', 'dong'), lh.ngay_gui DESC
     LIMIT 100"
);
$cauLenh->bind_param('ss', $trangThaiLoc, $trangThaiLoc);
$cauLenh->execute();
$danhSachLienHe = $cauLenh->get_result()->fetch_all(MYSQLI_ASSOC);
$cauLenh->close();

$activePage = 'contacts';
$pageTitle = 'Liên hệ khách hàng';
require __DIR__ . '/../../../includes/header.php';
?>
<div class="page-intro"><div><h2>Hộp thư liên hệ</h2><p class="muted">Phân công người xử lý, ghi phản hồi và theo dõi trạng thái liên hệ.</p></div><span class="status"><?= count($danhSachLienHe) ?> liên hệ</span></div>
<section class="card">
    <form class="filter-form" method="get" action="contacts.php">
        <div class="field"><label for="status">Trạng thái</label><select id="status" name="status"><option value="">Tất cả liên hệ</option><?php foreach ($cacTrangThaiLienHe as $trangThaiLuaChon): ?><option value="<?= admin_e($trangThaiLuaChon) ?>" <?= $trangThaiLoc === $trangThaiLuaChon ? 'selected' : '' ?>><?= admin_e(admin_status_label($trangThaiLuaChon)) ?></option><?php endforeach; ?></select></div>
        <button class="button button-primary" type="submit">Lọc</button><a class="button button-light" href="contacts.php">Xóa lọc</a>
    </form>
</section>
<?php if (!$danhSachLienHe): ?><section class="card empty-state">Chưa có liên hệ khách hàng.</section><?php endif; ?>
<?php foreach ($danhSachLienHe as $lienHe): ?>
    <section class="card">
        <div class="card-head">
            <div><h2><?= admin_e($lienHe['tieu_de']) ?></h2><span class="muted small">Gửi <?= admin_e(date('d/m/Y H:i', strtotime($lienHe['ngay_gui']))) ?> · xử lý: <?= admin_e($lienHe['nhan_vien'] ?: 'Chưa phân công') ?></span></div>
            <span class="<?= admin_e(admin_status_class($lienHe['trang_thai'])) ?>"><?= admin_e(admin_status_label($lienHe['trang_thai'])) ?></span>
        </div>
        <div class="grid-2">
            <div>
                <p><strong><?= admin_e($lienHe['ho_ten']) ?></strong></p>
                <p class="muted"><?= admin_e($lienHe['email'] ?: 'Không có email') ?><br><?= admin_e($lienHe['so_dien_thoai'] ?: 'Không có số điện thoại') ?></p>
                <div class="notice" style="white-space:pre-wrap"><?= admin_e($lienHe['noi_dung']) ?></div>
                <?php if ($lienHe['noi_dung_phan_hoi']): ?><p class="muted small">Phản hồi đã lưu (<?= admin_e($lienHe['ngay_phan_hoi'] ? date('d/m/Y H:i', strtotime($lienHe['ngay_phan_hoi'])) : 'chưa ghi ngày') ?>):</p><div class="notice notice-success" style="white-space:pre-wrap"><?= admin_e($lienHe['noi_dung_phan_hoi']) ?></div><?php endif; ?>
            </div>
            <form method="post" action="contacts.php" class="stack-form">
                <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="contact_id" value="<?= (int) $lienHe['ma_lien_he'] ?>">
                <label>Nội dung phản hồi<textarea name="reply" maxlength="5000" placeholder="Nhập phản hồi cho khách hàng..."><?= admin_e($lienHe['noi_dung_phan_hoi'] ?? '') ?></textarea></label>
                <label>Trạng thái xử lý<select name="status"><?php foreach ($cacTrangThaiLienHe as $trangThaiLuaChon): ?><option value="<?= admin_e($trangThaiLuaChon) ?>" <?= $lienHe['trang_thai'] === $trangThaiLuaChon ? 'selected' : '' ?>><?= admin_e(admin_status_label($trangThaiLuaChon)) ?></option><?php endforeach; ?></select></label>
                <div class="form-actions"><button class="button button-primary" type="submit">Lưu xử lý</button></div>
            </form>
        </div>
    </section>
<?php endforeach; ?>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
