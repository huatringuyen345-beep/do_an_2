<?php
/*
 * Nghiệp vụ dịch vụ/logistics: tra cứu đơn dịch vụ và cập nhật trạng thái xử lý,
 * bao gồm trạng thái vận đơn khi có chi tiết hàng hóa.
 */

require_once __DIR__ . '/../../_bootstrap.php';
admin_require_staff();

$serviceStatuses = ['cho_xu_ly', 'da_xac_nhan', 'dang_thuc_hien', 'hoan_thanh', 'da_huy'];
$parcelStatuses = ['cho_lay_hang', 'da_nhan_hang', 'dang_van_chuyen', 'da_den_noi', 'da_giao', 'that_bai', 'da_huy'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $action = admin_post_string('action', 30);
    $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $status = admin_post_string('status', 30);

    if (!$orderId) {
        admin_flash('error', 'Mã đơn dịch vụ không hợp lệ.');
        admin_redirect('services.php');
    }

    try {
        if ($action === 'update_order' && admin_valid_enum($status, $serviceStatuses)) {
            $stmt = $conn->prepare('UPDATE dat_dich_vu SET trang_thai = ? WHERE ma_dat_dich_vu_id = ?');
            $stmt->bind_param('si', $status, $orderId);
            $stmt->execute();
            $stmt->close();
            admin_flash('success', 'Đã cập nhật trạng thái đơn dịch vụ.');
        } elseif ($action === 'update_parcel' && admin_valid_enum($status, $parcelStatuses)) {
            $stmt = $conn->prepare('UPDATE chi_tiet_hang SET trang_thai_van_don = ? WHERE ma_dat_dich_vu_id = ?');
            $stmt->bind_param('si', $status, $orderId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected === 0) {
                $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM chi_tiet_hang WHERE ma_dat_dich_vu_id = ?');
                $stmt->bind_param('i', $orderId);
                $stmt->execute();
                $hasParcel = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
                $stmt->close();
                admin_flash($hasParcel ? 'success' : 'error', $hasParcel ? 'Trạng thái vận đơn không thay đổi.' : 'Đơn này chưa có chi tiết hàng hóa để cập nhật.');
            } else {
                admin_flash('success', 'Đã cập nhật trạng thái vận đơn.');
            }
        } else {
            admin_flash('error', 'Trạng thái hoặc thao tác không hợp lệ.');
        }
    } catch (mysqli_sql_exception $exception) {
        error_log('Admin service update failed: ' . $exception->getMessage());
        admin_flash('error', 'Không thể cập nhật đơn dịch vụ. Vui lòng thử lại.');
    }
    admin_redirect('services.php');
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 100, 'UTF-8')) : '';
$statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if ($statusFilter !== '' && !admin_valid_enum($statusFilter, $serviceStatuses)) {
    $statusFilter = '';
}
$searchLike = '%' . $search . '%';
$stmt = $conn->prepare(
    "SELECT dv.*, ds.ten_dich_vu,
        (SELECT ch.ma_van_don FROM chi_tiet_hang ch WHERE ch.ma_dat_dich_vu_id = dv.ma_dat_dich_vu_id ORDER BY ch.ma_chi_tiet_hang LIMIT 1) AS ma_van_don,
        (SELECT ch.ten_nguoi_nhan FROM chi_tiet_hang ch WHERE ch.ma_dat_dich_vu_id = dv.ma_dat_dich_vu_id ORDER BY ch.ma_chi_tiet_hang LIMIT 1) AS ten_nguoi_nhan,
        (SELECT ch.sdt_nguoi_nhan FROM chi_tiet_hang ch WHERE ch.ma_dat_dich_vu_id = dv.ma_dat_dich_vu_id ORDER BY ch.ma_chi_tiet_hang LIMIT 1) AS sdt_nguoi_nhan,
        (SELECT ch.loai_hang FROM chi_tiet_hang ch WHERE ch.ma_dat_dich_vu_id = dv.ma_dat_dich_vu_id ORDER BY ch.ma_chi_tiet_hang LIMIT 1) AS loai_hang,
        (SELECT ch.trang_thai_van_don FROM chi_tiet_hang ch WHERE ch.ma_dat_dich_vu_id = dv.ma_dat_dich_vu_id ORDER BY ch.ma_chi_tiet_hang LIMIT 1) AS trang_thai_van_don
     FROM dat_dich_vu dv
     INNER JOIN dich_vu ds ON ds.ma_dich_vu = dv.ma_dich_vu
     WHERE (? = '' OR dv.ma_dat_dich_vu LIKE ? OR dv.ho_ten LIKE ? OR dv.so_dien_thoai LIKE ?)
       AND (? = '' OR dv.trang_thai = ?)
     ORDER BY dv.ngay_dat DESC LIMIT 100"
);
$stmt->bind_param('ssssss', $search, $searchLike, $searchLike, $searchLike, $statusFilter, $statusFilter);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$activePage = 'services';
$pageTitle = 'Đơn dịch vụ & gửi hàng';
require __DIR__ . '/../../../includes/header.php';
?>
<div class="page-intro"><div><h2>Đơn dịch vụ</h2><p class="muted">Theo dõi dịch vụ trung chuyển, gửi hàng và tiến trình vận đơn.</p></div><span class="status"><?= count($orders) ?> đơn</span></div>
<section class="card">
    <form class="filter-form" method="get" action="services.php">
        <div class="field grow"><label for="q">Tìm mã đơn, tên hoặc số điện thoại</label><input id="q" name="q" value="<?= admin_e($search) ?>" placeholder="Tìm đơn dịch vụ"></div>
        <div class="field"><label for="status">Trạng thái đơn</label><select id="status" name="status"><option value="">Tất cả trạng thái</option><?php foreach ($serviceStatuses as $option): ?><option value="<?= admin_e($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option><?php endforeach; ?></select></div>
        <button class="button button-primary" type="submit">Lọc</button><a class="button button-light" href="services.php">Xóa lọc</a>
    </form>
</section>
<section class="card">
    <div class="table-wrap"><table>
        <thead><tr><th>Mã đơn / dịch vụ</th><th>Khách hàng</th><th>Thông tin gửi hàng</th><th>Ngày sử dụng</th><th>Tổng tiền</th><th>Trạng thái đơn</th><th>Vận đơn</th></tr></thead>
        <tbody>
        <?php if (!$orders): ?><tr><td colspan="7" class="empty-state">Chưa có đơn dịch vụ phù hợp.</td></tr><?php endif; ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong><?= admin_e($order['ma_dat_dich_vu']) ?></strong><br><?= admin_e($order['ten_dich_vu']) ?><br><span class="muted small">Số lượng: <?= (int) $order['so_luong'] ?></span></td>
                <td><strong><?= admin_e($order['ho_ten']) ?></strong><br><?= admin_e($order['so_dien_thoai']) ?><br><span class="muted small">Đơn vị tài khoản #<?= admin_e($order['ma_nguoi_dung'] ?? 'khách') ?></span></td>
                <td><?php if ($order['ma_van_don']): ?><strong><?= admin_e($order['ma_van_don']) ?></strong><br><?= admin_e($order['loai_hang']) ?> · người nhận <?= admin_e($order['ten_nguoi_nhan']) ?><br><span class="muted small"><?= admin_e($order['sdt_nguoi_nhan']) ?></span><?php else: ?><?= admin_e($order['dia_chi_don'] ?: 'Chưa có địa chỉ nhận') ?> → <?= admin_e($order['dia_chi_nhan'] ?: 'Chưa có địa chỉ giao') ?><?php endif; ?></td>
                <td><?= admin_e($order['ngay_su_dung'] ? date('d/m/Y H:i', strtotime($order['ngay_su_dung'])) : 'Chưa đặt lịch') ?><br><span class="muted small">Đặt <?= admin_e(date('d/m/Y H:i', strtotime($order['ngay_dat']))) ?></span></td>
                <td><strong><?= admin_e(admin_money($order['tong_tien'])) ?></strong></td>
                <td>
                    <span class="<?= admin_e(admin_status_class($order['trang_thai'])) ?>"><?= admin_e(admin_status_label($order['trang_thai'])) ?></span>
                    <form class="inline-form" method="post" action="services.php" style="margin-top:7px">
                        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="update_order"><input type="hidden" name="order_id" value="<?= (int) $order['ma_dat_dich_vu_id'] ?>">
                        <select name="status" aria-label="Trạng thái đơn <?= admin_e($order['ma_dat_dich_vu']) ?>"><?php foreach ($serviceStatuses as $option): ?><option value="<?= admin_e($option) ?>" <?= $order['trang_thai'] === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option><?php endforeach; ?></select>
                        <button class="button button-primary button-small" type="submit">Lưu</button>
                    </form>
                </td>
                <td><?php if ($order['ma_van_don']): ?>
                    <span class="<?= admin_e(admin_status_class($order['trang_thai_van_don'])) ?>"><?= admin_e(admin_status_label($order['trang_thai_van_don'])) ?></span>
                    <form class="inline-form" method="post" action="services.php" style="margin-top:7px">
                        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="update_parcel"><input type="hidden" name="order_id" value="<?= (int) $order['ma_dat_dich_vu_id'] ?>">
                        <select name="status" aria-label="Trạng thái vận đơn <?= admin_e($order['ma_van_don']) ?>"><?php foreach ($parcelStatuses as $option): ?><option value="<?= admin_e($option) ?>" <?= $order['trang_thai_van_don'] === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option><?php endforeach; ?></select>
                        <button class="button button-primary button-small" type="submit">Lưu</button>
                    </form>
                <?php else: ?><span class="muted">Không có vận đơn</span><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
