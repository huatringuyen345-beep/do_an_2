<?php
require_once __DIR__ . '/../backend/bootstrap.php';
require_once __DIR__ . '/../backend/controllers/ThanhToanController.php';

$pageTitle = 'Thanh toán đặt vé';
$pageDesc = 'Thanh toán mô phỏng cho đơn đặt vé của khách hàng Tài Nguyên.';
include __DIR__ . '/../includes/header.php';

$orderStatusLabels = [
    'cho_xac_nhan'   => ['label' => 'Chờ xác nhận', 'class' => 'badge-nhan_vien'],
    'da_xac_nhan'    => ['label' => 'Đã xác nhận chỗ', 'class' => 'badge-khach_hang'],
    'da_thanh_toan'  => ['label' => 'Đã thanh toán', 'class' => 'badge-khach_hang'],
    'da_huy'         => ['label' => 'Đã hủy', 'class' => 'badge-quan_tri'],
];
$paymentStatusLabels = [
    'cho_thanh_toan' => ['label' => 'Chờ thanh toán', 'class' => 'badge-quan_tri'],
    'thanh_cong' => ['label' => 'Thành công', 'class' => 'badge-khach_hang'],
    'that_bai' => ['label' => 'Chưa thanh toán', 'class' => 'badge-quan_tri'],
    'hoan_tien' => ['label' => 'Đã hoàn tiền', 'class' => 'badge-nhan_vien'],
];
$paymentMethodSelection = [
    'tien_mat' => 'quay',
    'chuyen_khoan' => 'ck',
    'the_ngan_hang' => 'the',
    'vi_dien_tu' => 'vi',
][$paymentData['phuong_thuc'] ?? 'quay'] ?? ($paymentData['phuong_thuc'] ?? 'quay');
$alreadyPaid = ($paymentData['payment_status'] ?? '') === 'thanh_cong';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Thanh toán đặt vé trực tuyến</h1>
        <p>Kiểm tra đơn vé và chạy giao dịch mô phỏng. Không có khoản tiền thật được thu.</p>
    </div>
</section>

<section class="section">
    <div class="wrap" style="max-width: 780px;">
        <?php if (!empty($paymentErrorMessage)): ?>
            <div class="alert alert-bad" role="alert">
                <p style="font-weight:700; display:flex; align-items:center; gap:8px;">
                    <?= icon('alert-circle', 20) ?> <span><?= e($paymentErrorMessage) ?></span>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($paymentData): ?>
            <div class="ticket ticket-ok">
                <div class="ticket-head" style="text-align:center;">
                    <span class="user-role-badge badge-nhan_vien" style="font-size:0.82rem; margin-bottom:8px;">
                        <?= icon('shield-check', 16) ?> Giao dịch mô phỏng
                    </span>
                    <p style="margin:8px 0 2px;">Mã đơn đặt vé</p>
                    <p class="code"><?= e($paymentData['ma_dat_ve']) ?></p>
                </div>

                <div class="ticket-body">
                    <dl class="kv">
                        <div>
                            <dt><?= icon('user', 18) ?> Khách hàng</dt>
                            <dd><strong><?= e($paymentData['ten_khach_hang']) ?></strong></dd>
                        </div>
                        <div>
                            <dt><?= icon('phone', 18) ?> Số điện thoại</dt>
                            <dd><?= e($paymentData['sdt_khach_hang']) ?></dd>
                        </div>
                        <div>
                            <dt>Trạng thái đơn</dt>
                            <dd>
                                <span class="user-role-badge <?= $orderStatusLabels[$paymentData['trang_thai']]['class'] ?? 'badge-nhan_vien' ?>">
                                    <?= e($orderStatusLabels[$paymentData['trang_thai']]['label'] ?? $paymentData['trang_thai']) ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt><?= icon('card', 18) ?> Trạng thái thanh toán</dt>
                            <dd>
                                <span class="user-role-badge <?= $paymentStatusLabels[$paymentData['payment_status']]['class'] ?? 'badge-quan_tri' ?>">
                                    <?= e($paymentStatusLabels[$paymentData['payment_status']]['label'] ?? 'Chưa thanh toán') ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt>Tổng số tiền</dt>
                            <dd style="font-size:1.3rem; color:var(--color-primary); font-weight:800;">
                                <?= money((float)$paymentData['thanh_tien']) ?>
                            </dd>
                        </div>
                    </dl>

                    <p class="alert alert-bad" role="note" style="margin-top:18px;">
                        <?= icon('alert-circle', 18) ?> Đây là giao dịch thử nghiệm, không chuyển tiền thật.
                    </p>

                    <?php if (!$alreadyPaid): ?>
                        <form method="post" action="ThanhToan.php" class="auth-form" style="margin-top:18px;">
                            <input type="hidden" name="order" value="<?= e($paymentData['ma_dat_ve']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <div class="field">
                                <label for="phuong_thuc"><?= icon('wallet', 18) ?> Phương thức thanh toán mô phỏng</label>
                                <select id="phuong_thuc" name="phuong_thuc">
                                    <option value="quay"<?= $paymentMethodSelection === 'quay' ? ' selected' : '' ?>>Thanh toán tại quầy</option>
                                    <option value="ck"<?= $paymentMethodSelection === 'ck' ? ' selected' : '' ?>>Chuyển khoản ngân hàng</option>
                                    <option value="the"<?= $paymentMethodSelection === 'the' ? ' selected' : '' ?>>Thẻ ATM / Visa / Mastercard</option>
                                    <option value="vi"<?= $paymentMethodSelection === 'vi' ? ' selected' : '' ?>>Ví điện tử (MoMo, ZaloPay)</option>
                                </select>
                            </div>
                            <div class="form-actions" style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                                <button class="btn btn-y" type="submit" name="action" value="confirm">
                                    <?= icon('check-circle', 18) ?> Xác nhận thanh toán mô phỏng
                                </button>
                                <button class="btn btn-line" type="submit" name="action" value="cancel">
                                    <?= icon('close', 18) ?> Hủy thanh toán
                                </button>
                                <a class="btn btn-line" href="TaiKhoan.php?tab=ve">
                                    <?= icon('ticket', 18) ?> Vé của tôi
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="alert alert-ok" role="status" style="margin-top:18px;">
                            <?= icon('check-circle', 18) ?> Đơn này đã thanh toán thành công.
                        </p>
                        <a class="btn btn-line" href="TaiKhoan.php?tab=ve"><?= icon('ticket', 18) ?> Vé của tôi</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
