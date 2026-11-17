<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('DatDichVu.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyDichVu.php';

$pageTitle = 'Đặt dịch vụ vận chuyển & trung chuyển';
$pageDesc  = 'Đặt dịch vụ vận chuyển hàng hóa, trung chuyển tận nơi và các dịch vụ hỗ trợ của Tài Nguyên.';
include __DIR__ . '/../includes/header.php';

$serviceCatalog = [
    'van_chuyen_hang' => ['id' => 1, 'ten' => 'Vận chuyển hàng hóa', 'gia' => 50000, 'mota' => 'Gửi hàng theo tuyến xe khách, nhận hàng tại văn phòng hoặc giao nhận tận nơi.'],
    'trung_chuyen' => ['id' => 2, 'ten' => 'Xe trung chuyển tận nơi', 'gia' => 30000, 'mota' => 'Xe trung chuyển đón và trả khách tại nhà trước giờ khởi hành trong khu vực Bạc Liêu.'],
];

if (isset($conn) && $conn instanceof mysqli) {
    try {
        $stmt = $conn->prepare('SELECT ma_dich_vu, ten_dich_vu, loai_dich_vu, gia_tham_khao, mo_ta FROM dich_vu WHERE trang_thai = "hoat_dong" ORDER BY ma_dich_vu');
        $stmt->execute();
        $ketQua = $stmt->get_result();
        while ($dong = $ketQua->fetch_assoc()) {
            $slug = $dong['loai_dich_vu'] === 'trung_chuyen' ? 'trung_chuyen' : 'van_chuyen_hang';
            $serviceCatalog[$slug] = [
                'id' => (int) $dong['ma_dich_vu'],
                'ten' => $dong['ten_dich_vu'],
                'gia' => (float) ($dong['gia_tham_khao'] ?? 0),
                'mota' => $dong['mo_ta'] ?? '',
            ];
        }
        $stmt->close();
    } catch (Throwable $loi) {
        error_log('Loi tai danh sach dich vu: ' . $loi->getMessage());
    }
}

$selectedService = $_POST['loai_dich_vu'] ?? ($_GET['service'] ?? 'van_chuyen_hang');
$selectedService = array_key_exists($selectedService, $serviceCatalog) ? $selectedService : 'van_chuyen_hang';
$selectedServiceInfo = $serviceCatalog[$selectedService];
?>

<section class="page-head">
    <div class="wrap">
        <h1>Đặt dịch vụ vận chuyển</h1>
        <p>Gửi hàng hóa theo xe hoặc đăng ký xe trung chuyển đón trả tận nơi tại Bạc Liêu và các tuyến liên tỉnh.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?php if (!empty($thongBaoThanhCong)): ?>
            <div class="alert alert-ok" role="alert">
                <p style="font-weight:700; display:flex; align-items:center; gap:8px;">
                    <?= icon('check-circle', 20) ?> <span><?= e($thongBaoThanhCong) ?></span>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($loi): ?>
            <div class="alert alert-bad" role="alert">
                <p style="font-weight:700; display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <?= icon('alert-circle', 20) ?> <span>Vui lòng kiểm tra lại thông tin:</span>
                </p>
                <ul>
                    <?php foreach ($loi as $loiChiTiet): ?>
                        <li><?= e($loiChiTiet) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="DatDichVu.php" class="booking-card" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

            <div class="book-grid">
                <div class="book-main">
                    <!-- BƯỚC 1: CHỌN DỊCH VỤ -->
                    <div class="book-sec">
                        <h2><span class="n">1</span> Chọn loại dịch vụ cần sử dụng</h2>
                        <div class="service-choice-list">
                            <?php foreach ($serviceCatalog as $slug => $service): ?>
                                <label class="service-choice <?= $slug === $selectedService ? 'is-selected' : '' ?>">
                                    <input type="radio" name="loai_dich_vu" value="<?= e($slug) ?>" <?= $slug === $selectedService ? 'checked' : '' ?>>
                                    <span>
                                        <strong>
                                            <?= $slug === 'van_chuyen_hang' ? icon('box', 20) : icon('car', 20) ?>
                                            <?= e($service['ten']) ?>
                                        </strong>
                                        <small><?= e($service['mota']) ?: 'Phù hợp cho nhu cầu vận chuyển của quý khách.' ?></small>
                                    </span>
                                    <em><?= e(money((int) $service['gia'])) ?></em>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- BƯỚC 2: THÔNG TIN KHÁCH HÀNG -->
                    <div class="book-sec">
                        <h2><span class="n">2</span> Thông tin người liên hệ / Người gửi</h2>
                        <div class="form-grid">
                            <div class="row2">
                                <div class="field">
                                    <label for="ho_ten">
                                        <?= icon('user', 17) ?> Họ và tên người gửi <span class="required">*</span>
                                    </label>
                                    <input type="text" id="ho_ten" name="ho_ten" placeholder="Ví dụ: Nguyễn Văn A" value="<?= old('ho_ten', $taiKhoan['name'] ?? '') ?>" required>
                                </div>
                                <div class="field">
                                    <label for="so_dien_thoai">
                                        <?= icon('phone', 17) ?> Số điện thoại liên hệ <span class="required">*</span>
                                    </label>
                                    <input type="tel" id="so_dien_thoai" name="so_dien_thoai" placeholder="Ví dụ: 0912345678" value="<?= old('so_dien_thoai', $taiKhoan['phone'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="row2">
                                <div class="field">
                                    <label for="ngay_su_dung">
                                        <?= icon('calendar', 17) ?> Thời gian gửi / Đón xe <span class="required">*</span>
                                    </label>
                                    <input type="datetime-local" id="ngay_su_dung" name="ngay_su_dung" value="<?= old('ngay_su_dung', date('Y-m-d\TH:i')) ?>" required>
                                </div>
                                <div class="field">
                                    <label for="so_luong">
                                        <?= icon('tag', 17) ?> Số lượng (kiện hàng / số khách) <span class="required">*</span>
                                    </label>
                                    <input type="number" id="so_luong" name="so_luong" min="1" max="20" value="<?= old('so_luong', '1') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BƯỚC 3: THÔNG TIN HÀNG HÓA & ĐỊA CHỈ -->
                    <div class="book-sec">
                        <h2><span class="n">3</span> Thông tin hàng hóa & Điểm giao nhận</h2>
                        <div class="form-grid">
                            <div class="row2">
                                <div class="field">
                                    <label for="loai_hang">
                                        <?= icon('box', 17) ?> Loại hàng hóa / Mô tả <span class="required">*</span>
                                    </label>
                                    <input type="text" id="loai_hang" name="loai_hang" value="<?= old('loai_hang') ?>" placeholder="Ví dụ: Bưu phẩm, thực phẩm khô, quần áo, thiết bị..." required>
                                </div>
                                <div class="field">
                                    <label for="khoi_luong_kg">
                                        <?= icon('tag', 17) ?> Khối lượng ước tính (kg) <span class="required">*</span>
                                    </label>
                                    <input type="number" id="khoi_luong_kg" name="khoi_luong_kg" min="0.1" step="0.1" value="<?= old('khoi_luong_kg', '1') ?>" required>
                                </div>
                            </div>

                            <div class="field">
                                <label for="gia_tri_khai_bao">
                                    <?= icon('shield', 17) ?> Giá trị khai báo hàng hóa (đ) - Dùng để bảo hiểm hàng
                                </label>
                                <input type="number" id="gia_tri_khai_bao" name="gia_tri_khai_bao" min="0" step="1000" placeholder="0" value="<?= old('gia_tri_khai_bao', '0') ?>">
                            </div>

                            <div class="field">
                                <label for="dia_chi_don">
                                    <?= icon('pin', 17) ?> Địa chỉ nhận hàng / Điểm đón khách <span class="required">*</span>
                                </label>
                                <input type="text" id="dia_chi_don" name="dia_chi_don" value="<?= old('dia_chi_don', $taiKhoan['address'] ?? '') ?>" placeholder="Số nhà, tên đường, phường/xã, TP. Bạc Liêu" required>
                            </div>

                            <div class="row2">
                                <div class="field">
                                    <label for="ten_nguoi_nhan">
                                        <?= icon('user', 17) ?> Họ tên người nhận hàng
                                    </label>
                                    <input type="text" id="ten_nguoi_nhan" name="ten_nguoi_nhan" value="<?= old('ten_nguoi_nhan') ?>" placeholder="Bắt buộc nếu gửi hàng">
                                </div>
                                <div class="field">
                                    <label for="sdt_nguoi_nhan">
                                        <?= icon('phone', 17) ?> Số điện thoại người nhận
                                    </label>
                                    <input type="tel" id="sdt_nguoi_nhan" name="sdt_nguoi_nhan" value="<?= old('sdt_nguoi_nhan') ?>" placeholder="Bắt buộc nếu gửi hàng">
                                </div>
                            </div>

                            <div class="field">
                                <label for="dia_chi_nhan">
                                    <?= icon('pin', 17) ?> Địa chỉ giao hàng / Bến đến nhận
                                </label>
                                <input type="text" id="dia_chi_nhan" name="dia_chi_nhan" value="<?= old('dia_chi_nhan') ?>" placeholder="Ví dụ: Bến xe Miền Tây (TP.HCM), địa chỉ nhà riêng...">
                            </div>

                            <div class="field">
                                <label for="ghi_chu">
                                    <?= icon('edit', 17) ?> Ghi chú thêm cho tài xế
                                </label>
                                <textarea id="ghi_chu" name="ghi_chu" rows="3" placeholder="Yêu cầu bốc dỡ, lưu ý bảo quản dễ vỡ, thời gian hẹn cụ thể..."><?= old('ghi_chu') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR TÓM TẮT ĐƠN DỊCH VỤ -->
                <aside class="summary">
                    <h2>
                        <span>Tóm tắt dịch vụ</span>
                        <span class="user-role-badge badge-nhan_vien" style="font-size:0.75rem;">Yêu cầu</span>
                    </h2>
                    <dl>
                        <div><dt>Dịch vụ</dt><dd><?= e($selectedServiceInfo['ten']) ?></dd></div>
                        <div><dt>Đơn giá</dt><dd><?= e(money((int) $selectedServiceInfo['gia'])) ?></dd></div>
                        <div><dt>Thời gian</dt><dd><?= e(date('d/m/Y', strtotime($_POST['ngay_su_dung'] ?? 'now')) ?: 'Hôm nay') ?></dd></div>
                        <div><dt>Khối lượng</dt><dd><?= e($_POST['khoi_luong_kg'] ?? '1') ?> kg</dd></div>
                    </dl>
                    <div class="total">
                        <span>Chi phí ước tính:</span>
                        <strong><?= e(money((int) ($selectedServiceInfo['gia'] * max(1, (int)($_POST['so_luong'] ?? 1))))) ?></strong>
                    </div>

                    <button class="btn btn-y btn-block" type="submit" style="margin-bottom:12px;">
                        <?= icon('check', 18) ?> <span>Gửi yêu cầu dịch vụ</span>
                    </button>

                    <ul class="summary-features">
                        <li><?= icon('shield-check', 16) ?> <span>Bảo hiểm hàng hóa an toàn</span></li>
                        <li><?= icon('clock', 16) ?> <span>Giao nhận đúng hẹn trong ngày</span></li>
                        <li><?= icon('phone', 16) ?> <span>Nhân viên gọi lại báo giá chính xác</span></li>
                    </ul>
                </aside>
            </div>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
