<?php
/*
 * Trang đăng nhập chung và đăng ký khách hàng.
 * Tham số area=staff giới hạn trang ở chế độ đăng nhập nhân viên, không đăng ký.
 * Logic xác thực được xử lý trong backend/controllers/account.php.
 */

require_once __DIR__ . '/../backend/includes/config.php';

$staffLogin = (($_GET['area'] ?? $_POST['area'] ?? '') === 'staff');
$authNext = customer_auth_next($_POST['next'] ?? $_GET['next'] ?? '');
$activeForm = $_POST['action'] ?? $_GET['mode'] ?? 'login';
if (!in_array($activeForm, ['login', 'register'], true) || $staffLogin) {
    $activeForm = 'login';
}

if (in_array($_SESSION['account']['role'] ?? '', ['quan_tri', 'nhan_vien'], true)) {
    header('Location: ../../Website_Manage/admin/bookings.php', true, 302);
    exit;
}
if (!empty($_SESSION['account']['id']) && !$staffLogin) {
    header('Location: tai-khoan.php', true, 302);
    exit;
}

require_once __DIR__ . '/../backend/controllers/account.php';

$pageTitle = $staffLogin ? 'Đăng nhập nhân viên' : ($activeForm === 'register' ? 'Tạo tài khoản khách hàng' : 'Đăng nhập tài khoản');
$pageDesc = $staffLogin
    ? 'Đăng nhập để truy cập khu vực quản lý của nhân viên Tài Nguyên.'
    : 'Đăng nhập hoặc tạo tài khoản để sử dụng các dịch vụ của Tài Nguyên.';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Lexend:wght@500;600;700&display=swap&subset=vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js" defer></script>
</head>
<body data-theme="dark" class="<?= $staffLogin ? 'staff-login' : '' ?>">
    <div class="login-orb login-orb-one" aria-hidden="true"></div>
    <div class="login-orb login-orb-two" aria-hidden="true"></div>
    <div class="login-orb login-orb-three" aria-hidden="true"></div>
    <div class="login-spotlight" id="loginSpotlight" aria-hidden="true"></div>

    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Chuyển chế độ sáng hoặc tối" title="Chuyển chế độ sáng hoặc tối">
        <span class="theme-icon" aria-hidden="true">☼</span>
        <span class="theme-label">Giao diện</span>
    </button>

    <main class="login-layout">
        <a class="login-brand" href="index.php" aria-label="<?= e(SITE_NAME) ?> - Trang chủ">
            <span class="login-brand-mark"><?= icon('bus', 28) ?></span>
            <span><strong><?= e(SITE_NAME) ?></strong><small>VẬN TẢI BẠC LIÊU</small></span>
        </a>

        <?php if (!empty($successMessage)): ?>
            <div class="login-alert login-alert-success" role="status"><?= e($successMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="login-alert login-alert-error" role="alert">
                <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="login-alert login-alert-error" role="alert"><?= e($errorMessage) ?></div>
        <?php endif; ?>

        <section class="login-card" id="loginCard" aria-labelledby="authHeading">
            <div class="login-card-glow" aria-hidden="true"></div>
            <header class="login-card-header">
                <span class="login-icon"><?= icon('bus', 30) ?></span>
                <p class="login-eyebrow"><?= $staffLogin ? 'CỔNG NHÂN VIÊN' : 'TÀI KHOẢN TÀI NGUYÊN' ?></p>
                <h1 id="authHeading"><?= $staffLogin ? 'Chào mừng trở lại' : ($activeForm === 'register' ? 'Tạo tài khoản mới' : 'Chào mừng trở lại') ?></h1>
                <p id="authSubheading"><?= $staffLogin
                    ? 'Đăng nhập để quản lý hoạt động vận tải.'
                    : ($activeForm === 'register' ? 'Tạo tài khoản để quản lý vé và sử dụng dịch vụ.' : 'Đăng nhập để đặt vé và quản lý chuyến đi của bạn.') ?></p>
            </header>

            <?php if (!$staffLogin): ?>
                <div class="auth-tabs" role="tablist" aria-label="Đăng nhập hoặc tạo tài khoản">
                    <span class="auth-tab-slider<?= $activeForm === 'register' ? ' is-register' : '' ?>" id="authTabSlider" aria-hidden="true"></span>
                    <button class="auth-tab<?= $activeForm === 'login' ? ' is-active' : '' ?>" id="loginTab" type="button" role="tab" aria-selected="<?= $activeForm === 'login' ? 'true' : 'false' ?>" aria-controls="loginPanel">
                        <?= icon('user', 17) ?> Đăng nhập
                    </button>
                    <button class="auth-tab<?= $activeForm === 'register' ? ' is-active' : '' ?>" id="registerTab" type="button" role="tab" aria-selected="<?= $activeForm === 'register' ? 'true' : 'false' ?>" aria-controls="registerPanel">
                        <?= icon('edit', 17) ?> Tạo tài khoản
                    </button>
                </div>
            <?php endif; ?>

            <div class="auth-forms">
                <section class="auth-panel<?= $activeForm === 'login' ? ' is-active' : '' ?>" id="loginPanel" role="tabpanel" aria-labelledby="<?= $staffLogin ? 'authHeading' : 'loginTab' ?>"<?= $activeForm === 'login' ? '' : ' hidden' ?>>
                    <form method="post" action="login.php<?= $staffLogin ? '?area=staff' : '' ?>" class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="area" value="<?= $staffLogin ? 'staff' : 'customer' ?>">
                        <input type="hidden" name="next" value="<?= e($authNext) ?>">

                        <div class="auth-field">
                            <label for="loginEmail">Email</label>
                            <div class="auth-input-wrap">
                                <span class="field-icon"><?= icon('mail', 19) ?></span>
                                <input type="email" id="loginEmail" name="email" class="auth-input" placeholder="<?= $staffLogin ? 'Email nhân viên' : 'tenban@example.com' ?>" autocomplete="username" maxlength="150" required value="<?= old('email') ?>">
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="loginPassword">Mật khẩu</label>
                            <div class="auth-input-wrap">
                                <span class="field-icon"><?= icon('lock', 19) ?></span>
                                <input type="password" id="loginPassword" name="password" class="auth-input" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
                                <button class="password-toggle" type="button" data-toggle-target="loginPassword" aria-label="Hiện mật khẩu"><?= icon('eye', 19) ?></button>
                            </div>
                        </div>

                        <div class="auth-form-meta">
                            <label class="remember-option"><input type="checkbox" name="remember" value="1"><span>Ghi nhớ tôi</span></label>
                            <?php if (!$staffLogin): ?><a class="forgot-link" href="#quen-mat-khau" id="forgotLink">Quên mật khẩu?</a><?php endif; ?>
                        </div>

                        <button class="auth-submit" type="submit">
                            <span class="submit-label"><?= $staffLogin ? 'Đăng nhập khu quản lý' : 'Đăng nhập' ?> <?= icon('chevron-down', 17) ?></span>
                            <span class="submit-spinner" aria-hidden="true"></span>
                        </button>
                    </form>

                    <?php if (!$staffLogin): ?>
                        <div class="demo-accounts">
                            <strong>Tài khoản dùng thử</strong>
                            <p>Tạo tài khoản khách hàng tại đây. Tài khoản nhân viên và quản trị cần được cấp riêng trên môi trường local.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if (!$staffLogin): ?>
                    <section class="auth-panel<?= $activeForm === 'register' ? ' is-active' : '' ?>" id="registerPanel" role="tabpanel" aria-labelledby="registerTab"<?= $activeForm === 'register' ? '' : ' hidden' ?>>
                        <form method="post" action="login.php?mode=register" class="auth-form" id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="next" value="<?= e($authNext) ?>">

                            <div class="auth-field">
                                <label for="registerName">Họ và tên</label>
                                <div class="auth-input-wrap">
                                    <span class="field-icon"><?= icon('user', 19) ?></span>
                                    <input type="text" id="registerName" name="name" class="auth-input" placeholder="Nguyễn Văn A" autocomplete="name" maxlength="100" required value="<?= old('name') ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label for="registerPhone">Số điện thoại <span class="optional">(không bắt buộc)</span></label>
                                <div class="auth-input-wrap">
                                    <span class="field-icon"><?= icon('phone', 19) ?></span>
                                    <input type="tel" id="registerPhone" name="phone" class="auth-input" placeholder="0912345678" autocomplete="tel" value="<?= old('phone') ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label for="registerEmail">Email</label>
                                <div class="auth-input-wrap">
                                    <span class="field-icon"><?= icon('mail', 19) ?></span>
                                    <input type="email" id="registerEmail" name="email" class="auth-input" placeholder="tenban@example.com" autocomplete="email" maxlength="150" required value="<?= old('email') ?>">
                                </div>
                            </div>
                            <div class="auth-field">
                                <label for="registerPassword">Mật khẩu</label>
                                <div class="auth-input-wrap">
                                    <span class="field-icon"><?= icon('lock', 19) ?></span>
                                    <input type="password" id="registerPassword" name="password" class="auth-input" placeholder="Ít nhất 8 ký tự" autocomplete="new-password" minlength="8" required>
                                    <button class="password-toggle" type="button" data-toggle-target="registerPassword" aria-label="Hiện mật khẩu"><?= icon('eye', 19) ?></button>
                                </div>
                                <div class="strength-meter" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                                <span class="strength-label" id="strengthLabel" aria-live="polite">Độ mạnh mật khẩu</span>
                            </div>
                            <div class="auth-field">
                                <label for="registerConfirm">Xác nhận mật khẩu</label>
                                <div class="auth-input-wrap">
                                    <span class="field-icon"><?= icon('shield-check', 19) ?></span>
                                    <input type="password" id="registerConfirm" name="confirm_password" class="auth-input" placeholder="Nhập lại mật khẩu" autocomplete="new-password" minlength="8" required>
                                    <button class="password-toggle" type="button" data-toggle-target="registerConfirm" aria-label="Hiện mật khẩu"><?= icon('eye', 19) ?></button>
                                </div>
                                <span class="password-match" id="passwordMatch" aria-live="polite"></span>
                            </div>
                            <label class="terms-option">
                                <input type="checkbox" name="terms" value="1" required checked>
                                <span>Tôi đồng ý với Điều khoản dịch vụ và Chính sách bảo mật.</span>
                            </label>
                            <button class="auth-submit" type="submit">
                                <span class="submit-label">Tạo tài khoản <?= icon('check', 17) ?></span>
                                <span class="submit-spinner" aria-hidden="true"></span>
                            </button>
                        </form>
                    </section>
                <?php endif; ?>
            </div>

            <p class="auth-back"><a href="index.php">← Quay lại trang chủ</a></p>
        </section>
        <p class="login-footer">© <?= date('Y') ?> <?= e($company['ten']) ?></p>
    </main>

    <?php if (!$staffLogin): ?>
        <div class="auth-modal" id="forgotModal" hidden>
            <div class="modal-backdrop" data-close-modal></div>
            <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="forgotHeading">
                <button class="modal-close" type="button" data-close-modal aria-label="Đóng">×</button>
                <h2 id="forgotHeading">Quên mật khẩu?</h2>
                <p>Vui lòng liên hệ hotline để được nhân viên hỗ trợ cấp lại mật khẩu.</p>
                <a class="modal-hotline" href="tel:<?= preg_replace('/\D/', '', $company['hotline']) ?>"><?= e($company['hotline']) ?></a>
                <p class="modal-hours">Giờ làm việc: <?= e($company['gio_mo']) ?></p>
            </section>
        </div>
    <?php endif; ?>
</body>
</html>
