<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('TuyenDung.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyTuyenDung.php';

$pageTitle = 'Cơ hội nghề nghiệp & Tuyển dụng';
$pageDesc  = 'Gia nhập đội ngũ nhà xe Tài Nguyên: Tuyển dụng tài xế dấu D, E, phụ xe và nhân viên phòng vé tại Bạc Liêu.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Gia nhập đội ngũ Tài Nguyên</h1>
        <p>Môi trường làm việc thân thiện, đãi ngộ cạnh tranh, chế độ bảo hiểm và phúc lợi đầy đủ cho người lao động tại Bạc Liêu.</p>
    </div>
</section>

<section class="section">
    <div class="wrap split">
        <div>
            <h2>Các vị trí đang tuyển dụng</h2>
            <p class="muted" style="margin-bottom:20px;">Bấm vào từng vị trí để xem chi tiết mô tả công việc, mức lương và chế độ đãi ngộ.</p>
            <div>
                <?php foreach ($jobs as $j): ?>
                    <details>
                        <summary>
                            <span>
                                <?= e($j['ten']) ?>
                                <span class="job-meta">
                                    <?= icon('users', 14) ?> Cần <?= (int)$j['sl'] ?> nhân sự · 
                                    <?= icon('pin', 14) ?> <?= e($j['noi']) ?> · 
                                    <strong><?= e($j['luong']) ?></strong>
                                </span>
                            </span>
                        </summary>
                        <div class="body">
                            <p><strong>Mô tả công việc:</strong> <?= e($j['mota']) ?></p>
                            <p style="margin-bottom:8px"><strong>Yêu cầu ứng viên:</strong></p>
                            <ul class="tick">
                                <?php foreach ($j['yeu_cau'] as $y): ?>
                                    <li><?= icon('check-circle', 18) ?> <span><?= e($y) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel" id="ung-tuyen">
            <?php if ($daGui): ?>
                <div class="alert alert-ok">
                    <p style="font-weight:700; display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <?= icon('check-circle', 20) ?> <span>Hồ sơ đã được gửi thành công!</span>
                    </p>
                    <p style="font-size:0.92rem;">
                        Bộ phận nhân sự Tài Nguyên đã tiếp nhận thông tin và sẽ liên hệ trực tiếp số điện thoại 
                        <strong><?= e($_POST['sdt'] ?? '') ?></strong> để hẹn lịch phỏng vấn.
                    </p>
                </div>
                <a class="btn btn-line btn-block" href="TuyenDung.php">Gửi thêm hồ sơ khác</a>
            <?php else: ?>
                <h2 style="font-size:1.35rem; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                    <?= icon('edit', 20) ?> <span>Nộp hồ sơ trực tuyến</span>
                </h2>
                <p class="hint" style="margin-bottom:18px;">Điền thông tin bên dưới, nhà xe sẽ phản hồi sớm nhất.</p>

                <?php if ($loi): ?>
                    <div class="alert alert-bad" role="alert">
                        <p style="font-weight:700; margin-bottom:6px;">Vui lòng kiểm tra lại:</p>
                        <ul>
                            <?php foreach ($loi as $loiChiTiet): ?>
                                <li><?= e($loiChiTiet) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="TuyenDung.php#ung-tuyen" novalidate>
                    <div class="form-grid">
                        <div class="field">
                            <label for="vi_tri">
                                <?= icon('tag', 16) ?> Vị trí muốn ứng tuyển <span class="required">*</span>
                            </label>
                            <select id="vi_tri" name="vi_tri" required>
                                <option value="">-- Chọn vị trí ứng tuyển --</option>
                                <?php foreach ($jobs as $j): ?>
                                    <option value="<?= e($j['ma']) ?>"<?= (($_POST['vi_tri'] ?? '') === $j['ma']) ? ' selected' : '' ?>>
                                        <?= e($j['ten']) ?> (<?= e($j['luong']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="ten">
                                <?= icon('user', 16) ?> Họ và tên của bạn <span class="required">*</span>
                            </label>
                            <input type="text" id="ten" name="ten" placeholder="Ví dụ: Trần Văn C" value="<?= old('ten') ?>" autocomplete="name" required>
                        </div>

                        <div class="row2">
                            <div class="field">
                                <label for="sdt">
                                    <?= icon('phone', 16) ?> Số điện thoại <span class="required">*</span>
                                </label>
                                <input type="tel" id="sdt" name="sdt" placeholder="0912345678" value="<?= old('sdt') ?>" autocomplete="tel" required>
                            </div>
                            <div class="field">
                                <label for="email">
                                    <?= icon('mail', 16) ?> Địa chỉ email
                                </label>
                                <input type="email" id="email" name="email" placeholder="email@example.com" value="<?= old('email') ?>" autocomplete="email">
                            </div>
                        </div>

                        <div class="field">
                            <label for="gioi_thieu">
                                <?= icon('edit', 16) ?> Kinh nghiệm & Giới thiệu bản thân
                            </label>
                            <textarea id="gioi_thieu" name="gioi_thieu" rows="4" placeholder="Nêu rõ loại bằng lái (nếu ứng tuyển tài xế), số năm kinh nghiệm lái xe hoặc các công việc liên quan..."><?= old('gioi_thieu') ?></textarea>
                        </div>

                        <button class="btn btn-y btn-lg btn-block" type="submit">
                            <?= icon('check', 18) ?> <span>Nộp hồ sơ ứng tuyển</span>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
