/**
 * taotaikhoan.js - Tương tác cho trang đăng nhập, đăng ký và tài khoản
 */
(function () {
    'use strict';

    // 1. Chuyển Tab Đăng nhập / Đăng ký
    var tabLoginBtn = document.getElementById('tabLoginBtn');
    var tabRegisterBtn = document.getElementById('tabRegisterBtn');
    var tabLogin = document.getElementById('tabLogin');
    var tabRegister = document.getElementById('tabRegister');
    var switchToRegister = document.getElementById('switchToRegister');
    var switchToLogin = document.getElementById('switchToLogin');

    function selectTab(mode) {
        if (!tabLogin || !tabRegister) return;
        var isLogin = mode === 'login';

        if (tabLoginBtn) {
            tabLoginBtn.classList.toggle('is-active', isLogin);
            tabLoginBtn.setAttribute('aria-selected', isLogin ? 'true' : 'false');
        }
        if (tabRegisterBtn) {
            tabRegisterBtn.classList.toggle('is-active', !isLogin);
            tabRegisterBtn.setAttribute('aria-selected', isLogin ? 'false' : 'true');
        }

        tabLogin.classList.toggle('is-active', isLogin);
        tabRegister.classList.toggle('is-active', !isLogin);

        // Cập nhật hash trên URL mà không giật trang
        if (history.replaceState) {
            var url = new URL(window.location);
            url.searchParams.set('mode', isLogin ? 'login' : 'register');
            history.replaceState(null, '', url);
        }
    }

    if (tabLoginBtn) tabLoginBtn.addEventListener('click', function () { selectTab('login'); });
    if (tabRegisterBtn) tabRegisterBtn.addEventListener('click', function () { selectTab('register'); });
    if (switchToRegister) switchToRegister.addEventListener('click', function () { selectTab('register'); });
    if (switchToLogin) switchToLogin.addEventListener('click', function () { selectTab('login'); });

    // 2. Ẩn/Hiện mật khẩu (Eye button)
    document.querySelectorAll('.btn-toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-toggle-target');
            var input = document.getElementById(targetId);
            if (!input) return;

            var isPw = input.type === 'password';
            input.type = isPw ? 'text' : 'password';

            var eyeShow = btn.querySelector('.eye-show');
            var eyeHide = btn.querySelector('.eye-hide');
            if (eyeShow && eyeHide) {
                eyeShow.hidden = isPw;
                eyeHide.hidden = !isPw;
            }
        });
    });

    // 3. Tự động điền tài khoản mẫu (Demo chip)
    document.querySelectorAll('.demo-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var email = chip.getAttribute('data-email');
            var pass = chip.getAttribute('data-pass');
            var emailInput = document.getElementById('login-email');
            var passInput = document.getElementById('login-password');

            if (emailInput && passInput) {
                selectTab('login');
                emailInput.value = email;
                passInput.value = pass;

                // Hiệu ứng highlight nhẹ
                emailInput.focus();
                emailInput.classList.add('is-highlight');
                passInput.classList.add('is-highlight');
                setTimeout(function () {
                    emailInput.classList.remove('is-highlight');
                    passInput.classList.remove('is-highlight');
                }, 1000);
            }
        });
    });

    // 4. Kiểm tra khớp mật khẩu đăng ký thời gian thực
    var regPw = document.getElementById('reg-password');
    var regConfirm = document.getElementById('reg-confirm-password');
    var pwHint = document.getElementById('pwMatchHint');

    function checkPwMatch() {
        if (!regPw || !regConfirm || !pwHint) return;
        var p1 = regPw.value;
        var p2 = regConfirm.value;

        if (!p2) {
            pwHint.hidden = true;
            return;
        }

        pwHint.hidden = false;
        if (p1 === p2) {
            pwHint.textContent = 'Mật khẩu xác nhận trùng khớp!';
            pwHint.className = 'pw-match-hint is-ok';
        } else {
            pwHint.textContent = 'Mật khẩu xác nhận chưa khớp.';
            pwHint.className = 'pw-match-hint is-bad';
        }
    }

    if (regPw && regConfirm) {
        regPw.addEventListener('input', checkPwMatch);
        regConfirm.addEventListener('input', checkPwMatch);
    }

    // 5. Modal Quên mật khẩu
    var forgotModal = document.getElementById('forgotModal');
    var forgotLink = document.getElementById('forgotPwLink');
    var closeForgot = document.getElementById('closeForgotModal');
    var confirmForgot = document.getElementById('confirmForgotModal');

    function openModal(modal) {
        if (modal) modal.hidden = false;
    }
    function closeModal(modal) {
        if (modal) modal.hidden = true;
    }

    if (forgotLink) {
        forgotLink.addEventListener('click', function (e) {
            e.preventDefault();
            openModal(forgotModal);
        });
    }
    if (closeForgot) closeForgot.addEventListener('click', function () { closeModal(forgotModal); });
    if (confirmForgot) confirmForgot.addEventListener('click', function () { closeModal(forgotModal); });

    // 6. Modal Xác nhận Đăng xuất ở trang Profile
    var logoutModal = document.getElementById('logoutModal');
    var btnLogoutProfile = document.getElementById('btnLogoutProfile');
    var closeLogout = document.getElementById('closeLogoutModal');
    var cancelLogout = document.getElementById('cancelLogoutBtn');

    if (btnLogoutProfile) {
        btnLogoutProfile.addEventListener('click', function (e) {
            e.preventDefault();
            openModal(logoutModal);
        });
    }
    if (closeLogout) closeLogout.addEventListener('click', function () { closeModal(logoutModal); });
    if (cancelLogout) cancelLogout.addEventListener('click', function () { closeModal(logoutModal); });

    // Đóng modal khi bấm vào phông nền
    document.querySelectorAll('.auth-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function () {
            closeModal(this.closest('.auth-modal'));
        });
    });
})();
