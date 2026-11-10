</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <p class="footer-brand"><?= e($company['ten']) ?></p>
            <p><?= e($company['slogan']) ?>.</p>
            <p class="footer-line"><?= icon('pin', 18) ?> <?= e($company['dia_chi']) ?></p>
            <p class="footer-line"><?= icon('phone', 18) ?> <a href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= e($company['hotline']) ?></a></p>
            <p class="footer-line"><?= icon('mail', 18) ?> <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></p>
        </div>

        <div>
            <h2 class="footer-h">Tuyến xe</h2>
            <ul class="footer-list">
                <?php foreach ($routes as $r): ?>
                    <li><a href="dat-ve.php?tuyen=<?= e($r['slug']) ?>">Bạc Liêu – <?= e($r['ten']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div>
            <h2 class="footer-h">Khách hàng</h2>
            <ul class="footer-list">
                <li><a href="dat-ve.php">Đặt vé online</a></li>
                <li><a href="lich-trinh.php">Lịch trình và giá vé</a></li>
                <li><a href="dich-vu.php">Gửi hàng, xe trung chuyển</a></li>
                <li><a href="ho-tro.php">Thanh toán và hỗ trợ</a></li>
                <li><a href="tin-tuc.php">Tin tức và khuyến mãi</a></li>
                <li><a href="tuyen-dung.php">Tuyển dụng</a></li>
            </ul>
        </div>

        <div>
            <h2 class="footer-h">Giờ làm việc</h2>
            <p><?= e($company['gio_mo']) ?></p>
            <p>Hotline: <a href="tel:<?= preg_replace('/\D/', '', $company['hotline2']) ?>"><?= e($company['hotline2']) ?></a></p>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <p>© <?= date('Y') ?> <?= e($company['ten']) ?>. Đồ án 2 – Ngành Công nghệ thông tin.</p>
    </div>
</footer>

<script src="../assets/js/main.js"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
