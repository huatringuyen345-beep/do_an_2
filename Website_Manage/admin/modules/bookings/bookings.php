<?php
/*
 * Danh sách đặt vé cho nhân viên: tìm/lọc đơn, xem vé và thanh toán liên quan,
 * cập nhật trạng thái đơn trong giao dịch và đồng bộ trạng thái vé chi tiết.
 */

require_once __DIR__ . '/../../_bootstrap.php';
admin_require_staff();

$allowedStatuses = ['cho_xac_nhan', 'da_xac_nhan', 'da_thanh_toan', 'da_huy', 'hoan_tien'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $bookingId = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $status = admin_post_string('status', 30);

    if (!$bookingId || !admin_valid_enum($status, $allowedStatuses)) {
        admin_flash('error', 'Thông tin cập nhật đặt vé không hợp lệ.');
        admin_redirect('bookings.php');
    }

    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare('SELECT ma_dat_ve_id FROM dat_ve WHERE ma_dat_ve_id = ? FOR UPDATE');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $bookingExists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$bookingExists) {
            $conn->rollback();
            admin_flash('error', 'Không tìm thấy đơn đặt vé.');
            admin_redirect('bookings.php');
        }
        $stmt = $conn->prepare('UPDATE dat_ve SET trang_thai = ? WHERE ma_dat_ve_id = ?');
        $stmt->bind_param('si', $status, $bookingId);
        $stmt->execute();
        $stmt->close();

        $ticketStatus = 'giu_cho';
        if (in_array($status, ['da_xac_nhan', 'da_thanh_toan'], true)) {
            $ticketStatus = 'da_xac_nhan';
        } elseif (in_array($status, ['da_huy', 'hoan_tien'], true)) {
            $ticketStatus = 'da_huy';
        }
        $stmt = $conn->prepare('UPDATE chi_tiet_ve SET trang_thai = ? WHERE ma_dat_ve_id = ?');
        $stmt->bind_param('si', $ticketStatus, $bookingId);
        $stmt->execute();
        $stmt->close();
        $conn->commit();

        admin_flash('success', 'Đã cập nhật trạng thái đặt vé và các vé chi tiết.');
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        error_log('Admin booking update failed: ' . $exception->getMessage());
        admin_flash('error', 'Không thể cập nhật đặt vé. Vui lòng kiểm tra database rồi thử lại.');
    }
    admin_redirect('bookings.php');
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 100, 'UTF-8')) : '';
$statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if ($statusFilter !== '' && !admin_valid_enum($statusFilter, $allowedStatuses)) {
    $statusFilter = '';
}
$searchLike = '%' . $search . '%';
$stmt = $conn->prepare(
    "SELECT d.*,
        (SELECT GROUP_CONCAT(CONCAT(td.ten_tuyen, ' · ', DATE_FORMAT(cx.gio_khoi_hanh, '%d/%m %H:%i'), ' · ghế ', gx.so_ghe) SEPARATOR '; ')
         FROM chi_tiet_ve ct
         INNER JOIN chuyen_xe cx ON cx.ma_chuyen = ct.ma_chuyen
         INNER JOIN tuyen_duong td ON td.ma_tuyen = cx.ma_tuyen
         INNER JOIN ghe_xe gx ON gx.ma_ghe = ct.ma_ghe
         WHERE ct.ma_dat_ve_id = d.ma_dat_ve_id) AS thong_tin_ve,
        (SELECT GROUP_CONCAT(CONCAT(tt.trang_thai, ': ', FORMAT(tt.so_tien, 0)) SEPARATOR '; ')
         FROM thanh_toan tt WHERE tt.ma_dat_ve_id = d.ma_dat_ve_id) AS thong_tin_thanh_toan
     FROM dat_ve d
     WHERE (? = '' OR d.ma_dat_ve LIKE ? OR d.ten_khach_hang LIKE ? OR d.sdt_khach_hang LIKE ?)
       AND (? = '' OR d.trang_thai = ?)
     ORDER BY d.ngay_dat DESC
     LIMIT 100"
);
$stmt->bind_param('ssssss', $search, $searchLike, $searchLike, $searchLike, $statusFilter, $statusFilter);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$activePage = 'bookings';
$pageTitle = 'Quản lý đặt vé';
require __DIR__ . '/../../../includes/header.php';
?>
<div class="page-intro">
    <div><h2>Danh sách đặt vé</h2><p class="muted">Tối đa 100 đơn mới nhất · Cập nhật trạng thái đồng bộ với vé chi tiết.</p></div>
    <span class="status"><?= count($bookings) ?> đơn</span>
</div>

<section class="card">
    <form class="filter-form" method="get" action="bookings.php">
        <div class="field grow"><label for="q">Tìm mã vé, tên hoặc số điện thoại</label><input id="q" name="q" value="<?= admin_e($search) ?>" placeholder="VD: DV2026..."></div>
        <div class="field"><label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option value="<?= admin_e($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Lọc danh sách</button>
        <a class="button button-light" href="bookings.php">Xóa lọc</a>
    </form>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Mã đặt vé</th><th>Khách hàng</th><th>Hành trình / ghế</th><th>Thanh toán</th><th>Thành tiền</th><th>Ngày đặt</th><th>Trạng thái / cập nhật</th></tr></thead>
            <tbody>
            <?php if (!$bookings): ?>
                <tr><td colspan="7" class="empty-state">Không tìm thấy đơn đặt vé.</td></tr>
            <?php endif; ?>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><strong><?= admin_e($booking['ma_dat_ve']) ?></strong><br><span class="muted small">#<?= (int) $booking['ma_dat_ve_id'] ?></span></td>
                    <td><strong><?= admin_e($booking['ten_khach_hang']) ?></strong><br><?= admin_e($booking['sdt_khach_hang']) ?><br><span class="muted small"><?= admin_e($booking['email_khach_hang'] ?: 'Không có email') ?></span></td>
                    <td><?= admin_e($booking['thong_tin_ve'] ?: 'Chưa có chi tiết vé') ?></td>
                    <td><?= admin_e($booking['thong_tin_thanh_toan'] ?: 'Chưa ghi nhận') ?></td>
                    <td><strong><?= admin_e(admin_money($booking['thanh_tien'])) ?></strong><br><span class="muted small">Tổng <?= admin_e(admin_money($booking['tong_tien'])) ?></span></td>
                    <td><?= admin_e(date('d/m/Y H:i', strtotime($booking['ngay_dat']))) ?></td>
                    <td>
                        <span class="<?= admin_e(admin_status_class($booking['trang_thai'])) ?>"><?= admin_e(admin_status_label($booking['trang_thai'])) ?></span>
                        <form class="inline-form" method="post" action="bookings.php" style="margin-top:7px">
                            <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>">
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['ma_dat_ve_id'] ?>">
                            <select name="status" aria-label="Trạng thái đơn <?= admin_e($booking['ma_dat_ve']) ?>">
                                <?php foreach ($allowedStatuses as $option): ?>
                                    <option value="<?= admin_e($option) ?>" <?= $booking['trang_thai'] === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="button button-primary button-small" type="submit">Lưu</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
