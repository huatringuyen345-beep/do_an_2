/* main.js - menu di động, hiệu ứng sơ đồ tuyến, bộ lọc lịch trình, ước tính cước gửi hàng, dropdown tài khoản */
(function () {
    'use strict';

    /* ----- Menu trên điện thoại ----- */
    var toggle = document.querySelector('.nav-toggle');
    var menu = document.getElementById('menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* ----- Menu người dùng (Avatar Dropdown) trên thanh Header ----- */
    var userMenuWrap = document.getElementById('userMenuWrap');
    var userMenuBtn = document.getElementById('userMenuBtn');
    if (userMenuWrap && userMenuBtn) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = userMenuWrap.classList.toggle('is-open');
            userMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!userMenuWrap.contains(e.target)) {
                userMenuWrap.classList.remove('is-open');
                userMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && userMenuWrap.classList.contains('is-open')) {
                userMenuWrap.classList.remove('is-open');
                userMenuBtn.setAttribute('aria-expanded', 'false');
                userMenuBtn.focus();
            }
        });
    }

    /* ----- Sơ đồ tuyến: vẽ các đường khi cuộn tới ----- */
    var fan = document.querySelector('.fan');
    if (fan) {
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) {
                        fan.classList.add('in');
                        io.disconnect();
                    }
                });
            }, { threshold: 0.25 });
            io.observe(fan);
        } else {
            fan.classList.add('in');
        }
    }

    /* ----- Lọc lịch trình theo nhóm + tìm kiếm ----- */
    var list = document.getElementById('routeList');
    if (list) {
        var rows = Array.prototype.slice.call(list.querySelectorAll('.route-row'));
        var chips = document.querySelectorAll('.chip[data-group]');
        var search = document.getElementById('routeSearch');
        var empty = document.getElementById('routeEmpty');
        var group = 'all';

        var normalize = function (s) {
            return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd');
        };

        var apply = function () {
            var q = normalize(search ? search.value.trim() : '');
            var shown = 0;
            rows.forEach(function (r) {
                var okGroup = group === 'all' || r.dataset.group === group;
                var okText = !q || normalize(r.dataset.name).indexOf(q) !== -1;
                r.hidden = !(okGroup && okText);
                if (!r.hidden) shown++;
            });
            if (empty) empty.hidden = shown !== 0;
        };

        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                chips.forEach(function (x) { x.setAttribute('aria-pressed', 'false'); });
                c.setAttribute('aria-pressed', 'true');
                group = c.dataset.group;
                apply();
            });
        });
        if (search) search.addEventListener('input', apply);
    }

    /* ----- Ước tính cước gửi hàng (công thức mẫu, sửa theo bảng giá thật) ----- */
    var est = document.getElementById('estimateForm');
    if (est) {
        var out = document.getElementById('estimateOut');
        var calc = function () {
            var km = parseInt(est.elements['tuyen'].selectedOptions[0].dataset.km, 10) || 0;
            var kg = parseFloat(est.elements['kg'].value) || 0;
            if (kg <= 0) { out.textContent = '0đ'; return; }
            var cost = Math.max(30000, Math.round(km * 20 * kg / 1000) * 1000);
            out.textContent = cost.toLocaleString('vi-VN') + 'đ';
        };
        est.addEventListener('input', calc);
        est.addEventListener('submit', function (e) { e.preventDefault(); calc(); });
        calc();
    }

    /* ----- Hiện ô địa chỉ khi chọn trung chuyển (trang đặt vé) ----- */
    var shuttle = document.getElementById('trung_chuyen');
    var shuttleBox = document.getElementById('shuttleBox');
    if (shuttle && shuttleBox) {
        var sync = function () {
            shuttleBox.classList.toggle('hidden', !shuttle.checked);
        };
        shuttle.addEventListener('change', sync);
        sync();
    }
})();
