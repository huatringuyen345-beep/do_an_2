<?php
/*
 * Điều hành chuyến xe: tạo/sửa chuyến, gán tuyến, xe và tài xế,
 * đồng thời cập nhật trạng thái chuyến xe.
 */

require_once __DIR__ . '/../../_bootstrap.php';
admin_require_staff();

$tripStatuses = ['mo_ban', 'het_cho', 'dang_chay', 'hoan_thanh', 'da_huy'];
$error = '';
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$editTrip = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $action = admin_post_string('action', 30);
    if ($action === 'save_trip') {
        $tripId = filter_var($_POST['trip_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $routeId = filter_var($_POST['route_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $vehicleId = filter_var($_POST['vehicle_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $driverRaw = $_POST['driver_id'] ?? '';
        $driverId = $driverRaw === '' ? null : (filter_var($driverRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $departureRaw = admin_post_string('departure_at', 20);
        $arrivalRaw = admin_post_string('arrival_at', 20);
        $priceRaw = admin_post_string('price', 30);
        $status = admin_post_string('status', 30);
        $departure = DateTime::createFromFormat('Y-m-d\TH:i', $departureRaw);
        $arrival = $arrivalRaw === '' ? null : DateTime::createFromFormat('Y-m-d\TH:i', $arrivalRaw);
        $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);

        if (
            !$routeId || !$vehicleId || $driverId === 0
            || !$departure || $departure->format('Y-m-d\TH:i') !== $departureRaw
            || ($arrivalRaw !== '' && (!$arrival || $arrival->format('Y-m-d\TH:i') !== $arrivalRaw))
            || ($arrival && $arrival <= $departure)
            || $price === false || $price < 0
            || !admin_valid_enum($status, $tripStatuses)
        ) {
            $error = 'Thông tin chuyến xe chưa hợp lệ. Vui lòng kiểm tra tuyến, xe, thời gian, giá vé và trạng thái.';
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tuyen_duong WHERE ma_tuyen = ? AND trang_thai = 'hoat_dong'");
            $stmt->bind_param('i', $routeId);
            $stmt->execute();
            $routeIsActive = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
            $stmt->close();
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM xe WHERE ma_xe = ? AND trang_thai = 'hoat_dong'");
            $stmt->bind_param('i', $vehicleId);
            $stmt->execute();
            $vehicleIsActive = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
            $stmt->close();
            $driverIsActive = true;
            if ($driverId !== null) {
                $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM nguoi_dung WHERE ma_nguoi_dung = ? AND ma_vai_tro = 4 AND trang_thai = 'hoat_dong'");
                $stmt->bind_param('i', $driverId);
                $stmt->execute();
                $driverIsActive = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
                $stmt->close();
            }
            if (!$routeIsActive || !$vehicleIsActive || !$driverIsActive) {
                $error = 'Tuyến, xe hoặc tài xế đã chọn không còn hoạt động. Vui lòng tải lại danh sách.';
            }
        }

        if ($error === '') {
            $departureSql = $departure->format('Y-m-d H:i:s');
            $arrivalSql = $arrival ? $arrival->format('Y-m-d H:i:s') : null;
            try {
                if ($tripId) {
                    $stmt = $conn->prepare(
                        'UPDATE chuyen_xe SET ma_tuyen = ?, ma_xe = ?, ma_tai_xe = ?, gio_khoi_hanh = ?, gio_den_du_kien = ?, gia_ve = ?, trang_thai = ? WHERE ma_chuyen = ?'
                    );
                    $stmt->bind_param('iiissdsi', $routeId, $vehicleId, $driverId, $departureSql, $arrivalSql, $price, $status, $tripId);
                    $stmt->execute();
                    $stmt->close();
                    admin_flash('success', 'Đã cập nhật chuyến xe.');
                } else {
                    $stmt = $conn->prepare(
                        'INSERT INTO chuyen_xe (ma_tuyen, ma_xe, ma_tai_xe, gio_khoi_hanh, gio_den_du_kien, gia_ve, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->bind_param('iiissds', $routeId, $vehicleId, $driverId, $departureSql, $arrivalSql, $price, $status);
                    $stmt->execute();
                    $stmt->close();
                    admin_flash('success', 'Đã tạo chuyến xe mới.');
                }
                admin_redirect('trips.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Admin trip save failed: ' . $exception->getMessage());
                $error = 'Không thể lưu chuyến xe. Hãy kiểm tra dữ liệu liên kết và thử lại.';
            }
        }
    } elseif ($action === 'update_status') {
        $tripId = filter_var($_POST['trip_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = admin_post_string('status', 30);
        if (!$tripId || !admin_valid_enum($status, $tripStatuses)) {
            $error = 'Trạng thái chuyến xe không hợp lệ.';
        } else {
            try {
                $stmt = $conn->prepare('UPDATE chuyen_xe SET trang_thai = ? WHERE ma_chuyen = ?');
                $stmt->bind_param('si', $status, $tripId);
                $stmt->execute();
                $stmt->close();
                admin_flash('success', 'Đã cập nhật trạng thái chuyến xe.');
                admin_redirect('trips.php');
            } catch (mysqli_sql_exception $exception) {
                error_log('Admin trip status update failed: ' . $exception->getMessage());
                $error = 'Không thể cập nhật trạng thái chuyến xe. Vui lòng thử lại.';
            }
        }
    }
}

if ($editId) {
    $stmt = $conn->prepare('SELECT * FROM chuyen_xe WHERE ma_chuyen = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editTrip = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$routes = $conn->query("SELECT ma_tuyen, ten_tuyen FROM tuyen_duong WHERE trang_thai = 'hoat_dong' ORDER BY ten_tuyen")->fetch_all(MYSQLI_ASSOC);
$vehicles = $conn->query(
    "SELECT x.ma_xe, x.bien_so, x.ten_xe, lx.ten_loai_xe
     FROM xe x INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
     WHERE x.trang_thai = 'hoat_dong' ORDER BY x.bien_so"
)->fetch_all(MYSQLI_ASSOC);
$drivers = $conn->query(
    "SELECT ma_nguoi_dung, ho_ten FROM nguoi_dung
     WHERE ma_vai_tro = 4 AND trang_thai = 'hoat_dong' ORDER BY ho_ten"
)->fetch_all(MYSQLI_ASSOC);
$trips = $conn->query(
    "SELECT cx.*, td.ten_tuyen, x.bien_so, x.ten_xe, nd.ho_ten AS tai_xe,
        (SELECT COUNT(*) FROM chi_tiet_ve ct WHERE ct.ma_chuyen = cx.ma_chuyen AND ct.trang_thai <> 'da_huy') AS so_ve
     FROM chuyen_xe cx
     INNER JOIN tuyen_duong td ON td.ma_tuyen = cx.ma_tuyen
     INNER JOIN xe x ON x.ma_xe = cx.ma_xe
     LEFT JOIN nguoi_dung nd ON nd.ma_nguoi_dung = cx.ma_tai_xe
     ORDER BY cx.gio_khoi_hanh DESC
     LIMIT 100"
)->fetch_all(MYSQLI_ASSOC);

$activePage = 'trips';
$pageTitle = 'Quản lý chuyến xe';
require __DIR__ . '/../../../includes/header.php';
?>
<div class="page-intro">
    <div><h2>Lịch chạy và điều phối</h2><p class="muted">Tạo chuyến, phân xe/tài xế và cập nhật tình trạng bán vé.</p></div>
    <?php if ($editTrip): ?><a class="button button-light" href="trips.php">Hủy chỉnh sửa</a><?php endif; ?>
</div>
<?php if ($error !== ''): ?><div class="notice notice-error" role="alert"><?= admin_e($error) ?></div><?php endif; ?>

<section class="card">
    <div class="card-head"><h2><?= $editTrip ? 'Cập nhật chuyến #' . (int) $editTrip['ma_chuyen'] : 'Tạo chuyến xe' ?></h2></div>
    <?php if (!$routes || !$vehicles): ?>
        <div class="notice notice-error">Cần có ít nhất một tuyến đang hoạt động và một xe đang hoạt động trước khi tạo chuyến.</div>
    <?php else: ?>
        <form method="post" action="trips.php<?= $editTrip ? '?edit=' . (int) $editTrip['ma_chuyen'] : '' ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>">
            <input type="hidden" name="action" value="save_trip">
            <input type="hidden" name="trip_id" value="<?= (int) ($editTrip['ma_chuyen'] ?? 0) ?>">
            <div class="grid-3">
                <label>Tuyến
                    <select name="route_id" required>
                        <option value="">Chọn tuyến</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?= (int) $route['ma_tuyen'] ?>" <?= (int) ($editTrip['ma_tuyen'] ?? 0) === (int) $route['ma_tuyen'] ? 'selected' : '' ?>><?= admin_e($route['ten_tuyen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Xe
                    <select name="vehicle_id" required>
                        <option value="">Chọn xe</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= (int) $vehicle['ma_xe'] ?>" <?= (int) ($editTrip['ma_xe'] ?? 0) === (int) $vehicle['ma_xe'] ? 'selected' : '' ?>><?= admin_e($vehicle['bien_so'] . ' · ' . ($vehicle['ten_xe'] ?? '') . ' · ' . $vehicle['ten_loai_xe']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Tài xế
                    <select name="driver_id">
                        <option value="">Chưa phân công</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= (int) $driver['ma_nguoi_dung'] ?>" <?= (int) ($editTrip['ma_tai_xe'] ?? 0) === (int) $driver['ma_nguoi_dung'] ? 'selected' : '' ?>><?= admin_e($driver['ho_ten']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Giờ khởi hành
                    <input type="datetime-local" name="departure_at" required value="<?= admin_e(isset($editTrip['gio_khoi_hanh']) ? date('Y-m-d\TH:i', strtotime($editTrip['gio_khoi_hanh'])) : '') ?>">
                </label>
                <label>Giờ đến dự kiến
                    <input type="datetime-local" name="arrival_at" value="<?= admin_e(!empty($editTrip['gio_den_du_kien']) ? date('Y-m-d\TH:i', strtotime($editTrip['gio_den_du_kien'])) : '') ?>">
                </label>
                <label>Giá vé (VNĐ)
                    <input type="number" name="price" min="0" step="1000" required value="<?= admin_e($editTrip['gia_ve'] ?? '') ?>">
                </label>
                <label>Trạng thái
                    <select name="status">
                        <?php foreach ($tripStatuses as $option): ?>
                            <option value="<?= admin_e($option) ?>" <?= ($editTrip['trang_thai'] ?? 'mo_ban') === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="form-actions"><button class="button button-primary" type="submit"><?= $editTrip ? 'Lưu chuyến xe' : 'Tạo chuyến xe' ?></button></div>
        </form>
    <?php endif; ?>
</section>

<section class="card">
    <div class="card-head"><h2>Danh sách chuyến xe</h2><span class="muted small">Tối đa 100 chuyến mới nhất</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Thời gian</th><th>Tuyến</th><th>Xe / tài xế</th><th>Giá vé</th><th>Đã đặt</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
        <tbody>
        <?php if (!$trips): ?><tr><td colspan="7" class="empty-state">Chưa có chuyến xe.</td></tr><?php endif; ?>
        <?php foreach ($trips as $trip): ?>
            <tr>
                <td><strong><?= admin_e(date('d/m/Y H:i', strtotime($trip['gio_khoi_hanh']))) ?></strong><br><span class="muted small">Đến: <?= admin_e($trip['gio_den_du_kien'] ? date('d/m/Y H:i', strtotime($trip['gio_den_du_kien'])) : 'Chưa đặt') ?></span></td>
                <td><?= admin_e($trip['ten_tuyen']) ?></td>
                <td><strong><?= admin_e($trip['bien_so']) ?></strong><br><?= admin_e($trip['ten_xe'] ?: 'Chưa đặt tên') ?><br><span class="muted small">Tài xế: <?= admin_e($trip['tai_xe'] ?: 'Chưa phân công') ?></span></td>
                <td><?= admin_e(admin_money($trip['gia_ve'])) ?></td>
                <td><?= (int) $trip['so_ve'] ?></td>
                <td><span class="<?= admin_e(admin_status_class($trip['trang_thai'])) ?>"><?= admin_e(admin_status_label($trip['trang_thai'])) ?></span></td>
                <td>
                    <a class="button button-light button-small" href="trips.php?edit=<?= (int) $trip['ma_chuyen'] ?>">Sửa</a>
                    <form class="inline-form" method="post" action="trips.php" style="margin-top:6px">
                        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="trip_id" value="<?= (int) $trip['ma_chuyen'] ?>">
                        <select name="status" aria-label="Trạng thái chuyến #<?= (int) $trip['ma_chuyen'] ?>">
                            <?php foreach ($tripStatuses as $option): ?>
                                <option value="<?= admin_e($option) ?>" <?= $trip['trang_thai'] === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="button button-primary button-small" type="submit">Lưu</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
