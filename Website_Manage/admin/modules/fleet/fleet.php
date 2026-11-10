<?php
/*
 * Quản lý dữ liệu vận tải nền: điểm dừng, tuyến đường, xe và sơ đồ ghế.
 */

require_once __DIR__ . '/../../_bootstrap.php';
admin_require_staff();

$error = '';
$stopId = filter_var($_GET['edit_stop'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$routeId = filter_var($_GET['edit_route'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$vehicleId = filter_var($_GET['edit_vehicle'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$seatId = filter_var($_GET['edit_seat'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();
    $action = admin_post_string('action', 30);
    try {
        if ($action === 'save_stop') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $name = admin_post_string('name', 120);
            $address = admin_post_string('address', 255);
            $province = admin_post_string('province', 100);
            $phone = admin_post_string('phone', 20);
            if ($name === '' || $address === '' || $province === '') {
                throw new InvalidArgumentException('Tên điểm dừng, địa chỉ và tỉnh/thành không được để trống.');
            }
            if ($id) {
                $stmt = $conn->prepare('UPDATE diem_dung SET ten_diem_dung = ?, dia_chi = ?, tinh_thanh = ?, so_dien_thoai = ? WHERE ma_diem_dung = ?');
                $stmt->bind_param('ssssi', $name, $address, $province, $phone, $id);
            } else {
                $stmt = $conn->prepare('INSERT INTO diem_dung (ten_diem_dung, dia_chi, tinh_thanh, so_dien_thoai) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $name, $address, $province, $phone);
            }
            $stmt->execute();
            $stmt->close();
            admin_flash('success', $id ? 'Đã cập nhật điểm dừng.' : 'Đã thêm điểm dừng.');
        } elseif ($action === 'save_route') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $name = admin_post_string('name', 150);
            $originId = filter_var($_POST['origin_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $destinationId = filter_var($_POST['destination_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $distanceRaw = admin_post_string('distance', 20);
            $durationRaw = admin_post_string('duration', 20);
            $priceRaw = admin_post_string('base_price', 30);
            $status = admin_post_string('status', 30);
            $distance = $distanceRaw === '' ? null : filter_var($distanceRaw, FILTER_VALIDATE_FLOAT);
            $duration = $durationRaw === '' ? null : filter_var($durationRaw, FILTER_VALIDATE_INT);
            $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
            if (
                $name === '' || !$originId || !$destinationId || $originId === $destinationId
                || ($distanceRaw !== '' && ($distance === false || $distance < 0))
                || ($durationRaw !== '' && ($duration === false || $duration < 0))
                || $price === false || $price < 0
                || !admin_valid_enum($status, ['hoat_dong', 'tam_ngung'])
            ) {
                throw new InvalidArgumentException('Vui lòng kiểm tra tên tuyến, điểm đi/đến, quãng đường, thời gian và giá vé.');
            }
            if ($id) {
                $stmt = $conn->prepare(
                    'UPDATE tuyen_duong SET ten_tuyen = ?, ma_diem_di = ?, ma_diem_den = ?, khoang_cach_km = ?, thoi_gian_phut = ?, gia_ve_co_ban = ?, trang_thai = ? WHERE ma_tuyen = ?'
                );
                $stmt->bind_param('siididsi', $name, $originId, $destinationId, $distance, $duration, $price, $status, $id);
            } else {
                $stmt = $conn->prepare(
                    'INSERT INTO tuyen_duong (ten_tuyen, ma_diem_di, ma_diem_den, khoang_cach_km, thoi_gian_phut, gia_ve_co_ban, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('siidids', $name, $originId, $destinationId, $distance, $duration, $price, $status);
            }
            $stmt->execute();
            $stmt->close();
            admin_flash('success', $id ? 'Đã cập nhật tuyến đường.' : 'Đã thêm tuyến đường.');
        } elseif ($action === 'save_vehicle') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $typeId = filter_var($_POST['type_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $plate = admin_post_string('plate', 20);
            $name = admin_post_string('name', 100);
            $status = admin_post_string('status', 30);
            if (
                !$typeId || $plate === ''
                || !preg_match('/^[A-Za-z0-9 .-]{4,20}$/', $plate)
                || !admin_valid_enum($status, ['hoat_dong', 'bao_tri', 'ngung_hoat_dong'])
            ) {
                throw new InvalidArgumentException('Vui lòng nhập biển số hợp lệ, chọn loại xe và trạng thái.');
            }
            if ($id) {
                $stmt = $conn->prepare('UPDATE xe SET ma_loai_xe = ?, bien_so = ?, ten_xe = ?, trang_thai = ? WHERE ma_xe = ?');
                $stmt->bind_param('isssi', $typeId, $plate, $name, $status, $id);
            } else {
                $stmt = $conn->prepare('INSERT INTO xe (ma_loai_xe, bien_so, ten_xe, trang_thai) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('isss', $typeId, $plate, $name, $status);
            }
            $stmt->execute();
            $stmt->close();
            admin_flash('success', $id ? 'Đã cập nhật thông tin xe.' : 'Đã thêm xe vào đội xe.');
        } elseif ($action === 'save_seat') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $carId = filter_var($_POST['car_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
            $seatNumber = admin_post_string('seat_number', 10);
            $floor = filter_var($_POST['floor'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
            if (!$carId || $seatNumber === '' || !$floor) {
                throw new InvalidArgumentException('Xe, mã ghế và tầng là bắt buộc.');
            }
            $stmt = $conn->prepare('SELECT ma_ghe FROM ghe_xe WHERE ma_xe = ? AND so_ghe = ? AND ma_ghe <> ? LIMIT 1');
            $stmt->bind_param('isi', $carId, $seatNumber, $id);
            $stmt->execute();
            $duplicate = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($duplicate) {
                throw new InvalidArgumentException('Xe này đã có mã ghế đó.');
            }
            if ($id) {
                $stmt = $conn->prepare('UPDATE ghe_xe SET ma_xe = ?, so_ghe = ?, tang = ? WHERE ma_ghe = ?');
                $stmt->bind_param('isii', $carId, $seatNumber, $floor, $id);
            } else {
                $stmt = $conn->prepare('INSERT INTO ghe_xe (ma_xe, so_ghe, tang) VALUES (?, ?, ?)');
                $stmt->bind_param('isi', $carId, $seatNumber, $floor);
            }
            $stmt->execute();
            $stmt->close();
            admin_flash('success', $id ? 'Đã cập nhật ghế xe.' : 'Đã thêm ghế xe.');
        } elseif ($action === 'delete_seat') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id) {
                throw new InvalidArgumentException('Mã ghế không hợp lệ.');
            }
            $stmt = $conn->prepare('DELETE FROM ghe_xe WHERE ma_ghe = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            admin_flash('success', 'Đã xóa ghế chưa được sử dụng.');
        } else {
            throw new InvalidArgumentException('Thao tác không hợp lệ.');
        }
        admin_redirect('fleet.php');
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (mysqli_sql_exception $exception) {
        error_log('Admin fleet operation failed: ' . $exception->getMessage());
        $error = 'Không thể lưu thay đổi. Có thể dữ liệu đang được chuyến xe/đặt vé sử dụng hoặc mã đã tồn tại.';
    }
}

$stops = $conn->query('SELECT * FROM diem_dung ORDER BY tinh_thanh, ten_diem_dung')->fetch_all(MYSQLI_ASSOC);
$routes = $conn->query(
    'SELECT td.*, dd1.ten_diem_dung AS diem_di, dd2.ten_diem_dung AS diem_den
     FROM tuyen_duong td
     INNER JOIN diem_dung dd1 ON dd1.ma_diem_dung = td.ma_diem_di
     INNER JOIN diem_dung dd2 ON dd2.ma_diem_dung = td.ma_diem_den
     ORDER BY td.ten_tuyen'
)->fetch_all(MYSQLI_ASSOC);
$vehicleTypes = $conn->query('SELECT ma_loai_xe, ten_loai_xe FROM loai_xe ORDER BY ten_loai_xe')->fetch_all(MYSQLI_ASSOC);
$vehicles = $conn->query(
    'SELECT x.*, lx.ten_loai_xe, lx.so_ghe AS suc_chua,
        (SELECT COUNT(*) FROM ghe_xe gx WHERE gx.ma_xe = x.ma_xe) AS so_ghe
     FROM xe x INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
     ORDER BY x.bien_so'
)->fetch_all(MYSQLI_ASSOC);
$seats = $conn->query(
    'SELECT gx.*, x.bien_so, x.ten_xe
     FROM ghe_xe gx INNER JOIN xe x ON x.ma_xe = gx.ma_xe
     ORDER BY x.bien_so, gx.tang, gx.so_ghe'
)->fetch_all(MYSQLI_ASSOC);
$editStop = null;
$editRoute = null;
$editVehicle = null;
$editSeat = null;
foreach ([['diem_dung', 'ma_diem_dung', $stopId, 'editStop'], ['tuyen_duong', 'ma_tuyen', $routeId, 'editRoute'], ['xe', 'ma_xe', $vehicleId, 'editVehicle'], ['ghe_xe', 'ma_ghe', $seatId, 'editSeat']] as [$table, $primaryKey, $id, $variable]) {
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$primaryKey` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $$variable = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$activePage = 'fleet';
$pageTitle = 'Tuyến & đội xe';
require __DIR__ . '/../../../includes/header.php';
?>
<div class="page-intro"><div><h2>Danh mục vận hành</h2><p class="muted">Quản lý điểm dừng, tuyến đường, phương tiện và sơ đồ ghế.</p></div></div>
<?php if ($error !== ''): ?><div class="notice notice-error" role="alert"><?= admin_e($error) ?></div><?php endif; ?>
<nav class="section-tabs" aria-label="Các danh mục đội xe">
    <a href="#stops">Điểm dừng</a><a href="#routes">Tuyến đường</a><a href="#vehicles">Xe</a><a href="#seats">Ghế xe</a>
</nav>

<section class="card" id="stops">
    <div class="card-head"><h2><?= $editStop ? 'Sửa điểm dừng' : 'Thêm điểm dừng' ?></h2><?php if ($editStop): ?><a class="button button-light button-small" href="fleet.php#stops">Hủy sửa</a><?php endif; ?></div>
    <form method="post" action="fleet.php#stops" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="save_stop"><input type="hidden" name="id" value="<?= (int) ($editStop['ma_diem_dung'] ?? 0) ?>">
        <div class="grid-3">
            <label>Tên điểm dừng<input name="name" maxlength="120" required value="<?= admin_e($editStop['ten_diem_dung'] ?? '') ?>"></label>
            <label>Địa chỉ<input name="address" maxlength="255" required value="<?= admin_e($editStop['dia_chi'] ?? '') ?>"></label>
            <label>Tỉnh / thành<input name="province" maxlength="100" required value="<?= admin_e($editStop['tinh_thanh'] ?? '') ?>"></label>
            <label>Số điện thoại<input name="phone" maxlength="20" value="<?= admin_e($editStop['so_dien_thoai'] ?? '') ?>"></label>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit"><?= $editStop ? 'Lưu điểm dừng' : 'Thêm điểm dừng' ?></button></div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Tên điểm dừng</th><th>Địa chỉ</th><th>Tỉnh/thành</th><th>Điện thoại</th><th></th></tr></thead><tbody>
        <?php foreach ($stops as $stop): ?><tr><td><strong><?= admin_e($stop['ten_diem_dung']) ?></strong></td><td><?= admin_e($stop['dia_chi']) ?></td><td><?= admin_e($stop['tinh_thanh']) ?></td><td><?= admin_e($stop['so_dien_thoai'] ?: '—') ?></td><td><a class="button button-light button-small" href="fleet.php?edit_stop=<?= (int) $stop['ma_diem_dung'] ?>#stops">Sửa</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<section class="card" id="routes">
    <div class="card-head"><h2><?= $editRoute ? 'Sửa tuyến đường' : 'Thêm tuyến đường' ?></h2><?php if ($editRoute): ?><a class="button button-light button-small" href="fleet.php#routes">Hủy sửa</a><?php endif; ?></div>
    <form method="post" action="fleet.php#routes" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="save_route"><input type="hidden" name="id" value="<?= (int) ($editRoute['ma_tuyen'] ?? 0) ?>">
        <div class="grid-3">
            <label>Tên tuyến<input name="name" maxlength="150" required value="<?= admin_e($editRoute['ten_tuyen'] ?? '') ?>"></label>
            <label>Điểm đi<select name="origin_id" required><option value="">Chọn điểm đi</option><?php foreach ($stops as $stop): ?><option value="<?= (int) $stop['ma_diem_dung'] ?>" <?= (int) ($editRoute['ma_diem_di'] ?? 0) === (int) $stop['ma_diem_dung'] ? 'selected' : '' ?>><?= admin_e($stop['ten_diem_dung'] . ' · ' . $stop['tinh_thanh']) ?></option><?php endforeach; ?></select></label>
            <label>Điểm đến<select name="destination_id" required><option value="">Chọn điểm đến</option><?php foreach ($stops as $stop): ?><option value="<?= (int) $stop['ma_diem_dung'] ?>" <?= (int) ($editRoute['ma_diem_den'] ?? 0) === (int) $stop['ma_diem_dung'] ? 'selected' : '' ?>><?= admin_e($stop['ten_diem_dung'] . ' · ' . $stop['tinh_thanh']) ?></option><?php endforeach; ?></select></label>
            <label>Khoảng cách (km)<input type="number" min="0" step="0.1" name="distance" value="<?= admin_e($editRoute['khoang_cach_km'] ?? '') ?>"></label>
            <label>Thời gian (phút)<input type="number" min="0" step="1" name="duration" value="<?= admin_e($editRoute['thoi_gian_phut'] ?? '') ?>"></label>
            <label>Giá vé cơ bản (VNĐ)<input type="number" min="0" step="1000" name="base_price" required value="<?= admin_e($editRoute['gia_ve_co_ban'] ?? '') ?>"></label>
            <label>Trạng thái<select name="status"><option value="hoat_dong" <?= ($editRoute['trang_thai'] ?? 'hoat_dong') === 'hoat_dong' ? 'selected' : '' ?>>Hoạt động</option><option value="tam_ngung" <?= ($editRoute['trang_thai'] ?? '') === 'tam_ngung' ? 'selected' : '' ?>>Tạm ngưng</option></select></label>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit"><?= $editRoute ? 'Lưu tuyến' : 'Thêm tuyến' ?></button></div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Tuyến</th><th>Điểm đi - đến</th><th>Khoảng cách</th><th>Giá cơ bản</th><th>Trạng thái</th><th></th></tr></thead><tbody>
        <?php foreach ($routes as $route): ?><tr><td><strong><?= admin_e($route['ten_tuyen']) ?></strong></td><td><?= admin_e($route['diem_di'] . ' → ' . $route['diem_den']) ?></td><td><?= admin_e($route['khoang_cach_km'] === null ? '—' : $route['khoang_cach_km'] . ' km') ?></td><td><?= admin_e(admin_money($route['gia_ve_co_ban'])) ?></td><td><span class="<?= admin_e(admin_status_class($route['trang_thai'])) ?>"><?= admin_e(admin_status_label($route['trang_thai'])) ?></span></td><td><a class="button button-light button-small" href="fleet.php?edit_route=<?= (int) $route['ma_tuyen'] ?>#routes">Sửa</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<section class="card" id="vehicles">
    <div class="card-head"><h2><?= $editVehicle ? 'Sửa thông tin xe' : 'Thêm xe' ?></h2><?php if ($editVehicle): ?><a class="button button-light button-small" href="fleet.php#vehicles">Hủy sửa</a><?php endif; ?></div>
    <form method="post" action="fleet.php#vehicles" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="save_vehicle"><input type="hidden" name="id" value="<?= (int) ($editVehicle['ma_xe'] ?? 0) ?>">
        <div class="grid-3">
            <label>Biển số<input name="plate" maxlength="20" required value="<?= admin_e($editVehicle['bien_so'] ?? '') ?>"></label>
            <label>Tên xe<input name="name" maxlength="100" value="<?= admin_e($editVehicle['ten_xe'] ?? '') ?>"></label>
            <label>Loại xe<select name="type_id" required><option value="">Chọn loại xe</option><?php foreach ($vehicleTypes as $type): ?><option value="<?= (int) $type['ma_loai_xe'] ?>" <?= (int) ($editVehicle['ma_loai_xe'] ?? 0) === (int) $type['ma_loai_xe'] ? 'selected' : '' ?>><?= admin_e($type['ten_loai_xe']) ?></option><?php endforeach; ?></select></label>
            <label>Trạng thái<select name="status"><?php foreach (['hoat_dong', 'bao_tri', 'ngung_hoat_dong'] as $option): ?><option value="<?= admin_e($option) ?>" <?= ($editVehicle['trang_thai'] ?? 'hoat_dong') === $option ? 'selected' : '' ?>><?= admin_e(admin_status_label($option)) ?></option><?php endforeach; ?></select></label>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit"><?= $editVehicle ? 'Lưu xe' : 'Thêm xe' ?></button></div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Biển số / tên xe</th><th>Loại xe</th><th>Ghế</th><th>Trạng thái</th><th></th></tr></thead><tbody>
        <?php foreach ($vehicles as $vehicle): ?><tr><td><strong><?= admin_e($vehicle['bien_so']) ?></strong><br><?= admin_e($vehicle['ten_xe'] ?: 'Chưa đặt tên') ?></td><td><?= admin_e($vehicle['ten_loai_xe']) ?></td><td><?= (int) $vehicle['so_ghe'] ?> / <?= (int) $vehicle['suc_chua'] ?></td><td><span class="<?= admin_e(admin_status_class($vehicle['trang_thai'])) ?>"><?= admin_e(admin_status_label($vehicle['trang_thai'])) ?></span></td><td><a class="button button-light button-small" href="fleet.php?edit_vehicle=<?= (int) $vehicle['ma_xe'] ?>#vehicles">Sửa</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<section class="card" id="seats">
    <div class="card-head"><h2><?= $editSeat ? 'Sửa ghế xe' : 'Thêm ghế xe' ?></h2><?php if ($editSeat): ?><a class="button button-light button-small" href="fleet.php#seats">Hủy sửa</a><?php endif; ?></div>
    <form method="post" action="fleet.php#seats" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="save_seat"><input type="hidden" name="id" value="<?= (int) ($editSeat['ma_ghe'] ?? 0) ?>">
        <div class="grid-3">
            <label>Xe<select name="car_id" required><option value="">Chọn xe</option><?php foreach ($vehicles as $vehicle): ?><option value="<?= (int) $vehicle['ma_xe'] ?>" <?= (int) ($editSeat['ma_xe'] ?? 0) === (int) $vehicle['ma_xe'] ? 'selected' : '' ?>><?= admin_e($vehicle['bien_so'] . ' · ' . ($vehicle['ten_xe'] ?: 'Xe #' . $vehicle['ma_xe'])) ?></option><?php endforeach; ?></select></label>
            <label>Mã ghế<input name="seat_number" maxlength="10" required value="<?= admin_e($editSeat['so_ghe'] ?? '') ?>"></label>
            <label>Tầng<input type="number" min="1" max="9" name="floor" required value="<?= admin_e($editSeat['tang'] ?? 1) ?>"></label>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit"><?= $editSeat ? 'Lưu ghế' : 'Thêm ghế' ?></button></div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Xe</th><th>Mã ghế</th><th>Tầng</th><th></th></tr></thead><tbody>
        <?php foreach ($seats as $seat): ?><tr><td><strong><?= admin_e($seat['bien_so']) ?></strong> · <?= admin_e($seat['ten_xe'] ?: 'Xe #' . $seat['ma_xe']) ?></td><td><?= admin_e($seat['so_ghe']) ?></td><td><?= (int) $seat['tang'] ?></td><td class="inline-form"><a class="button button-light button-small" href="fleet.php?edit_seat=<?= (int) $seat['ma_ghe'] ?>#seats">Sửa</a><form method="post" action="fleet.php#seats" onsubmit="return confirm('Xóa ghế này? Ghế đã nằm trong vé sẽ không thể xóa.');"><input type="hidden" name="csrf_token" value="<?= admin_e(admin_csrf_token()) ?>"><input type="hidden" name="action" value="delete_seat"><input type="hidden" name="id" value="<?= (int) $seat['ma_ghe'] ?>"><button class="button button-danger button-small" type="submit">Xóa</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
