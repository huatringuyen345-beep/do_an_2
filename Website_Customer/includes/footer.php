</main>

<footer class="site-footer" id="footer">
    <div class="wrap footer-grid">
        <div>
            <div class="footer-brand">
                <strong><?= e(SITE_NAME) ?> <span>BUS LINES</span></strong>
            </div>
            <p class="footer-desc"><?= e($company['slogan']) ?>.</p>
            <div class="footer-line">
                <?= icon('pin', 18) ?>
                <span>Văn phòng Bạc Liêu: <?= e($company['dia_chi']) ?></span>
            </div>
            <div class="footer-line">
                <?= icon('phone', 18) ?>
                <span>Tổng đài đặt vé: <a href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><strong><?= e($company['hotline']) ?></strong></a></span>
            </div>
            <div class="footer-line">
                <?= icon('mail', 18) ?>
                <span>Hộp thư: <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></span>
            </div>
        </div>

        <div>
            <h2 class="footer-h">Tuyến Xe Chính</h2>
            <ul class="footer-list">
                <?php foreach ($routes as $r): ?>
                    <li><a href="DatVe.php?tuyen=<?= e($r['slug']) ?>">Xe Bạc Liêu đi <?= e($r['ten']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div>
            <h2 class="footer-h">Hỗ Trợ Khách Hàng</h2>
            <ul class="footer-list">
                <li><a href="TaiKhoan.php?tab=ve">Tra cứu mã vé trực tuyến</a></li>
                <li><a href="DatVe.php">Đặt vé online</a></li>
                <li><a href="LichTrinh.php">Lịch trình và giá vé</a></li>
                <li><a href="DichVu.php">Gửi hàng Express & Trung chuyển</a></li>
                <li><a href="HoTro.php#doi-huy">Chính sách đổi trả & hoàn tiền</a></li>
                <li><a href="HoTro.php#cau-hoi">Câu hỏi thường gặp (FAQ)</a></li>
                <li><a href="TinTuc.php">Tin tức & Khuyến mãi</a></li>
                <li><a href="TuyenDung.php">Cơ hội việc làm & Tuyển dụng</a></li>
                <li><a href="LienHe.php">Liên hệ & Góp ý</a></li>
            </ul>
        </div>

        <div>
            <h2 class="footer-h">Giờ Làm Việc</h2>
            <p><?= e($company['gio_mo']) ?></p>
            <p style="margin-top:12px;">Hotline gửi hàng Express:<br>
                <a href="tel:<?= preg_replace('/\D/', '', $company['hotline2']) ?>" style="font-weight:700; color:#fbbf24; font-size:1.05rem;">
                    <?= e($company['hotline2']) ?>
                </a>
            </p>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <p>© <?= date('Y') ?> <?= e($company['ten']) ?>. Đồ án 2 – Ngành Công nghệ thông tin.</p>
        <div style="display:flex; gap:18px;">
            <a href="GioiThieu.php">Giới thiệu</a>
            <a href="LienHe.php">Chính sách & Điều khoản</a>
            <a href="LichTrinh.php">Lịch trình</a>
        </div>
    </div>
</footer>

<script src="../assets/js/main.js"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
