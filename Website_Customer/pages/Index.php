<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
include __DIR__ . '/../includes/header.php';

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
        <p><?= icon('sparkles', 18) ?> <strong>Khuyến mãi:</strong> <?= e($promo['tieu_de']) ?></p>
        <a href="TinTuc.php?id=<?= (int)$promo['id'] ?>">Xem điều kiện áp dụng <?= icon('arrow-right', 14) ?></a>
    </div>
</div>
<?php endif; ?>

<!-- ============ HERO + FORM TÌM CHUYẾN XE ============ -->
<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="user-role-badge badge-nhan_vien" style="margin-bottom:14px; font-size:0.85rem; padding:4px 14px;">
                <?= icon('shield-check', 16) ?> Nhà xe chất lượng cao Bạc Liêu
            </span>
            <h1>Mỗi ngày, xe Tài Nguyên rời Bạc Liêu đi khắp miền Nam</h1>
            <p class="lead">Đặt vé trực tuyến nhanh chóng đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt, Nha Trang và các tỉnh miền Tây. Đội xe đời mới với ghế ngồi và giường nằm tiện nghi.</p>
            
            <div class="hero-actions">
                <a class="btn btn-y" href="DatVe.php">
                    <?= icon('ticket', 18) ?> <span>Đặt vé ngay</span>
                </a>
                <a class="btn btn-ghost" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>">
                    <?= icon('phone', 18) ?> <span>Hotline: <?= e($company['hotline']) ?></span>
                </a>
            </div>

            <div class="hero-badges">
                <div class="hero-badge-item">
                    <?= icon('shield-check', 18) ?>
                    <span>100% Giữ đúng chỗ</span>
                </div>
                <div class="hero-badge-item">
                    <?= icon('car', 18) ?>
                    <span>Đón tận nhà miễn phí</span>
                </div>
                <div class="hero-badge-item">
                    <?= icon('clock', 18) ?>
                    <span>Xuất bến đúng giờ</span>
                </div>
            </div>
        </div>

        <!-- FORM TÌM CHUYẾN XE (GET TO DatVe.php) -->
        <form class="ticket" action="DatVe.php" method="get">
            <div class="ticket-head">
                <h2>
                    <?= icon('search', 22) ?>
                    <span>Tìm chuyến xe online</span>
                </h2>
                <p>Tra cứu nhanh giờ chạy, loại xe và chỗ ngồi</p>
            </div>
            <div class="ticket-body">
                <div class="field">
                    <span class="label" id="lbl-chieu">
                        <?= icon('arrow-left-right', 16) ?> Chiều di chuyển
                    </span>
                    <div class="seg" role="radiogroup" aria-labelledby="lbl-chieu">
                        <label>
                            <input type="radio" name="chieu" value="di" checked>
                            <span>Đi từ Bạc Liêu</span>
                        </label>
                        <label>
                            <input type="radio" name="chieu" value="ve">
                            <span>Về Bạc Liêu</span>
                        </label>
                    </div>
                </div>

                <div class="field">
                    <label for="h-tuyen">
                        <?= icon('pin', 16) ?> Tuyến đường / Điểm đến
                    </label>
                    <select id="h-tuyen" name="tuyen">
                        <?php foreach ($routes as $r): ?>
                            <option value="<?= e($r['slug']) ?>"<?= $r['slug'] === 'tp-hcm' ? ' selected' : '' ?>>
                                <?= e($r['ten']) ?> (<?= (int)$r['km'] ?> km)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row2">
                    <div class="field">
                        <label for="h-ngay">
                            <?= icon('calendar', 16) ?> Ngày đi
                        </label>
                        <input type="date" id="h-ngay" name="ngay" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="field">
                        <label for="h-sl">
                            <?= icon('users', 16) ?> Số khách
                        </label>
                        <input type="number" id="h-sl" name="sl" value="1" min="1" max="10">
                    </div>
                </div>

                <button class="btn btn-y btn-lg btn-block" type="submit">
                    <?= icon('search', 18) ?> <span>Tìm chuyến & Chọn chỗ</span>
                </button>
                <p class="hint" style="margin:0; text-align:center; font-size:0.82rem;">
                    * Giá vé và tình trạng chỗ sẽ được nhân viên nhà xe kiểm tra & xác nhận.
                </p>
            </div>
        </form>
    </div>
</section>

<!-- ============ CÁC TUYẾN ============ -->
<section class="section">
    <div class="wrap">
        <div class="sec-head">
            <h2><?= count($routes) ?> tuyến xe xuất phát từ Bạc Liêu</h2>
            <p>Chọn điểm đến của bạn để tham khảo khoảng cách, thời gian di chuyển và đặt vé trực tiếp.</p>
        </div>

        <div class="fan" id="fan">
            <?php foreach ($sorted as $r): ?>
                <a class="fan-row" href="DatVe.php?tuyen=<?= e($r['slug']) ?>" title="Đặt vé đi <?= e($r['ten']) ?>">
                    <span class="fan-city"><?= e($r['ten']) ?></span>
                    <span class="fan-track"><i style="--w: calc(<?= round($r['km'] / $maxKm * 100) ?>% - 12px)"></i></span>
                    <span class="fan-meta">
                        <span><?= (int)$r['km'] ?> km · <?= e(duration($r['gio'])) ?></span>
                        <span class="btn btn-sm btn-line" style="padding:4px 10px; font-size:0.84rem;">Chọn vé</span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:24px; text-align:center;">
            <a class="btn btn-line" href="LichTrinh.php">
                <?= icon('clock', 18) ?> <span>Xem toàn bộ lịch trình và bảng giá vé chi tiết</span>
            </a>
        </p>
    </div>
</section>

<!-- ============ LOẠI PHƯƠNG TIỆN ============ -->
<section class="section section-mist">
    <div class="wrap">
        <div class="sec-head">
            <h2>Hai dòng xe phục vụ mọi nhu cầu di chuyển</h2>
            <p>Trang bị điều hòa, rèm che, cổng sạc điện thoại và bảo dưỡng định kỳ nghiêm ngặt.</p>
        </div>

        <div class="types">
            <article class="type type-seat">
                <span class="tag tag-ghe" style="align-self:flex-start; margin-bottom:12px;">Nội vùng & Miền Tây</span>
                <h3>Xe ghế ngồi tiện nghi</h3>
                <p class="type-price">Giá vé chỉ từ <?= money(min_price('ghe')) ?></p>
                <ul>
                    <li><?= icon('check', 20) ?> Ghế bọc da ngả lưng êm ái, phù hợp các tuyến trong ngày</li>
                    <li><?= icon('check', 20) ?> Nhiều khung giờ khởi hành liên tục từ sáng sớm đến chiều</li>
                    <li><?= icon('check', 20) ?> Đón và trả tận nơi trong nội ô TP. Bạc Liêu</li>
                    <li><?= icon('check', 20) ?> Nước suối và khăn lạnh miễn phí suốt hành trình</li>
                </ul>
                <a class="btn btn-line" href="DatVe.php">
                    <?= icon('ticket', 16) ?> <span>Đặt vé ghế ngồi</span>
                </a>
            </article>

            <article class="type type-bed">
                <span class="tag tag-khuyen-mai" style="align-self:flex-start; margin-bottom:12px; background:var(--color-accent); color:var(--color-primary-dark);">Đường dài & Ban đêm</span>
                <h3>Xe giường nằm cao cấp</h3>
                <p class="type-price">Giá vé chỉ từ <?= money(min_price('giuong')) ?></p>
                <ul>
                    <li><?= icon('check', 20) ?> Giường nằm 2 tầng riêng tư, chăn đệm sạch sẽ cho giấc ngủ ngon</li>
                    <li><?= icon('check', 20) ?> Đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt, Nha Trang</li>
                    <li><?= icon('check', 20) ?> Chạy đêm êm ái, thức dậy là đã tới điểm đến</li>
                    <li><?= icon('check', 20) ?> Trạm dừng chân tiện nghi, ăn uống vệ sinh sạch sẽ</li>
                </ul>
                <a class="btn btn-y" href="DatVe.php">
                    <?= icon('ticket', 16) ?> <span>Đặt vé giường nằm</span>
                </a>
            </article>
        </div>
    </div>
</section>

<!-- ============ DỊCH VỤ ============ -->
<section class="section">
    <div class="wrap">
        <div class="sec-head">
            <h2>Hệ sinh thái dịch vụ của nhà xe Tài Nguyên</h2>
            <p>Không chỉ vận tải hành khách, chúng tôi đáp ứng trọn vẹn nhu cầu đi lại và gửi đồ của bạn.</p>
        </div>
        <div class="svc-strip">
            <a class="svc" href="DichVu.php#hang-hoa">
                <div class="svc-icon-box"><?= icon('box', 28) ?></div>
                <h3>Vận chuyển hàng hóa</h3>
                <p>Nhận gửi bưu phẩm, nông sản, hàng khô và máy móc hỏa tốc trong ngày giữa Bạc Liêu và các tỉnh.</p>
            </a>
            <a class="svc" href="DichVu.php#trung-chuyen">
                <div class="svc-icon-box"><?= icon('car', 28) ?></div>
                <h3>Xe trung chuyển</h3>
                <p>Đón và trả khách tại nhà hoàn toàn miễn phí trong nội ô TP. Bạc Liêu, an tâm và tiện lợi.</p>
            </a>
            <a class="svc" href="DichVu.php#khach-san">
                <div class="svc-icon-box"><?= icon('bed', 28) ?></div>
                <h3>Hợp tác khách sạn</h3>
                <p>Hỗ trợ đặt phòng khách sạn uy tín với giá ưu đãi tại các điểm du lịch Đà Lạt, Nha Trang, Vũng Tàu.</p>
            </a>
            <a class="svc" href="DichVu.php#tram-dung-chan">
                <div class="svc-icon-box"><?= icon('coffee', 28) ?></div>
                <h3>Trạm dừng chân</h3>
                <p>Trạm dừng rộng rãi, sạch sẽ với đồ ăn ngon miệng, đảm bảo vệ sinh an toàn thực phẩm.</p>
            </a>
        </div>
    </div>
</section>

<!-- ============ TIN TỨC ============ -->
<section class="section section-mist">
    <div class="wrap">
        <div class="sec-head">
            <h2>Tin tức & Khuyến mãi mới nhất</h2>
            <p>Cập nhật thông tin các chương trình giảm giá vé, thông báo lịch chạy lễ Tết và tin tức nhà xe.</p>
        </div>
        <div class="news-grid">
            <a class="news-lead" href="TinTuc.php?id=<?= (int)$latest[0]['id'] ?>">
                <p style="margin-bottom:8px;">
                    <span class="tag tag-<?= e($latest[0]['loai']) ?>"><?= e($news_types[$latest[0]['loai']]) ?></span>
                    <span class="date"><?= date('d/m/Y', strtotime($latest[0]['ngay'])) ?></span>
                </p>
                <h3><?= e($latest[0]['tieu_de']) ?></h3>
                <p class="muted" style="margin:0"><?= e($latest[0]['tom_tat']) ?></p>
            </a>
            <ul class="news-list">
                <?php foreach (array_slice($latest, 1) as $n): ?>
                    <li>
                        <a href="TinTuc.php?id=<?= (int)$n['id'] ?>">
                            <span class="tag tag-<?= e($n['loai']) ?>"><?= e($news_types[$n['loai']]) ?></span>
                            <span class="date"><?= date('d/m/Y', strtotime($n['ngay'])) ?></span>
                            <h3><?= e($n['tieu_de']) ?></h3>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <p style="margin-top:28px; text-align:center;">
            <a class="btn btn-line" href="TinTuc.php">
                <span>Xem tất cả tin tức và khuyến mãi</span> <?= icon('arrow-right', 16) ?>
            </a>
        </p>
    </div>
</section>

<!-- ============ GỌI HOTLINE ============ -->
<section class="section section-dark cta">
    <div class="wrap">
        <h2>Cần hỗ trợ đặt vé hoặc gửi hàng nhanh?</h2>
        <p>Tổng đài viên Tài Nguyên sẵn sàng phục vụ và giải đáp mọi thắc mắc từ <?= e($company['gio_mo']) ?> hàng ngày.</p>
        <div class="hero-actions">
            <a class="btn btn-y btn-lg" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>">
                <?= icon('phone', 20) ?> <span>Gọi Hotline: <?= e($company['hotline']) ?></span>
            </a>
            <a class="btn btn-ghost btn-lg" href="LienHe.php">
                <?= icon('mail', 20) ?> <span>Gửi tin nhắn liên hệ</span>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
