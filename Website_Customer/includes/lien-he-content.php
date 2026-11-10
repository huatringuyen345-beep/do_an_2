<section class="page-head">
    <div class="wrap">
        <h1>Liên hệ</h1>
        <p>Gọi điện, ghé văn phòng hoặc gửi tin nhắn cho chúng tôi.</p>
    </div>
</section>

<section class="section">
    <div class="wrap contact-grid">
        <div>
            <h2>Thông tin liên hệ</h2>
            <ul class="contact-list">
                <li>
                    <?= icon('pin', 24) ?>
                    <div>
                        <strong>Văn phòng</strong>
                        <?= e($company['dia_chi']) ?>
                    </div>
                </li>
                <li>
                    <?= icon('phone', 24) ?>
                    <div>
                        <strong>Điện thoại</strong>
                        <a href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= e($company['hotline']) ?></a><br>
                        <a href="tel:<?= preg_replace('/\D/', '', $company['hotline2']) ?>"><?= e($company['hotline2']) ?></a>
                        (hotline)
                    </div>
                </li>
                <li>
                    <?= icon('mail', 24) ?>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a>
                    </div>
                </li>
                <li>
                    <?= icon('clock', 24) ?>
                    <div>
                        <strong>Giờ làm việc</strong>
                        <?= e($company['gio_mo']) ?>
                    </div>
                </li>
            </ul>

            <iframe
                class="map"
                title="Bản đồ Bạc Liêu"
                loading="lazy"
                src="https://www.google.com/maps?q=B%E1%BA%A1c+Li%C3%AAu&amp;output=embed"
            ></iframe>
        </div>

        <div class="panel" id="gui-tin">
            <?php if ($sent): ?>
                <div class="alert alert-ok">
                    <p>
                        <strong>Đã nhận tin nhắn thành công!</strong>
                        Chúng tôi sẽ liên hệ lại với bạn qua số
                        <strong><?= e($sdt) ?></strong> trong thời gian sớm nhất.
                    </p>
                </div>
                <a class="btn btn-line" href="lien-he.php">Gửi tin nhắn khác</a>
            <?php else: ?>
                <h2 class="contact-form-title">Gửi tin nhắn</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-bad" role="alert">
                        <ul>
                            <?php foreach ($errors as $er): ?>
                                <li><?= e($er) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="lien-he.php#gui-tin" novalidate>
                    <div class="form-grid">
                        <div class="field">
                            <label for="ten">Họ và tên</label>
                            <input
                                type="text"
                                id="ten"
                                name="ten"
                                value="<?= old('ten', $account['name'] ?? '') ?>"
                                autocomplete="name"
                                required
                            >
                        </div>
                        <div class="row2">
                            <div class="field">
                                <label for="sdt">Số điện thoại</label>
                                <input
                                    type="tel"
                                    id="sdt"
                                    name="sdt"
                                    value="<?= old('sdt', $account['phone'] ?? '') ?>"
                                    autocomplete="tel"
                                    required
                                >
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= old('email', $account['email'] ?? '') ?>"
                                    autocomplete="email"
                                >
                            </div>
                        </div>
                        <div class="field">
                            <label for="noi_dung">Nội dung liên hệ</label>
                            <textarea
                                id="noi_dung"
                                name="noi_dung"
                                placeholder="Bạn cần tư vấn về tuyến xe, gửi hàng hay thông tin gì khác?"
                                required
                            ><?= old('noi_dung') ?></textarea>
                        </div>
                        <button class="btn btn-g" type="submit">Gửi tin nhắn</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
