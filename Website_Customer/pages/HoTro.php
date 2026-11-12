<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Hỗ trợ';
$pageDesc  = 'Hình thức thanh toán, chính sách đổi hủy vé và câu hỏi thường gặp khi đi xe Tài Nguyên.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Hỗ trợ khách hàng</h1>
        <p>Thanh toán, đổi hủy vé và các câu hỏi thường gặp.</p>
    </div>
</section>

<section class="section" id="thanh-toan">
    <div class="wrap">
        <div class="sec-head">
            <h2>Hình thức thanh toán</h2>
            <p>Bạn có thể chọn một trong các cách sau khi đặt vé.</p>
        </div>
        <div class="pay-cards">
            <?php foreach ($payments as $p): ?>
                <div>
                    <?= icon($p['icon'], 32) ?>
                    <h3><?= e($p['ten']) ?></h3>
                    <p class="muted"><?= e($p['mota']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-mist" id="doi-huy">
    <div class="wrap">
        <div class="sec-head">
            <h2>Đổi và hủy vé</h2>
            <p>Bảng phí mẫu, vui lòng thay bằng chính sách thực tế của công ty.</p>
        </div>
        <div class="table-scroll">
            <table class="fees">
                <thead>
                    <tr><th>Thời điểm hủy hoặc đổi</th><th>Phí</th></tr>
                </thead>
                <tbody>
                    <tr><td>Trước giờ xe chạy trên 24 giờ</td><td>Miễn phí</td></tr>
                    <tr><td>Từ 12 đến 24 giờ</td><td>10% giá vé</td></tr>
                    <tr><td>Từ 4 đến 12 giờ</td><td>30% giá vé</td></tr>
                    <tr><td>Dưới 4 giờ</td><td>50% giá vé</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section" id="cau-hoi">
    <div class="wrap">
        <div class="sec-head"><h2>Câu hỏi thường gặp</h2></div>
        <div style="max-width:820px">
            <?php foreach ($faqs as $f): ?>
                <details>
                    <summary><?= e($f['q']) ?></summary>
                    <div class="body"><p><?= e($f['a']) ?></p></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-dark cta">
    <div class="wrap">
        <h2>Chưa tìm thấy câu trả lời?</h2>
        <p>Gọi hotline hoặc gửi tin nhắn, chúng tôi sẽ trả lời bạn.</p>
        <div class="hero-actions">
            <a class="btn btn-y" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= icon('phone', 20) ?> <?= e($company['hotline']) ?></a>
            <a class="btn btn-ghost" href="LienHe.php">Gửi tin nhắn</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
