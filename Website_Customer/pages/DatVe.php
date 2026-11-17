<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('DatVe.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyDatVe.php';

$pageTitle = 'Đặt vé online';
$pageDesc  = 'Đặt vé xe Tài Nguyên online: chọn tuyến, giờ chạy, chỗ ngồi và thanh toán.';
$extraScripts = $ketQuaDatVe ? [] : ['../assets/js/booking.js'];
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
        <h1>Đặt vé trực tuyến</h1>
        <p>Chọn tuyến xe, giờ khởi hành, sơ đồ chỗ ngồi mong muốn và nhận xác nhận nhanh chóng từ đội ngũ Tài Nguyên.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?php if ($ketQuaDatVe): ?>
            <!-- ================= MÀN HÌNH VÉ ĐÃ ĐẶT THÀNH CÔNG ================= -->
            <div class="ticket ticket-ok">
                <div class="ticket-head">
                    <span class="user-role-badge badge-nhan_vien" style="font-size:0.82rem; margin-bottom:8px;">Yêu cầu đặt vé trực tuyến</span>
                    <p style="margin:4px 0 2px;">Mã yêu cầu giữ chỗ</p>
                    <p class="code"><?= e($ketQuaDatVe['ma']) ?></p>
                </div>
                <div class="ticket-body">
                    <div class="alert alert-ok" style="margin-bottom:18px;">
                        <p style="font-weight:600; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <?= icon('check-circle', 20) ?> <span>Đã ghi nhận yêu cầu đặt vé thành công!</span>
                        </p>
                        <p style="font-size:0.92rem;">
                            Nhân viên nhà xe Tài Nguyên sẽ liên hệ trực tiếp số điện thoại <strong><?= e($ketQuaDatVe['sdt']) ?></strong> để xác nhận tình trạng chỗ và hướng dẫn thanh toán chi tiết.
                        </p>
                    </div>

                    <dl class="kv">
                        <div><dt><?= icon('bus', 18) ?> Tuyến đường</dt><dd><strong><?= e($ketQuaDatVe['tuyen']) ?></strong></dd></div>
                        <div><dt><?= icon('calendar', 18) ?> Ngày giờ chạy</dt><dd><?= date('d/m/Y', strtotime($ketQuaDatVe['ngay'])) ?> · <?= e($ketQuaDatVe['gio']) ?></dd></div>
                        <div><dt><?= icon('tag', 18) ?> Loại phương tiện</dt><dd><?= e($ketQuaDatVe['loai']) ?></dd></div>
                        <div><dt><?= icon('ticket', 18) ?> Chỗ đã chọn</dt><dd><span class="tag tag-khuyen-mai" style="font-size:0.95rem;"><?= e(implode(', ', $ketQuaDatVe['seats'])) ?></span></dd></div>
                        <div><dt><?= icon('user', 18) ?> Hành khách</dt><dd><?= e($ketQuaDatVe['ten']) ?></dd></div>
                        <div><dt><?= icon('card', 18) ?> Hình thức thanh toán</dt><dd><?= e($ketQuaDatVe['tt']) ?></dd></div>
                        <?php if ($ketQuaDatVe['tc']): ?>
                            <div><dt><?= icon('pin', 18) ?> Đón tận nhà</dt><dd><?= e($ketQuaDatVe['tc']) ?></dd></div>
                        <?php endif; ?>
                        <div style="border-top:1.5px solid var(--color-border); padding-top:12px; margin-top:6px;">
                            <dt style="font-size:1.05rem; color:var(--color-text); font-weight:700;">Tổng tiền dự kiến</dt>
                            <dd style="font-size:1.35rem; color:var(--color-primary); font-weight:800;"><?= money($ketQuaDatVe['tong']) ?></dd>
                        </div>
                    </dl>

                    <div style="display:grid; gap:10px; margin-top:10px;">
                        <?php if (!empty($taiKhoan)): ?>
                            <a class="btn btn-y btn-block" href="ThanhToan.php?order=<?= e($ketQuaDatVe['ma']) ?>">
                                <?= icon('wallet', 18) ?> <span>Tiến hành thanh toán ngay</span>
                            </a>
                            <a class="btn btn-line btn-block" href="TaiKhoan.php?tab=ve">
                                <?= icon('ticket', 18) ?> <span>Xem danh sách vé trong tài khoản</span>
                            </a>
                        <?php endif; ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <a class="btn btn-g" href="DatVe.php">
                                <?= icon('edit', 16) ?> <span>Đặt thêm vé khác</span>
                            </a>
                            <a class="btn btn-line" href="Index.php">
                                <?= icon('arrow-right', 16) ?> <span>Về trang chủ</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- ================= FORM ĐẶT VÉ CHÍNH ================= -->
            <?php if ($loi): ?>
                <div class="alert alert-bad" role="alert">
                    <p style="font-weight:700; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                        <?= icon('alert-circle', 20) ?> <span>Chưa đặt được vé. Vui lòng kiểm tra lại:</span>
                    </p>
                    <ul>
                        <?php foreach ($loi as $loiChiTiet): ?>
                            <li><?= e($loiChiTiet) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="bookForm" method="post" action="DatVe.php" novalidate>
                <input type="hidden" name="sl" value="<?= (int)$soLuongMuonDat ?>">

                <div class="book-grid">
                    <div>
                        <!-- BƯỚC 1: CHỌN CHUYẾN XE -->
                        <div class="book-sec">
                            <h2><span class="n">1</span> Chọn chuyến xe khởi hành</h2>
                            <div class="form-grid">
                                <div class="field">
                                    <span class="label" id="lbl-chieu">
                                        <?= icon('arrow-left-right', 18) ?> Chiều di chuyển
                                    </span>
                                    <div class="seg" role="radiogroup" aria-labelledby="lbl-chieu">
                                        <label>
                                            <input type="radio" name="chieu" value="di"<?= $chieu === 'di' ? ' checked' : '' ?>>
                                            <span>Đi từ Bạc Liêu</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="chieu" value="ve"<?= $chieu === 've' ? ' checked' : '' ?>>
                                            <span>Về lại Bạc Liêu</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row2">
                                    <div class="field">
                                        <label for="tuyen">
                                            <?= icon('pin', 17) ?> Tuyến đường <span class="required">*</span>
                                        </label>
                                        <select id="tuyen" name="tuyen">
                                            <?php foreach ($routes as $r): ?>
                                                <option value="<?= e($r['slug']) ?>"<?= $r['slug'] === $tuyenHienTai['slug'] ? ' selected' : '' ?>>
                                                    <?= e($r['ten']) ?> (<?= (int)$r['km'] ?> km)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label for="ngay">
                                            <?= icon('calendar', 17) ?> Ngày khởi hành <span class="required">*</span>
                                        </label>
                                        <input type="date" id="ngay" name="ngay" value="<?= e($ngay) ?>" min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="row2">
                                    <div class="field">
                                        <label for="gio">
                                            <?= icon('clock', 17) ?> Giờ khởi hành <span class="required">*</span>
                                        </label>
                                        <select id="gio" name="gio">
                                            <?php foreach ($tuyenHienTai['khoi_hanh'] as $time): ?>
                                                <option value="<?= e($time) ?>"<?= $time === $gio ? ' selected' : '' ?>><?= e($time) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label for="loai">
                                            <?= icon('bus', 17) ?> Loại phương tiện <span class="required">*</span>
                                        </label>
                                        <select id="loai" name="loai">
                                            <option value="ghe"<?= empty($tuyenHienTai['ghe']) ? ' disabled' : '' ?><?= $loai === 'ghe' ? ' selected' : '' ?>>Ghế ngồi tiện nghi</option>
                                            <option value="giuong"<?= empty($tuyenHienTai['giuong']) ? ' disabled' : '' ?><?= $loai === 'giuong' ? ' selected' : '' ?>>Giường nằm cao cấp</option>
                                        </select>
                                        <span class="hint" id="loaiHint" style="color:var(--color-primary);"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BƯỚC 2: CHỌN VỊ TRÍ CHỖ NGỒI / GIƯỜNG NẰM -->
                        <div class="book-sec">
                            <h2><span class="n">2</span> Chọn vị trí chỗ mong muốn</h2>
                            <p class="hint" style="margin-top:-10px; margin-bottom:12px;">
                                Bấm trực tiếp vào ghế/giường còn trống để chọn chỗ. Sơ đồ mô phỏng giúp bạn chọn vị trí ưng ý nhất.
                            </p>

                            <div class="seat-legend">
                                <span><i></i> Chỗ còn trống</span>
                                <span><i class="i-pick"></i> Chỗ bạn đang chọn</span>
                                <span><i class="i-taken"></i> Chỗ đã có người đặt</span>
                            </div>

                            <div id="seatmap" class="seatmap" aria-live="polite"></div>
                            <input type="hidden" name="ghe" id="ghe" value="<?= e($seatsPosted) ?>">
                            <p class="field-error" id="seatError" role="alert" hidden style="margin-top:12px;">
                                <?= icon('alert-circle', 18) ?> Vui lòng chọn ít nhất một chỗ.
                            </p>
                        </div>

                        <!-- BƯỚC 3: THÔNG TIN HÀNH KHÁCH & THANH TOÁN -->
                        <div class="book-sec">
                            <h2><span class="n">3</span> Thông tin hành khách & Thanh toán</h2>
                            <div class="form-grid">
                                <div class="row2">
                                    <div class="field">
                                        <label for="ten">
                                            <?= icon('user', 17) ?> Họ và tên hành khách <span class="required">*</span>
                                        </label>
                                        <input type="text" id="ten" name="ten" placeholder="Ví dụ: Nguyễn Văn A" value="<?= old('ten', $taiKhoan['name'] ?? '') ?>" autocomplete="name" required>
                                    </div>
                                    <div class="field">
                                        <label for="sdt">
                                            <?= icon('phone', 17) ?> Số điện thoại nhận tin <span class="required">*</span>
                                        </label>
                                        <input type="tel" id="sdt" name="sdt" placeholder="Ví dụ: 0912345678" value="<?= old('sdt', $taiKhoan['phone'] ?? '') ?>" autocomplete="tel" inputmode="tel" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="email">
                                        <?= icon('mail', 17) ?> Email (nhận mã vé điện tử & thông báo hành trình)
                                    </label>
                                    <input type="email" id="email" name="email" placeholder="email@example.com" value="<?= old('email', $taiKhoan['email'] ?? '') ?>" autocomplete="email">
                                </div>

                                <fieldset class="field" style="border:0; padding:0; margin:8px 0 0;">
                                    <legend style="font-weight:700; font-size:0.95rem; margin-bottom:8px; color:var(--color-text);">
                                        <?= icon('card', 18) ?> Hình thức thanh toán mong muốn
                                    </legend>
                                    <div class="pay-list">
                                        <?php foreach ($payments as $key => $payment): ?>
                                            <label class="pay">
                                                <input type="radio" name="thanh_toan" value="<?= e($key) ?>"<?= (($_POST['thanh_toan'] ?? 'quay') === $key) ? ' checked' : '' ?> required>
                                                <span class="icon"><?= icon($payment['icon'], 24) ?></span>
                                                <span>
                                                    <strong><?= e($payment['ten']) ?></strong>
                                                    <small><?= e($payment['mota']) ?></small>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>

                                <!-- Dịch vụ trung chuyển đón tận nhà -->
                                <div class="field" style="background:var(--color-surface-soft); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--color-border); margin-top:8px;">
                                    <label class="check">
                                        <input type="checkbox" id="trung_chuyen" name="trung_chuyen" value="1"<?= !empty($_POST['trung_chuyen']) ? ' checked' : '' ?>>
                                        <span style="font-weight:600; color:var(--color-text);">
                                            <?= icon('car', 18) ?> Đăng ký xe trung chuyển đón tận nơi tại Bạc Liêu (Miễn phí)
                                        </span>
                                    </label>
                                    <div id="shuttleBox" class="<?= !empty($_POST['trung_chuyen']) ? '' : 'hidden' ?>" style="margin-top: 14px;">
                                        <label for="dia_chi_don" style="font-size:0.9rem; font-weight:600; margin-bottom:6px; display:block;">
                                            Địa chỉ đón cụ thể (Số nhà, đường, phường/xã)
                                        </label>
                                        <input type="text" id="dia_chi_don" name="dia_chi_don" placeholder="Ví dụ: Số 123 đường Trần Phú, Phường 7, TP. Bạc Liêu" value="<?= old('dia_chi_don', $taiKhoan['address'] ?? '') ?>">
                                    </div>
                                </div>

                                <div style="margin-top:10px;">
                                    <button class="btn btn-y btn-lg btn-block" type="submit">
                                        <?= icon('ticket', 20) ?> <span>Gửi yêu cầu đặt vé ngay</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ASIDE: TÓM TẮT THÔNG TIN VÉ (STICKY) -->
                    <aside class="summary">
                        <h2>
                            <span>Thông tin vé xe</span>
                            <span class="user-role-badge badge-nhan_vien" style="font-size:0.75rem;">Trực tuyến</span>
                        </h2>

                        <dl>
                            <div><dt>Tuyến</dt><dd id="sumRoute">—</dd></div>
                            <div><dt>Ngày đi</dt><dd id="sumDate">—</dd></div>
                            <div><dt>Giờ chạy</dt><dd id="sumTime">—</dd></div>
                            <div><dt>Loại xe</dt><dd id="sumType">—</dd></div>
                            <div><dt>Chỗ chọn</dt><dd id="sumSeats">—</dd></div>
                            <div><dt>Đơn giá</dt><dd id="sumPrice">—</dd></div>
                        </dl>

                        <div class="total">
                            <span>Tổng tạm tính:</span>
                            <strong id="sumTotal">0đ</strong>
                        </div>

                        <button class="btn btn-y btn-block" type="submit" style="margin-bottom:12px;">
                            <?= icon('check', 18) ?> <span>Xác nhận đặt vé</span>
                        </button>

                        <ul class="summary-features">
                            <li><?= icon('shield-check', 16) ?> <span>Cam kết giữ đúng vị trí chỗ</span></li>
                            <li><?= icon('phone', 16) ?> <span>Nhà xe xác nhận nhanh chóng</span></li>
                            <li><?= icon('clock', 16) ?> <span>Hỗ trợ đổi chuyến trước 24h</span></li>
                        </ul>
                    </aside>
                </div>
            </form>

            <script>
                window.TN_ROUTES = <?= json_encode($jsRoutes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                window.TN_INIT = {
                    seats: <?= json_encode($seatsPosted, JSON_UNESCAPED_UNICODE) ?>,
                    want: <?= (int) $soLuongMuonDat ?>
                };
            </script>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
