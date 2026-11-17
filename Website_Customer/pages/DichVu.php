<?php
require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/includes/data.php';
$pageTitle = 'Dịch vụ';
$pageDesc  = 'Dịch vụ của Tài Nguyên: vận chuyển hàng hóa, xe trung chuyển, khách sạn và trạm dừng chân.';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Dịch vụ tiện ích Tài Nguyên</h1>
        <p>Không chỉ đưa đón hành khách an toàn, chúng tôi cung cấp giải pháp vận chuyển hàng hóa nhanh, xe trung chuyển tận nơi và trạm dừng chân tiện nghi.</p>
    </div>
</section>

<div class="wrap">

    <!-- Vận chuyển hàng hóa -->
    <section class="service-block split" id="hang-hoa">
        <div>
            <?= icon('box', 40) ?>
            <h2>Vận chuyển hàng hóa hỏa tốc</h2>
            <p>Hàng hóa được gửi theo các chuyến xe khách chạy liên tục trong ngày giữa Bạc Liêu và các tỉnh. Phù hợp cho bưu kiện nhỏ, nông sản tươi sống, hàng tiêu dùng và tài liệu cần giao nhận trong ngày.</p>
            <h3>Quy trình gửi hàng đơn giản:</h3>
            <ol class="steps">
                <li><span>Mang hàng trực tiếp ra văn phòng Tài Nguyên hoặc yêu cầu xe trung chuyển đến lấy tận nhà.</span></li>
                <li><span>Nhân viên kiểm tra, cân hàng, tính cước minh bạch và phát phiếu biên nhận gửi hàng.</span></li>
                <li><span>Người nhận xuất trình biên nhận hoặc đọc số điện thoại để nhận hàng an toàn tại điểm đến.</span></li>
            </ol>
            <a class="btn btn-y" href="DatDichVu.php?service=van_chuyen_hang">
                <?= icon('box', 18) ?> <span>Đặt dịch vụ gửi hàng online</span>
            </a>
        </div>

        <div class="panel" style="box-shadow:var(--shadow-md);">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <?= icon('tag', 20) ?> <span>Công cụ tính cước nhanh</span>
            </h3>
            <p class="hint" style="margin-bottom:16px;">Chọn tuyến đường và khối lượng để xem cước vận chuyển dự kiến.</p>

            <form id="estimateForm">
                <div class="form-grid">
                    <div class="field">
                        <label for="e-tuyen">
                            <?= icon('pin', 16) ?> Tuyến vận chuyển
                        </label>
                        <select id="e-tuyen" name="tuyen">
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= e($r['slug']) ?>" data-km="<?= (int)$r['km'] ?>">
                                    <?= e($r['ten']) ?> (<?= (int)$r['km'] ?> km)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="e-kg">
                            <?= icon('tag', 16) ?> Khối lượng ước tính (kg)
                        </label>
                        <input type="number" id="e-kg" name="kg" value="10" min="1" max="500">
                    </div>
                </div>
            </form>

            <div class="estimate">
                <span>Cước vận chuyển ước tính:</span>
                <strong id="estimateOut">0đ</strong>
            </div>
            <p class="muted" style="margin:14px 0 0; font-size:0.86rem; text-align:center;">
                * Cước phí mang tính tham khảo. Cước chính thức phụ thuộc vào kích thước thực tế khi nhân viên cân nhận.
            </p>
        </div>
    </section>

    <!-- Xe trung chuyển -->
    <section class="service-block split split-rev" id="trung-chuyen">
        <div class="split-a">
            <?= icon('car', 40) ?>
            <h2>Xe trung chuyển đưa đón tận nơi</h2>
            <p>Quý khách không cần tự chạy xe ra bến xe đông đúc. Đội xe trung chuyển của Tài Nguyên sẽ liên hệ và đón tận cửa nhà trong nội ô TP. Bạc Liêu, đưa thẳng ra bến trước giờ xe lớn xuất phát.</p>
            <p>Khi đặt vé trực tuyến, chỉ cần tích chọn <strong>"Đăng ký xe trung chuyển đón tại nhà"</strong> và nhập địa chỉ cụ thể. Tài xế sẽ gọi hẹn giờ đón chính xác.</p>
            <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                <a class="btn btn-g" href="DatVe.php">
                    <?= icon('ticket', 18) ?> <span>Đặt vé có đón tận nhà</span>
                </a>
                <a class="btn btn-line" href="DatDichVu.php?service=trung_chuyen">
                    <span>Đăng ký trung chuyển riêng</span>
                </a>
            </div>
        </div>
        <div class="panel panel-mist">
            <h3>Lợi ích của xe trung chuyển Tài Nguyên</h3>
            <ul class="tick">
                <li><?= icon('check-circle', 20) ?> <span><strong>Hoàn toàn miễn phí</strong> cho hành khách đi các tuyến liên tỉnh</span></li>
                <li><?= icon('check-circle', 20) ?> <span>Đón trả tận nhà trong bán kính nội ô TP. Bạc Liêu</span></li>
                <li><?= icon('check-circle', 20) ?> <span>Tài xế nhã nhặn, hỗ trợ bốc xếp hành lý cẩn thận</span></li>
                <li><?= icon('check-circle', 20) ?> <span>Chủ động thời gian, không lo trễ giờ xe xuất bến</span></li>
            </ul>
        </div>
    </section>

    <!-- Trạm dừng chân -->
    <section class="service-block split" id="tram-dung-chan">
        <div>
            <?= icon('coffee', 40) ?>
            <h2>Trạm dừng chân tiện nghi</h2>
            <p>Trên những hành trình dài từ Bạc Liêu đi TP. Hồ Chí Minh hay miền Trung, xe Tài Nguyên luôn dừng nghỉ tại các trạm dừng chân đạt chuẩn vệ sinh an toàn thực phẩm.</p>
            <p>Hành khách có thời gian nghỉ ngơi 20–30 phút, thưởng thức đặc sản địa phương, đồ ăn nóng sốt và khu vệ sinh sạch sẽ, thoáng mát.</p>
        </div>
        <div class="panel">
            <h3>Tiêu chuẩn phục vụ tại trạm</h3>
            <ul class="tick">
                <li><?= icon('check-circle', 20) ?> <span>Khu vực vệ sinh rộng rãi, được lau dọn thường xuyên</span></li>
                <li><?= icon('check-circle', 20) ?> <span>Khu vực ăn uống niêm yết giá công khai, không chèo kéo</span></li>
                <li><?= icon('check-circle', 20) ?> <span>Quầy đặc sản miền Tây làm quà phong phú, rõ nguồn gốc</span></li>
            </ul>
        </div>
    </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
