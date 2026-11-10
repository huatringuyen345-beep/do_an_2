(function () {
    'use strict';

    var body = document.body;
    var themeToggle = document.getElementById('themeToggle');
    var themeIcon = document.querySelector('.theme-icon');
    var loginCard = document.getElementById('loginCard');
    var spotlight = document.getElementById('loginSpotlight');
    var loginTab = document.getElementById('loginTab');
    var registerTab = document.getElementById('registerTab');
    var loginPanel = document.getElementById('loginPanel');
    var registerPanel = document.getElementById('registerPanel');
    var tabSlider = document.getElementById('authTabSlider');
    var heading = document.getElementById('authHeading');
    var subheading = document.getElementById('authSubheading');
    var isStaff = body.classList.contains('staff-login');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var nextTheme = body.dataset.theme === 'dark' ? 'light' : 'dark';
            body.dataset.theme = nextTheme;
            themeIcon.textContent = nextTheme === 'dark' ? '☼' : '☾';
        });
    }

    function selectTab(mode, updateUrl) {
        if (!loginTab || !registerTab || !loginPanel || !registerPanel) return;
        var isLogin = mode === 'login';

        loginTab.classList.toggle('is-active', isLogin);
        registerTab.classList.toggle('is-active', !isLogin);
        loginTab.setAttribute('aria-selected', isLogin ? 'true' : 'false');
        registerTab.setAttribute('aria-selected', isLogin ? 'false' : 'true');
        loginPanel.hidden = !isLogin;
        registerPanel.hidden = isLogin;
        loginPanel.classList.toggle('is-active', isLogin);
        registerPanel.classList.toggle('is-active', !isLogin);
        if (tabSlider) tabSlider.classList.toggle('is-register', !isLogin);

        if (heading) heading.textContent = isLogin ? 'Chào mừng trở lại' : 'Tạo tài khoản mới';
        if (subheading) {
            subheading.textContent = isLogin
                ? 'Đăng nhập để đặt vé và quản lý chuyến đi của bạn.'
                : 'Tạo tài khoản để quản lý vé và sử dụng dịch vụ.';
        }

        if (updateUrl && window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            if (isLogin) {
                url.searchParams.delete('mode');
            } else {
                url.searchParams.set('mode', 'register');
            }
            window.history.replaceState(null, '', url);
        }
    }

    if (!isStaff) {
        if (loginTab) loginTab.addEventListener('click', function () { selectTab('login', true); });
        if (registerTab) registerTab.addEventListener('click', function () { selectTab('register', true); });
    }

    document.querySelectorAll('[data-toggle-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.toggleTarget);
            if (!input) return;
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.innerHTML = reveal
                ? '<svg class="icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>'
                : '<svg class="icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
            button.setAttribute('aria-label', reveal ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });

    document.querySelectorAll('.demo-account').forEach(function (button) {
        button.addEventListener('click', function () {
            var email = document.getElementById('loginEmail');
            var password = document.getElementById('loginPassword');
            if (!email || !password) return;
            selectTab('login', true);
            email.value = button.dataset.email || '';
            password.value = button.dataset.password || '';
            email.focus();
        });
    });

    var registerPassword = document.getElementById('registerPassword');
    var registerConfirm = document.getElementById('registerConfirm');
    var strengthLabel = document.getElementById('strengthLabel');
    var passwordMatch = document.getElementById('passwordMatch');
    var strengthColors = ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'];

    function updatePasswordStrength() {
        if (!registerPassword || !strengthLabel) return;
        var value = registerPassword.value;
        var score = 0;
        if (value.length >= 8) score++;
        if (value.length >= 12) score++;
        if (/[A-Z]/.test(value) && /[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        document.querySelectorAll('.strength-meter span').forEach(function (bar, index) {
            bar.style.backgroundColor = index < score ? strengthColors[score - 1] : '';
        });
        var labels = ['Độ mạnh mật khẩu', 'Mật khẩu yếu', 'Mật khẩu trung bình', 'Mật khẩu tốt', 'Mật khẩu rất mạnh'];
        strengthLabel.textContent = value ? labels[score] : labels[0];
        strengthLabel.style.color = value ? (strengthColors[Math.max(0, score - 1)]) : '';
    }

    function updatePasswordMatch() {
        if (!registerPassword || !registerConfirm || !passwordMatch) return;
        if (!registerConfirm.value) {
            passwordMatch.textContent = '';
            passwordMatch.className = 'password-match';
            return;
        }
        var matches = registerPassword.value === registerConfirm.value;
        passwordMatch.textContent = matches ? 'Mật khẩu đã khớp.' : 'Mật khẩu chưa khớp.';
        passwordMatch.className = 'password-match ' + (matches ? 'is-match' : 'is-mismatch');
    }

    if (registerPassword) {
        registerPassword.addEventListener('input', function () {
            updatePasswordStrength();
            updatePasswordMatch();
        });
    }
    if (registerConfirm) registerConfirm.addEventListener('input', updatePasswordMatch);

    document.querySelectorAll('.auth-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) return;
            var submit = form.querySelector('.auth-submit');
            if (submit) {
                submit.classList.add('is-loading');
                submit.disabled = true;
            }
        });
    });

    var forgotLink = document.getElementById('forgotLink');
    var forgotModal = document.getElementById('forgotModal');
    if (forgotLink && forgotModal) {
        forgotLink.addEventListener('click', function (event) {
            event.preventDefault();
            forgotModal.hidden = false;
        });
        forgotModal.querySelectorAll('[data-close-modal]').forEach(function (button) {
            button.addEventListener('click', function () { forgotModal.hidden = true; });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') forgotModal.hidden = true;
        });
    }

    if (spotlight && window.matchMedia('(pointer: fine)').matches) {
        window.addEventListener('pointermove', function (event) {
            spotlight.style.left = event.clientX + 'px';
            spotlight.style.top = event.clientY + 'px';
        }, { passive: true });
    }

    if (loginCard && !isStaff && window.matchMedia('(pointer: fine)').matches) {
        loginCard.addEventListener('pointermove', function (event) {
            var rect = loginCard.getBoundingClientRect();
            var x = (event.clientX - rect.left) / rect.width - .5;
            var y = (event.clientY - rect.top) / rect.height - .5;
            loginCard.style.transform = 'perspective(1000px) rotateX(' + (-y * 2) + 'deg) rotateY(' + (x * 2) + 'deg)';
        });
        loginCard.addEventListener('pointerleave', function () {
            loginCard.style.transform = '';
        });
    }
})();
