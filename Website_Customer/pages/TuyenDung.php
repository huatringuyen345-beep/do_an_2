<?php
require_once __DIR__ . '/../backend/includes/config.php';
customer_require_account('TuyenDung.php');
require_once __DIR__ . '/../backend/includes/data.php';
require_once __DIR__ . '/../backend/controllers/XuLyTuyenDung.php';

$pageTitle = 'Tuyển dụng';
$pageDesc  = 'Tài Nguyên tuyển lái xe, phụ xe, nhân viên phòng vé tại Bạc Liêu.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Tuyển dụng</h1>
        <p>Làm việc cùng Tài Nguyên tại Bạc Liêu.</p>
    </div>
</section>

<section class="section">
    <div class="wrap split">
        <div>
            <h2>Vị trí đang tuyển</h2>
            <div>
                <?php foreach ($jobs as $j): ?>
                    <details>
                        <summary>
                            <span><?= e($j['ten']) ?><span class="job-meta">Cần <?= (int)$j['sl'] ?> người, <?= e($j['noi']) ?>, lương <?= e($j['luong']) ?></span></span>
                        </summary>
                        <div class="body">
                            <p><?= e($j['mota']) ?></p>
                            <p style="margin-bottom:.4em"><strong>Yêu cầu</strong></p>
                            <ul class="tick">
                                <?php foreach ($j['yeu_cau'] as $y): ?><li><?= icon('check', 20) ?> <?= e($y) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel" id="ung-tuyen">
            <?php if ($sent): ?>
                <div class="alert alert-ok"><p><strong>Đã nhận hồ sơ.</strong> Chúng tôi sẽ gọi cho bạn qua số <?= e($sdt) ?> khi có lịch phỏng vấn.</p></div>
                <a class="btn btn-line" href="TuyenDung.php">Gửi hồ sơ khác</a>
            <?php else: ?>
                <h2 style="font-size:1.3rem">Nộp hồ sơ ứng tuyển</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-bad" role="alert"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" action="TuyenDung.php#ung-tuyen" novalidate>
                    <div class="form-grid">
                        <div class="field">
                            <label for="vi_tri">Vị trí ứng tuyển</label>
                            <select id="vi_tri" name="vi_tri">
                                <option value="">Chọn vị trí</option>
                                <?php foreach ($jobs as $j): ?>
                                    <option value="<?= e($j['ma']) ?>"<?= (($_POST['vi_tri'] ?? '') === $j['ma']) ? ' selected' : '' ?>><?= e($j['ten']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="ten">Họ và tên</label>
                            <input type="text" id="ten" name="ten" value="<?= old('ten') ?>" autocomplete="name">
                        </div>
                        <div class="row2">
                            <div class="field">
                                <label for="sdt">Số điện thoại</label>
                                <input type="tel" id="sdt" name="sdt" value="<?= old('sdt') ?>" autocomplete="tel">
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= old('email') ?>" autocomplete="email">
                            </div>
                        </div>
                        <div class="field">
                            <label for="gioi_thieu">Giới thiệu ngắn về bạn</label>
                            <textarea id="gioi_thieu" name="gioi_thieu"><?= old('gioi_thieu') ?></textarea>
                        </div>
                        <button class="btn btn-g" type="submit">Gửi hồ sơ</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
