<?php
require_once __DIR__ . '/../backend/bootstrap.php';
require_once __DIR__ . '/../backend/data/loader.php';
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
            <p>Phí đổi hoặc hủy phụ thuộc vào chuyến đi và thời điểm yêu cầu. Vui lòng liên hệ nhà xe để được xác nhận mức phí áp dụng cho vé của bạn.</p>
        </div>
        <p>Thông tin chính sách cụ thể sẽ được cập nhật tại đây khi có dữ liệu chính thức.</p>
        <a class="btn btn-y" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>">
            <?= icon('phone', 18) ?> <span>Gọi <?= e($company['hotline']) ?></span>
        </a>
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
