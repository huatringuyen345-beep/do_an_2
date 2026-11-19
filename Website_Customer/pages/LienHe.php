<?php
require_once __DIR__ . '/../backend/bootstrap.php';
require_once __DIR__ . '/../backend/data/loader.php';
require_once __DIR__ . '/../backend/controllers/LienHeController.php';

$pageTitle = 'Liên hệ';
$pageDesc  = 'Liên hệ nhà xe Tài Nguyên tại Bạc Liêu: địa chỉ, số điện thoại, email và form gửi tin nhắn.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Liên hệ & Góp ý</h1>
        <p>Gọi điện trực tiếp hotline, ghé văn phòng bến xe hoặc để lại tin nhắn phản hồi cho nhà xe Tài Nguyên.</p>
    </div>
</section>

<section class="section">
    <div class="wrap contact-grid">
        <div>
            <h2>Thông tin liên hệ trực tiếp</h2>
            <p class="muted" style="margin-bottom:24px;">Chúng tôi luôn sẵn sàng lắng nghe ý kiến phản hồi và tư vấn lịch trình tốt nhất cho quý khách.</p>

            <ul class="contact-list">
                <li>
                    <?= icon('pin', 24) ?>
                    <div>
                        <strong>Địa chỉ văn phòng chính</strong>
                        <?= e($company['dia_chi']) ?>
                    </div>
                </li>
                <li>
                    <?= icon('phone', 24) ?>
                    <div>
                        <strong>Tổng đài đặt vé & giải đáp</strong>
                        <a href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= e($company['hotline']) ?></a> ·
                        <a href="tel:<?= preg_replace('/\D/', '', $company['hotline2']) ?>"><?= e($company['hotline2']) ?></a>
                        <small style="display:block; color:var(--color-text-muted);">(Hỗ trợ từ 06:00 đến 22:00 hàng ngày)</small>
                    </div>
                </li>
                <li>
                    <?= icon('mail', 24) ?>
                    <div>
                        <strong>Hộp thư điện tử</strong>
                        <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a>
                    </div>
                </li>
                <li>
                    <?= icon('clock', 24) ?>
                    <div>
                        <strong>Thời gian phục vụ</strong>
                        <?= e($company['gio_mo']) ?>
                    </div>
                </li>
            </ul>

            <iframe
                class="map"
                title="Bản đồ Bạc Liêu"
                loading="lazy"
                src="https://www.google.com/maps?q=B%E1%BA%A1c+Li%C3%AAu&amp;output=embed"
            ></iframe>
        </div>

        <div class="panel" id="gui-tin">
            <?php if ($daGui): ?>
                <div class="alert alert-ok">
                    <p style="font-weight:700; display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <?= icon('check-circle', 20) ?> <span>Đã gửi tin nhắn thành công!</span>
                    </p>
                    <p style="font-size:0.92rem;">
                        Cảm ơn bạn đã liên hệ. Đội ngũ chăm sóc khách hàng Tài Nguyên sẽ gọi lại qua số
                        <strong><?= e($_POST['sdt'] ?? '') ?></strong> trong thời gian sớm nhất.
                    </p>
                </div>
                <a class="btn btn-line btn-block" href="LienHe.php">Gửi thêm tin nhắn khác</a>
            <?php else: ?>
                <h2 class="contact-form-title" style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                    <?= icon('mail', 22) ?> <span>Gửi tin nhắn cho chúng tôi</span>
                </h2>
                <p class="hint" style="margin-bottom:18px;">Để lại thắc mắc về tuyến xe, cước gửi hàng hay ý kiến đóng góp phục vụ.</p>

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

                <form method="post" action="LienHe.php#gui-tin" novalidate>
                    <div class="form-grid">
                        <div class="field">
                            <label for="ten">
                                <?= icon('user', 16) ?> Họ và tên của bạn <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="ten"
                                name="ten"
                                placeholder="Ví dụ: Nguyễn Văn A"
                                value="<?= old('ten', $taiKhoan['name'] ?? '') ?>"
                                autocomplete="name"
                                required
                            >
                        </div>
                        <div class="row2">
                            <div class="field">
                                <label for="sdt">
                                    <?= icon('phone', 16) ?> Số điện thoại liên hệ <span class="required">*</span>
                                </label>
                                <input
                                    type="tel"
                                    id="sdt"
                                    name="sdt"
                                    placeholder="0912345678"
                                    value="<?= old('sdt', $taiKhoan['phone'] ?? '') ?>"
                                    autocomplete="tel"
                                    required
                                >
                            </div>
                            <div class="field">
                                <label for="email">
                                    <?= icon('mail', 16) ?> Địa chỉ email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="email@example.com"
                                    value="<?= old('email', $taiKhoan['email'] ?? '') ?>"
                                    autocomplete="email"
                                >
                            </div>
                        </div>
                        <div class="field">
                            <label for="noi_dung">
                                <?= icon('edit', 16) ?> Nội dung tin nhắn / Thắc mắc <span class="required">*</span>
                            </label>
                            <textarea
                                id="noi_dung"
                                name="noi_dung"
                                rows="4"
                                placeholder="Quý khách cần tư vấn về tuyến xe nào, gửi hàng hay góp ý về thái độ phục vụ?"
                                required
                            ><?= old('noi_dung') ?></textarea>
                        </div>
                        <button class="btn btn-y btn-lg btn-block" type="submit">
                            <?= icon('check', 18) ?> <span>Gửi tin nhắn liên hệ</span>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
