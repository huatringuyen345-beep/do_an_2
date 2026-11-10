<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Dịch vụ';
$pageDesc  = 'Dịch vụ của Tài Nguyên: vận chuyển hàng hóa, xe trung chuyển, khách sạn và trạm dừng chân.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Dịch vụ</h1>
        <p>Vận chuyển hàng hóa, xe trung chuyển, khách sạn và trạm dừng chân.</p>
    </div>
</section>

<div class="wrap">

    <!-- Vận chuyển hàng hóa -->
    <section class="service-block split" id="hang-hoa">
        <div>
            <?= icon('box', 36) ?>
            <h2>Vận chuyển hàng hóa</h2>
            <p>Hàng được gửi theo xe khách trên tất cả các tuyến của Tài Nguyên. Phù hợp với kiện hàng nhỏ, nông sản, hàng tiêu dùng cần đến nơi trong ngày hoặc trong đêm.</p>
            <h3>Cách gửi hàng</h3>
            <ol class="steps">
                <li><span>Mang hàng đến văn phòng Tài Nguyên, hoặc gọi hotline để xe trung chuyển đến lấy.</span></li>
                <li><span>Nhân viên cân hàng, báo cước và đưa biên nhận.</span></li>
                <li><span>Người nhận mang biên nhận hoặc báo số điện thoại để nhận hàng tại điểm đến.</span></li>
            </ol>
        </div>
        <div class="panel panel-mist">
            <h3>Ước tính cước gửi hàng</h3>
            <form id="estimateForm">
                <div class="form-grid">
                    <div class="field">
                        <label for="e-tuyen">Tuyến</label>
                        <select id="e-tuyen" name="tuyen">
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= e($r['slug']) ?>" data-km="<?= (int)$r['km'] ?>"><?= e($r['ten']) ?> (<?= (int)$r['km'] ?> km)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="e-kg">Khối lượng (kg)</label>
                        <input type="number" id="e-kg" name="kg" value="10" min="1" max="500">
                    </div>
                </div>
            </form>
            <div class="estimate">
                <span class="muted">Cước ước tính</span><br>
                <strong id="estimateOut">0đ</strong>
            </div>
            <p class="muted" style="margin:12px 0 0;font-size:.9rem">Chỉ để tham khảo. Cước chính thức được báo khi nhân viên cân hàng.</p>
        </div>
    </section>

    <!-- Xe trung chuyển -->
    <section class="service-block split split-rev" id="trung-chuyen">
        <div class="split-a">
            <?= icon('car', 36) ?>
            <h2>Xe trung chuyển</h2>
            <p>Không cần tự ra văn phòng. Xe nhỏ sẽ đón bạn tại nhà trong nội ô và các khu vực lân cận, đưa ra điểm xuất bến rồi lên xe lớn.</p>
            <p>Khi đặt vé online, chỉ cần đánh dấu "Đăng ký xe trung chuyển" và nhập địa chỉ. Nhân viên sẽ gọi xác nhận giờ đón.</p>
            <a class="btn btn-g" href="dat-ve.php">Đặt vé kèm trung chuyển</a>
        </div>
        <div class="panel">
            <h3>Dịch vụ gồm có</h3>
            <ul class="tick">
                <li><?= icon('check', 20) ?> Đón tại nhà trước giờ xe chạy</li>
                <li><?= icon('check', 20) ?> Trả khách về nơi ở khi xe đến Bạc Liêu</li>
                <li><?= icon('check', 20) ?> Đón nhận hàng gửi tại nhà</li>
            </ul>
        </div>
    </section>

    <!-- Khách sạn -->
    <section class="service-block split" id="khach-san">
        <div>
            <?= icon('bed', 36) ?>
            <h2>Khách sạn</h2>
            <p>Đi Đà Lạt, Nha Trang hay Vũng Tàu, bạn có thể đặt phòng khách sạn cùng lúc với vé xe. Tài Nguyên liên kết với các khách sạn gần điểm xe đến để bạn nghỉ ngơi ngay sau chuyến đi.</p>
            <p>Gọi hotline hoặc nhắn qua trang Liên hệ để được tư vấn phòng và giá.</p>
            <a class="btn btn-line" href="lien-he.php">Hỏi giá phòng</a>
        </div>
        <div class="panel panel-mist">
            <h3>Điểm đến có khách sạn liên kết</h3>
            <ul class="tick">
                <?php foreach (['Đà Lạt', 'Nha Trang', 'Vũng Tàu', 'TP. Hồ Chí Minh'] as $c): ?>
                    <li><?= icon('pin', 20) ?> <?= e($c) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Trạm dừng chân -->
    <section class="service-block split split-rev" id="tram-dung-chan">
        <div class="split-a">
            <?= icon('coffee', 36) ?>
            <h2>Trạm dừng chân</h2>
            <p>Các tuyến dài như Đà Lạt và Nha Trang đều có điểm dừng nghỉ. Xe đỗ khoảng 20 đến 30 phút để khách đi vệ sinh, ăn uống và duỗi chân.</p>
            <p>Lái xe sẽ thông báo giờ xe tiếp tục chạy. Quý khách vui lòng ghi nhớ biển số xe và giờ lên lại.</p>
        </div>
        <div class="panel">
            <h3>Tại trạm dừng chân</h3>
            <ul class="tick">
                <li><?= icon('check', 20) ?> Nhà vệ sinh sạch sẽ</li>
                <li><?= icon('check', 20) ?> Quán ăn và nước uống</li>
                <li><?= icon('check', 20) ?> Chỗ ngồi nghỉ có mái che</li>
            </ul>
        </div>
    </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
