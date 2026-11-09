<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
include __DIR__ . '/includes/header.php';

// Sắp xếp tuyến theo khoảng cách để vẽ sơ đồ
$sorted = $routes;
usort($sorted, function ($a, $b) { return $a['km'] <=> $b['km']; });
$maxKm = max(array_column($routes, 'km'));

// Chỉ hiển thị các chương trình còn hạn.
$latest = array_slice($news, 0, 4);
$promo = null;
foreach ($news as $n) {
    if ($n['loai'] === 'khuyen-mai' && (!isset($n['ket_thuc']) || $n['ket_thuc'] >= date('Y-m-d'))) {
        $promo = $n;
        break;
    }
}
?>

<?php if ($promo): ?>
<div class="promo">
    <div class="wrap">
        <p>Khuyến mãi: <?= e($promo['tieu_de']) ?></p>
        <a href="tin-tuc.php?id=<?= (int)$promo['id'] ?>">Xem điều kiện áp dụng</a>
    </div>
</div>
<?php endif; ?>

<!-- ============ HERO + FORM TÌM CHUYẾN ============ -->
<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <h1>Mỗi ngày, xe Tài Nguyên rời Bạc Liêu đi khắp miền Nam</h1>
            <p class="lead">Gửi yêu cầu đặt vé đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt, Nha Trang và các tỉnh đồng bằng sông Cửu Long. Có xe ghế ngồi và xe giường nằm.</p>
            <div class="hero-actions">
                <a class="btn btn-y" href="lich-trinh.php">Xem lịch trình</a>
                <a class="btn btn-ghost" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= icon('phone', 20) ?> <?= e($company['hotline']) ?></a>
            </div>
        </div>

        <form class="ticket" action="dat-ve.php" method="get">
            <div class="ticket-head">
                <h2>Tìm chuyến xe</h2>
                <p>Chọn tuyến, ngày đi và số khách</p>
            </div>
            <div class="ticket-body">
                <div class="field">
                    <span class="label" id="lbl-chieu">Chiều đi</span>
                    <div class="seg" role="radiogroup" aria-labelledby="lbl-chieu">
                        <label><input type="radio" name="chieu" value="di" checked><span>Đi từ Bạc Liêu</span></label>
                        <label><input type="radio" name="chieu" value="ve"><span>Về Bạc Liêu</span></label>
                    </div>
                </div>
                <div class="field">
                    <label for="h-tuyen">Điểm đến / điểm đi còn lại</label>
                    <select id="h-tuyen" name="tuyen">
                        <?php foreach ($routes as $r): ?>
                            <option value="<?= e($r['slug']) ?>"<?= $r['slug'] === 'tp-hcm' ? ' selected' : '' ?>><?= e($r['ten']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row2">
                    <div class="field">
                        <label for="h-ngay">Ngày đi</label>
                        <input type="date" id="h-ngay" name="ngay" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="field">
                        <label for="h-sl">Số khách</label>
                        <input type="number" id="h-sl" name="sl" value="1" min="1" max="10">
                    </div>
                </div>
                <button class="btn btn-y btn-block" type="submit">Tìm chuyến</button>
                <p class="hint">Giờ chạy và giá vé cần được nhà xe xác nhận. Tìm chuyến chưa phải là xác nhận chỗ.</p>
            </div>
        </form>
    </div>
</section>

<!-- ============ CÁC TUYẾN ============ -->
<section class="section">
    <div class="wrap">
        <div class="sec-head">
            <h2><?= count($routes) ?> tuyến chạy từ Bạc Liêu</h2>
            <p>Chọn tuyến để tham khảo giờ chạy, giá vé và gửi yêu cầu đặt chỗ.</p>
        </div>

        <div class="fan" id="fan">
            <?php foreach ($sorted as $r): ?>
                <a class="fan-row" href="dat-ve.php?tuyen=<?= e($r['slug']) ?>">
                    <span class="fan-city"><?= e($r['ten']) ?></span>
                    <span class="fan-track"><i style="--w: calc(<?= round($r['km'] / $maxKm * 100) ?>% - 12px)"></i></span>
                    <span class="fan-meta"><?= (int)$r['km'] ?> km, <?= e(duration($r['gio'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:20px"><a href="lich-trinh.php">Xem giá vé và giờ khởi hành của tất cả các tuyến</a></p>
    </div>
</section>

<!-- ============ LOẠI XE ============ -->
<section class="section section-mist">
    <div class="wrap">
        <div class="sec-head">
            <h2>Hai loại xe cho hai kiểu chuyến đi</h2>
            <p>Đi trong ngày thì chọn ghế ngồi. Đi xa qua đêm thì chọn giường nằm.</p>
        </div>

        <div class="types">
            <article class="type type-seat">
                <h3>Xe ghế ngồi</h3>
                <p class="type-price">Giá từ <?= money(min_price('ghe')) ?></p>
                <ul>
                    <li><?= icon('check', 20) ?> Ghế ngả, chạy các tuyến trong khu vực miền Tây</li>
                    <li><?= icon('check', 20) ?> Chạy nhiều chuyến mỗi ngày, từ sáng sớm đến chiều</li>
                    <li><?= icon('check', 20) ?> Có xe trung chuyển đón tại nhà</li>
                </ul>
                <a class="btn btn-line" href="lich-trinh.php">Xem tuyến ghế ngồi</a>
            </article>
            <article class="type type-bed">
                <h3>Xe giường nằm</h3>
                <p class="type-price">Giá từ <?= money(min_price('giuong')) ?></p>
                <ul>
                    <li><?= icon('check', 20) ?> Hai tầng giường, đi TP. Hồ Chí Minh, Vũng Tàu, Đà Lạt, Nha Trang</li>
                    <li><?= icon('check', 20) ?> Chuyến đêm, ngủ một giấc là tới nơi</li>
                    <li><?= icon('check', 20) ?> Dừng nghỉ tại trạm dừng chân dọc đường</li>
                </ul>
                <a class="btn btn-y" href="lich-trinh.php">Xem tuyến giường nằm</a>
            </article>
        </div>
    </div>
</section>

<!-- ============ DỊCH VỤ ============ -->
<section class="section">
    <div class="wrap">
        <div class="sec-head">
            <h2>Ngoài chở khách, Tài Nguyên còn làm gì?</h2>
        </div>
        <div class="svc-strip">
            <a class="svc" href="dich-vu.php#hang-hoa">
                <?= icon('box', 32) ?>
                <h3>Vận chuyển hàng hóa</h3>
                <p>Gửi hàng theo xe khách trên tất cả các tuyến, nhận tại văn phòng hoặc tận nơi.</p>
            </a>
            <a class="svc" href="dich-vu.php#trung-chuyen">
                <?= icon('car', 32) ?>
                <h3>Xe trung chuyển</h3>
                <p>Đón và trả khách tại nhà trong nội ô, không cần tự ra bến.</p>
            </a>
            <a class="svc" href="dich-vu.php#khach-san">
                <?= icon('bed', 32) ?>
                <h3>Khách sạn</h3>
                <p>Đặt phòng kèm vé xe tại các điểm đến như Đà Lạt, Nha Trang, Vũng Tàu.</p>
            </a>
            <a class="svc" href="dich-vu.php#tram-dung-chan">
                <?= icon('coffee', 32) ?>
                <h3>Trạm dừng chân</h3>
                <p>Nghỉ ngơi, ăn uống và đi vệ sinh trên những tuyến đường dài.</p>
            </a>
        </div>
    </div>
</section>

<!-- ============ TIN TỨC ============ -->
<section class="section section-mist">
    <div class="wrap">
        <div class="sec-head">
            <h2>Tin mới từ Tài Nguyên</h2>
        </div>
        <div class="news-grid">
            <a class="news-lead" href="tin-tuc.php?id=<?= (int)$latest[0]['id'] ?>">
                <p><span class="tag tag-<?= e($latest[0]['loai']) ?>"><?= e($news_types[$latest[0]['loai']]) ?></span><span class="date"><?= date('d/m/Y', strtotime($latest[0]['ngay'])) ?></span></p>
                <h3><?= e($latest[0]['tieu_de']) ?></h3>
                <p class="muted" style="margin:0"><?= e($latest[0]['tom_tat']) ?></p>
            </a>
            <ul class="news-list">
                <?php foreach (array_slice($latest, 1) as $n): ?>
                    <li>
                        <a href="tin-tuc.php?id=<?= (int)$n['id'] ?>">
                            <span class="tag tag-<?= e($n['loai']) ?>"><?= e($news_types[$n['loai']]) ?></span><span class="date"><?= date('d/m/Y', strtotime($n['ngay'])) ?></span>
                            <h3><?= e($n['tieu_de']) ?></h3>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <p style="margin-top:28px"><a href="tin-tuc.php">Xem tất cả tin tức và khuyến mãi</a></p>
    </div>
</section>

<!-- ============ GỌI HOTLINE ============ -->
<section class="section section-dark cta">
    <div class="wrap">
        <h2>Cần hỗ trợ đặt vé hoặc gửi hàng?</h2>
        <p>Gọi hotline, nhân viên trả lời <?= e($company['gio_mo']) ?>.</p>
        <div class="hero-actions">
            <a class="btn btn-y" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= icon('phone', 20) ?> <?= e($company['hotline']) ?></a>
            <a class="btn btn-ghost" href="lien-he.php">Nhắn tin cho chúng tôi</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
