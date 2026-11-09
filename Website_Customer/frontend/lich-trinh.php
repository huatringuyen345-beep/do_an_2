<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Lịch trình và giá vé';
$pageDesc  = 'Giờ khởi hành, thời gian chạy và giá vé xe ghế ngồi, giường nằm từ Bạc Liêu đi các tỉnh.';
include __DIR__ . '/includes/header.php';

$groups = array_values(array_unique(array_column($routes, 'nhom')));
?>

<section class="page-head">
    <div class="wrap">
        <h1>Lịch trình và giá vé</h1>
        <p>Tất cả các tuyến chạy hai chiều. Giờ ghi bên dưới là giờ xuất bến tại Bạc Liêu; vui lòng xác nhận giờ và giá với nhà xe trước chuyến đi.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="toolbar">
            <div class="chips" role="group" aria-label="Lọc theo khu vực">
                <button type="button" class="chip" data-group="all" aria-pressed="true">Tất cả</button>
                <?php foreach ($groups as $g): ?>
                    <button type="button" class="chip" data-group="<?= e($g) ?>" aria-pressed="false"><?= e($g) ?></button>
                <?php endforeach; ?>
            </div>
            <label class="sr-only" for="routeSearch">Tìm điểm đến</label>
            <input type="search" id="routeSearch" placeholder="Tìm điểm đến, ví dụ Đà Lạt">
        </div>

        <div class="route-list" id="routeList">
            <?php foreach ($routes as $r): ?>
                <article class="route-row" data-group="<?= e($r['nhom']) ?>" data-name="Bạc Liêu <?= e($r['ten']) ?>">
                    <h2 class="route-name">
                        Bạc Liêu – <?= e($r['ten']) ?>
                        <small><?= e($r['nhom']) ?></small>
                    </h2>
                    <div>
                        <?= (int)$r['km'] ?> km<br>
                        <span class="muted"><?= e(duration($r['gio'])) ?></span>
                    </div>
                    <div class="times" aria-label="Giờ khởi hành">
                        <?php foreach ($r['khoi_hanh'] as $t): ?><span><?= e($t) ?></span><?php endforeach; ?>
                    </div>
                    <div>
                        <?php if ($r['ghe']): ?><span class="price-line">Ghế ngồi <b><?= money($r['ghe']) ?></b></span><?php endif; ?>
                        <?php if ($r['giuong']): ?><span class="price-line">Giường nằm <b><?= money($r['giuong']) ?></b></span><?php endif; ?>
                    </div>
                    <div class="go">
                        <a class="btn btn-y btn-sm" href="dat-ve.php?tuyen=<?= e($r['slug']) ?>">Đặt vé</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="empty" id="routeEmpty" hidden>Không tìm thấy tuyến phù hợp. Thử gõ tên tỉnh khác hoặc chọn "Tất cả".</p>

        <p class="muted" style="margin-top:24px">Giá vé đang hiển thị để tham khảo, là giá một chiều cho một người lớn và có thể thay đổi vào dịp lễ, Tết. Gọi hotline <?= e($company['hotline']) ?> để xác nhận giá và giờ chạy.</p>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap split">
        <div>
            <h2>Điểm đón và trả khách</h2>
            <p>Khách lên xe tại văn phòng Tài Nguyên ở Bạc Liêu. Nếu ở xa, hãy đăng ký xe trung chuyển khi đặt vé để được đón tận nơi.</p>
            <p><a class="btn btn-line" href="dich-vu.php#trung-chuyen">Tìm hiểu xe trung chuyển</a></p>
        </div>
        <div class="panel">
            <h3>Văn phòng Tài Nguyên</h3>
            <ul class="tick">
                <li><?= icon('pin', 20) ?> <?= e($company['dia_chi']) ?></li>
                <li><?= icon('clock', 20) ?> Mở cửa <?= e($company['gio_mo']) ?></li>
                <li><?= icon('phone', 20) ?> <?= e($company['hotline']) ?></li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
