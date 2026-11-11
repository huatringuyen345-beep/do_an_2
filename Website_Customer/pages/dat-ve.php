<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('dat-ve.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyDatVe.php';

$pageTitle = 'Đặt vé online';
$pageDesc  = 'Đặt vé xe Tài Nguyên online: chọn tuyến, giờ chạy, chỗ ngồi và thanh toán.';
$extraScripts = $done ? [] : ['../assets/js/booking.js'];
include __DIR__ . '/../includes/header.php';

$jsRoutes = [];
foreach ($routes as $r) {
    $jsRoutes[$r['slug']] = [
        'ten' => $r['ten'],
        'ghe' => $r['ghe'],
        'giuong' => $r['giuong'],
        'gio' => $r['khoi_hanh'],
    ];
}
?>

<section class="page-head">
    <div class="wrap">
        <h1>Đặt vé online</h1>
        <p>Gửi yêu cầu đặt vé, chọn chỗ mong muốn và để nhân viên xác nhận tình trạng chỗ trước khi thanh toán.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?php if ($done): ?>
            <div class="ticket ticket-ok">
                <div class="ticket-head">
                    <p>Mã yêu cầu đặt vé</p>
                    <p class="code"><?= e($done['ma']) ?></p>
                </div>
                <div class="ticket-body">
                    <div class="alert alert-ok">
                        <p>Đã ghi nhận yêu cầu. Đây chưa phải vé đã xác nhận; nhân viên Tài Nguyên sẽ gọi số <strong><?= e($done['sdt']) ?></strong> để xác nhận chỗ và hướng dẫn thanh toán.</p>
                    </div>
                    <dl class="kv">
                        <div><dt>Tuyến</dt><dd><?= e($done['tuyen']) ?></dd></div>
                        <div><dt>Ngày giờ</dt><dd><?= date('d/m/Y', strtotime($done['ngay'])) ?>, <?= e($done['gio']) ?></dd></div>
                        <div><dt>Loại xe</dt><dd><?= e($done['loai']) ?></dd></div>
                        <div><dt>Chỗ mong muốn</dt><dd><?= e(implode(', ', $done['seats'])) ?></dd></div>
                        <div><dt>Hành khách</dt><dd><?= e($done['ten']) ?></dd></div>
                        <div><dt>Thanh toán mong muốn</dt><dd><?= e($done['tt']) ?></dd></div>
                        <?php if ($done['tc']): ?><div><dt>Đón tại</dt><dd><?= e($done['tc']) ?></dd></div><?php endif; ?>
                        <div><dt>Tổng tiền dự kiến</dt><dd><strong><?= money($done['tong']) ?></strong></dd></div>
                    </dl>
                    <a class="btn btn-g btn-block" href="dat-ve.php">Đặt thêm vé khác</a>
                    <?php if (!empty($account)): ?>
                        <a class="btn btn-y btn-block" href="tai-khoan.php?tab=ve">Xem danh sách vé trong tài khoản</a>
                    <?php endif; ?>
                    <a class="btn btn-line btn-block" href="index.php">Về trang chủ</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="alert alert-bad" role="alert">
                    <p><strong>Chưa đặt được vé. Vui lòng sửa các mục sau:</strong></p>
                    <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form id="bookForm" method="post" action="dat-ve.php" novalidate>
                <input type="hidden" name="sl" value="<?= (int)$want ?>">
                <div class="book-grid">
                    <div>
                        <div class="book-sec">
                            <h2><span class="n">1</span> Chọn chuyến</h2>
                            <div class="form-grid">
                                <div class="field">
                                    <span class="label" id="lbl-chieu">Chiều đi</span>
                                    <div class="seg" role="radiogroup" aria-labelledby="lbl-chieu">
                                        <label><input type="radio" name="chieu" value="di"<?= $chieu === 'di' ? ' checked' : '' ?>><span>Đi từ Bạc Liêu</span></label>
                                        <label><input type="radio" name="chieu" value="ve"<?= $chieu === 've' ? ' checked' : '' ?>><span>Về Bạc Liêu</span></label>
                                    </div>
                                </div>
                                <div class="row2">
                                    <div class="field">
                                        <label for="tuyen">Tuyến</label>
                                        <select id="tuyen" name="tuyen">
                                            <?php foreach ($routes as $r): ?>
                                                <option value="<?= e($r['slug']) ?>"<?= $r['slug'] === $route['slug'] ? ' selected' : '' ?>><?= e($r['ten']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label for="ngay">Ngày đi</label>
                                        <input type="date" id="ngay" name="ngay" value="<?= e($ngay) ?>" min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="row2">
                                    <div class="field">
                                        <label for="gio">Giờ khởi hành</label>
                                        <select id="gio" name="gio">
                                            <?php foreach ($route['khoi_hanh'] as $time): ?>
                                                <option value="<?= e($time) ?>"<?= $time === $gio ? ' selected' : '' ?>><?= e($time) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label for="loai">Loại xe</label>
                                        <select id="loai" name="loai">
                                            <option value="ghe"<?= empty($route['ghe']) ? ' disabled' : '' ?><?= $loai === 'ghe' ? ' selected' : '' ?>>Ghế ngồi</option>
                                            <option value="giuong"<?= empty($route['giuong']) ? ' disabled' : '' ?><?= $loai === 'giuong' ? ' selected' : '' ?>>Giường nằm</option>
                                        </select>
                                        <span class="hint" id="loaiHint"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="book-sec">
                            <h2><span class="n">2</span> Chọn chỗ</h2>
                            <p class="hint">Sơ đồ chỉ ghi nhận chỗ bạn mong muốn; tình trạng chỗ thực tế sẽ được nhân viên xác nhận.</p>
                            <div class="seat-legend">
                                <span><i></i> Chỗ có thể chọn</span>
                                <span><i class="i-pick"></i> Chỗ bạn đã chọn</span>
                            </div>
                            <div id="seatmap" class="seatmap" aria-live="polite"></div>
                            <input type="hidden" name="ghe" id="ghe" value="<?= e($seatsPosted) ?>">
                            <p class="field-error" id="seatError" role="alert" hidden>Vui lòng chọn ít nhất một chỗ.</p>
                            <p class="hint muted" style="margin:14px 0 0">Bấm trực tiếp vào ghế/giường còn trống để chọn chỗ.</p>
                        </div>

                        <div class="book-sec">
                            <h2><span class="n">3</span> Thông tin liên hệ</h2>
                            <div class="form-grid">
                                <div class="row2">
                                    <div class="field">
                                        <label for="ten">Họ và tên</label>
                                        <input type="text" id="ten" name="ten" value="<?= old('ten', $account['name'] ?? '') ?>" autocomplete="name" required>
                                    </div>
                                    <div class="field">
                                        <label for="sdt">Số điện thoại</label>
                                        <input type="tel" id="sdt" name="sdt" value="<?= old('sdt', $account['phone'] ?? '') ?>" autocomplete="tel" inputmode="tel" required>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="email">Email (để nhận mã vé điện tử)</label>
                                    <input type="email" id="email" name="email" value="<?= old('email', $account['email'] ?? '') ?>" autocomplete="email">
                                </div>
                                <fieldset class="field">
                                    <legend>Hình thức thanh toán mong muốn (chưa thu tiền)</legend>
                                    <div class="pay-list">
                                        <?php foreach ($payments as $key => $payment): ?>
                                            <label class="pay">
                                                <input type="radio" name="thanh_toan" value="<?= e($key) ?>"<?= (($_POST['thanh_toan'] ?? 'quay') === $key) ? ' checked' : '' ?> required>
                                                <span><?= icon($payment['icon'], 22) ?><strong><?= e($payment['ten']) ?></strong><small><?= e($payment['mota']) ?></small></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                                <div class="field">
                                    <label class="check">
                                        <input type="checkbox" id="trung_chuyen" name="trung_chuyen" value="1"<?= !empty($_POST['trung_chuyen']) ? ' checked' : '' ?>>
                                        <span>Đăng ký xe trung chuyển đón tại nhà</span>
                                    </label>
                                    <div id="shuttleBox" class="<?= !empty($_POST['trung_chuyen']) ? '' : 'hidden' ?>" style="margin-top: 8px;">
                                        <label for="dia_chi_don">Địa chỉ đón tận nơi</label>
                                        <input type="text" id="dia_chi_don" name="dia_chi_don" placeholder="Ví dụ: Số 123, đường Trần Phú, Bạc Liêu" value="<?= old('dia_chi_don', $account['address'] ?? '') ?>">
                                    </div>
                                </div>
                                <button class="btn btn-g" type="submit">Gửi yêu cầu đặt vé</button>
                            </div>
                        </div>
                    </div>

                    <aside class="summary">
                        <h2>Thông tin vé</h2>
                        <dl>
                            <div><dt>Tuyến</dt><dd id="sumRoute"></dd></div>
                            <div><dt>Ngày đi</dt><dd id="sumDate"></dd></div>
                            <div><dt>Giờ chạy</dt><dd id="sumTime"></dd></div>
                            <div><dt>Loại xe</dt><dd id="sumType"></dd></div>
                            <div><dt>Chỗ mong muốn</dt><dd id="sumSeats"></dd></div>
                            <div><dt>Giá mỗi chỗ</dt><dd id="sumPrice"></dd></div>
                        </dl>
                        <p class="total"><span>Tổng tiền dự kiến</span><strong id="sumTotal"></strong></p>
                    </aside>
                </div>
            </form>
            <script>
                window.TN_ROUTES = <?= json_encode($jsRoutes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                window.TN_INIT = {
                    seats: <?= json_encode($seatsPosted, JSON_UNESCAPED_UNICODE) ?>,
                    want: <?= (int) $want ?>
                };
            </script>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
