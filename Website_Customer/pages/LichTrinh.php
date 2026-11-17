<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Lịch trình và giá vé';
$pageDesc  = 'Giờ khởi hành, thời gian chạy và giá vé xe ghế ngồi, giường nằm từ Bạc Liêu đi các tỉnh.';
include __DIR__ . '/../includes/header.php';

$groups = array_values(array_unique(array_column($routes, 'nhom')));
?>

<section class="page-head">
    <div class="wrap">
        <h1>Lịch trình & Giá vé các tuyến</h1>
        <p>Tất cả các tuyến xe đều hoạt động hai chiều liên tục mỗi ngày. Giờ khởi hành bên dưới là giờ xuất bến tại TP. Bạc Liêu.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="toolbar">
            <div class="chips" role="group" aria-label="Lọc theo khu vực">
                <button type="button" class="chip" data-group="all" aria-pressed="true">Tất cả tuyến</button>
                <?php foreach ($groups as $g): ?>
                    <button type="button" class="chip" data-group="<?= e($g) ?>" aria-pressed="false"><?= e($g) ?></button>
                <?php endforeach; ?>
            </div>
            <div>
                <label class="sr-only" for="routeSearch">Tìm điểm đến</label>
                <input type="search" id="routeSearch" placeholder="Tìm điểm đến (ví dụ: Đà Lạt, TP.HCM...)">
            </div>
        </div>

        <div class="route-list" id="routeList">
            <?php foreach ($routes as $r): ?>
                <article class="route-row" data-group="<?= e($r['nhom']) ?>" data-name="Bạc Liêu <?= e($r['ten']) ?>">
                    <h2 class="route-name">
                        Bạc Liêu – <?= e($r['ten']) ?>
                        <small><?= icon('pin', 14) ?> <?= e($r['nhom']) ?></small>
                    </h2>
                    <div class="route-km">
                        <?= (int)$r['km'] ?> km<br>
                        <span class="muted"><?= icon('clock', 13) ?> <?= e(duration($r['gio'])) ?></span>
                    </div>
                    <div class="times" aria-label="Giờ khởi hành">
                        <?php foreach ($r['khoi_hanh'] as $t): ?>
                            <span><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div>
                        <?php if ($r['ghe']): ?>
                            <span class="price-line">Ghế ngồi: <b><?= money($r['ghe']) ?></b></span>
                        <?php endif; ?>
                        <?php if ($r['giuong']): ?>
                            <span class="price-line">Giường nằm: <b><?= money($r['giuong']) ?></b></span>
                        <?php endif; ?>
                    </div>
                    <div class="go">
                        <a class="btn btn-y btn-sm" href="DatVe.php?tuyen=<?= e($r['slug']) ?>">
                            <?= icon('ticket', 15) ?> <span>Đặt vé</span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="empty" id="routeEmpty" hidden>
            <?= icon('alert-circle', 24) ?><br>
            Không tìm thấy tuyến xe phù hợp. Vui lòng thử tìm từ khóa khác hoặc chọn "Tất cả tuyến".
        </p>

        <p class="muted" style="margin-top:24px; font-size:0.9rem;">
            * Giá vé niêm yết trên website là giá vé một chiều cho một hành khách và có thể điều chỉnh theo quy định vào dịp lễ, Tết. Quý khách vui lòng gọi hotline <strong><?= e($company['hotline']) ?></strong> để được nhân viên tư vấn nhanh nhất.
        </p>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap split">
        <div>
            <h2>Điểm đón và trả khách</h2>
            <p>Khách có thể lên xe trực tiếp tại văn phòng Tài Nguyên ở TP. Bạc Liêu. Nếu ở xa, hãy đăng ký xe trung chuyển đón trả tận nhà khi đặt vé để được hỗ trợ hoàn toàn miễn phí.</p>
            <p><a class="btn btn-line" href="DichVu.php#trung-chuyen"><?= icon('car', 18) ?> <span>Tìm hiểu dịch vụ xe trung chuyển</span></a></p>
        </div>
        <div class="panel">
            <h3>Văn phòng nhà xe Tài Nguyên</h3>
            <ul class="tick">
                <li><?= icon('pin', 20) ?> <span><strong>Bạc Liêu:</strong> <?= e($company['dia_chi']) ?></span></li>
                <li><?= icon('phone', 20) ?> <span><strong>Hotline tổng đài:</strong> <?= e($company['hotline']) ?> · <?= e($company['hotline2']) ?></span></li>
                <li><?= icon('clock', 20) ?> <span><strong>Thời gian hoạt động:</strong> <?= e($company['gio_mo']) ?></span></li>
                <li><?= icon('car', 20) ?> <span><strong>Đón trả tận nơi:</strong> Bán kính 10km nội ô TP. Bạc Liêu</span></li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
