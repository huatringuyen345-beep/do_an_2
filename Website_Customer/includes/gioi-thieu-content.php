<section class="page-head">
    <div class="wrap">
        <h1>Giới thiệu về Tài Nguyên</h1>
        <p>Công ty vận tải hành khách và hàng hóa tại Bạc Liêu.</p>
    </div>
</section>

<section class="section">
    <div class="wrap split">
        <div>
            <h2>Chúng tôi là ai</h2>
            <p class="lead">Tài Nguyên chuyên vận tải hành khách và hàng hóa từ Bạc Liêu đến nhiều tỉnh, thành trong cả nước.</p>
            <p>Công ty có các tuyến vận tải đi tất cả các tỉnh đồng bằng sông Cửu Long, cùng các tuyến đường dài đi TP. Hồ Chí Minh, Bình Dương, Vũng Tàu, Đà Lạt và Nha Trang.</p>
            <p>Xe của công ty gồm hai dạng: xe ghế ngồi cho các tuyến ngắn và xe giường nằm cho các tuyến đường dài. Ngoài chở khách, Tài Nguyên còn nhận vận chuyển hàng hóa, có xe trung chuyển đón khách tại nhà, liên kết khách sạn tại điểm đến và các trạm dừng chân dọc đường.</p>
        </div>
        <div class="panel panel-mist">
            <h3>Mạng tuyến hiện tại</h3>
            <?php foreach ($byGroup as $g => $list): ?>
                <p style="margin-bottom:.8em"><strong><?= e($g) ?></strong><br><?= e(implode(', ', $list)) ?></p>
            <?php endforeach; ?>
            <a href="LichTrinh.php">Xem giờ chạy và giá vé</a>
        </div>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap">
        <div class="sec-head"><h2>Chúng tôi cam kết</h2></div>
        <div class="values">
            <div>
                <?= icon('clock', 32) ?>
                <h3>Đúng giờ</h3>
                <p>Xe xuất bến theo giờ đã công bố. Khi có thay đổi, nhân viên gọi báo trước cho khách đã đặt vé.</p>
            </div>
            <div>
                <?= icon('shield', 32) ?>
                <h3>An toàn</h3>
                <p>Xe được bảo dưỡng định kỳ. Lái xe có bằng hạng D và kinh nghiệm chạy đường dài.</p>
            </div>
            <div>
                <?= icon('users', 32) ?>
                <h3>Dễ liên hệ</h3>
                <p>Đặt vé online hoặc gọi hotline. Có người trả lời mỗi ngày trong giờ làm việc.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap split split-rev">
        <div class="split-a">
            <h2>Dịch vụ của Tài Nguyên</h2>
            <ul class="tick">
                <li><?= icon('check', 20) ?> Vận chuyển hành khách bằng xe ghế ngồi và giường nằm</li>
                <li><?= icon('check', 20) ?> Vận chuyển hàng hóa trên mọi tuyến</li>
                <li><?= icon('check', 20) ?> Xe trung chuyển đón và trả khách tận nơi</li>
                <li><?= icon('check', 20) ?> Khách sạn và trạm dừng chân cho chuyến đi dài</li>
            </ul>
        </div>
        <div>
            <p class="lead">Cần đi hoặc cần gửi hàng?</p>
            <p>Đặt vé online chỉ mất vài phút, hoặc gọi trực tiếp cho chúng tôi.</p>
            <div class="hero-actions" style="margin-top:0">
                <a class="btn btn-g" href="DatVe.php">Đặt vé online</a>
                <a class="btn btn-line" href="LienHe.php">Liên hệ</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
