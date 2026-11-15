<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('DatDichVu.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyDichVu.php';

$pageTitle = 'Đặt dịch vụ';
$pageDesc  = 'Đặt dịch vụ vận chuyển hàng hóa, trung chuyển tận nơi và các dịch vụ hỗ trợ của Tài Nguyên.';
include __DIR__ . '/../includes/header.php';

$serviceCatalog = [
    'van_chuyen_hang' => ['id' => 1, 'ten' => 'Vận chuyển hàng hóa', 'gia' => 50000, 'mota' => 'Gửi hàng theo tuyến, chỉ cần mang hàng đến bến hoặc yêu cầu đón tận nơi.'],
    'trung_chuyen' => ['id' => 2, 'ten' => 'Xe trung chuyển tận nơi', 'gia' => 30000, 'mota' => 'Xe mini đón khách tại nhà trước giờ xe chạy, phù hợp khách ở xa trung tâm.'],
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
<div class="wrap page-stack">
    <section class="page-head">
        <div class="wrap">
            <h1>Đặt dịch vụ</h1>
            <p>Gửi hàng nhanh hoặc đăng ký xe trung chuyển tận nơi trong khu vực Bạc Liêu.</p>
        </div>
    </section>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= e($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <form method="post" action="DatDichVu.php" class="card booking-card">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <div class="book-grid">
            <div class="book-main">
                <div class="book-sec">
                    <h2><span class="n">1</span> Chọn dịch vụ</h2>
                    <div class="service-choice-list">
                        <?php foreach ($serviceCatalog as $slug => $service): ?>
                            <label class="service-choice <?= $slug === $selectedService ? 'is-selected' : '' ?>">
                                <input type="radio" name="loai_dich_vu" value="<?= e($slug) ?>" <?= $slug === $selectedService ? 'checked' : '' ?>>
                                <span>
                                    <strong><?= e($service['ten']) ?></strong>
                                    <small><?= e($service['mota']) ?: 'Phù hợp cho nhu cầu vận chuyển của quý khách.' ?></small>
                                </span>
                                <em><?= e(money((int) $service['gia'])) ?> / đơn</em>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="book-sec">
                    <h2><span class="n">2</span> Thông tin khách hàng</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="ho_ten">Họ và tên</label>
                            <input type="text" id="ho_ten" name="ho_ten" value="<?= old('ho_ten', $account['name'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="so_dien_thoai">Số điện thoại</label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" value="<?= old('so_dien_thoai', $account['phone'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="ngay_su_dung">Ngày muốn sử dụng</label>
                            <input type="datetime-local" id="ngay_su_dung" name="ngay_su_dung" value="<?= old('ngay_su_dung', date('Y-m-d\TH:i')) ?>" required>
                        </div>
                        <div class="field">
                            <label for="so_luong">Số lượng</label>
                            <input type="number" id="so_luong" name="so_luong" min="1" max="20" value="<?= old('so_luong', '1') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="book-sec">
                    <h2><span class="n">3</span> Thông tin hàng hóa / đón xe</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="loai_hang">Loại hàng / mô tả</label>
                            <input type="text" id="loai_hang" name="loai_hang" value="<?= old('loai_hang') ?>" placeholder="Ví dụ: Thiết bị điện, thực phẩm, túi hành lý, ..." required>
                        </div>
                        <div class="field">
                            <label for="khoi_luong_kg">Khối lượng (kg)</label>
                            <input type="number" id="khoi_luong_kg" name="khoi_luong_kg" min="0.1" step="0.1" value="<?= old('khoi_luong_kg', '1') ?>" required>
                        </div>
                        <div class="field">
                            <label for="gia_tri_khai_bao">Giá trị khai báo (đ)</label>
                            <input type="number" id="gia_tri_khai_bao" name="gia_tri_khai_bao" min="0" step="1000" value="<?= old('gia_tri_khai_bao', '0') ?>">
                        </div>
                        <div class="field">
                            <label for="dia_chi_don">Địa chỉ lấy hàng / đón tại nhà</label>
                            <input type="text" id="dia_chi_don" name="dia_chi_don" value="<?= old('dia_chi_don', $account['address'] ?? '') ?>" placeholder="Số nhà, đường, phường/xã, tỉnh thành" required>
                        </div>
                        <div class="field">
                            <label for="ten_nguoi_nhan">Tên người nhận</label>
                            <input type="text" id="ten_nguoi_nhan" name="ten_nguoi_nhan" value="<?= old('ten_nguoi_nhan') ?>" placeholder="Bắt buộc cho vận chuyển hàng hóa">
                        </div>
                        <div class="field">
                            <label for="sdt_nguoi_nhan">Số điện thoại người nhận</label>
                            <input type="tel" id="sdt_nguoi_nhan" name="sdt_nguoi_nhan" value="<?= old('sdt_nguoi_nhan') ?>" placeholder="Bắt buộc cho vận chuyển hàng hóa">
                        </div>
                        <div class="field full-width">
                            <label for="dia_chi_nhan">Địa chỉ giao hàng / điểm đến</label>
                            <input type="text" id="dia_chi_nhan" name="dia_chi_nhan" value="<?= old('dia_chi_nhan') ?>" placeholder="Ví dụ: Bến xe Cà Mau, Nhà riêng, ...">
                        </div>
                        <div class="field full-width">
                            <label for="ghi_chu">Ghi chú</label>
                            <textarea id="ghi_chu" name="ghi_chu" rows="4" placeholder="Mô tả thêm về hàng hóa, thời điểm nhận, yêu cầu đặc biệt..."><?= old('ghi_chu') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="summary">
                <h2>Tóm tắt đơn</h2>
                <dl>
                    <div><dt>Dịch vụ</dt><dd><?= e($selectedServiceInfo['ten']) ?></dd></div>
                    <div><dt>Giá tham khảo</dt><dd><?= e(money((int) $selectedServiceInfo['gia'])) ?></dd></div>
                    <div><dt>Ngày hẹn</dt><dd><?= e(date('d/m/Y', strtotime($_POST['ngay_su_dung'] ?? 'now')) ?: 'Chưa chọn') ?></dd></div>
                    <div><dt>Khối lượng</dt><dd><?= e($_POST['khoi_luong_kg'] ?? '1') ?> kg</dd></div>
                </dl>
                <p class="total"><span>Ước tính</span><strong><?= e(money((int) ($selectedServiceInfo['gia'] * max(1, (int)($_POST['so_luong'] ?? 1))))) ?></strong></p>
                <button class="btn btn-g" type="submit">Gửi yêu cầu</button>
            </aside>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
