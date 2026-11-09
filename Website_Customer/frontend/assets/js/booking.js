/* booking.js - đổi giờ/giá/sơ đồ chỗ ngay trên trang đặt vé (không cần tải lại) */
(function () {
    'use strict';

    var ROUTES = window.TN_ROUTES;
    var INIT = window.TN_INIT || {};
    var form = document.getElementById('bookForm');
    if (!ROUTES || !form) return;

    var el = {
        tuyen: form.elements['tuyen'],
        ngay: form.elements['ngay'],
        gio: form.elements['gio'],
        loai: form.elements['loai'],
        ghe: document.getElementById('ghe'),
        map: document.getElementById('seatmap'),
        hint: document.getElementById('loaiHint'),
        err: document.getElementById('seatError'),
        sRoute: document.getElementById('sumRoute'),
        sDate: document.getElementById('sumDate'),
        sTime: document.getElementById('sumTime'),
        sType: document.getElementById('sumType'),
        sSeats: document.getElementById('sumSeats'),
        sPrice: document.getElementById('sumPrice'),
        sTotal: document.getElementById('sumTotal')
    };

    var selected = new Set(INIT.seats ? INIT.seats.split(',').filter(Boolean) : []);
    var lastContext = '';

    function money(n) { return Number(n).toLocaleString('vi-VN') + 'đ'; }
    function chieu() {
        var c = form.querySelector('input[name="chieu"]:checked');
        return c ? c.value : 'di';
    }
    function route() { return ROUTES[el.tuyen.value]; }

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    /* Cập nhật danh sách giờ + loại xe khi đổi tuyến */
    function refreshRoute() {
        var r = route();
        var keep = el.gio.value;
        el.gio.innerHTML = '';
        r.gio.forEach(function (t) {
            var o = document.createElement('option');
            o.value = t; o.textContent = t;
            el.gio.appendChild(o);
        });
        if (r.gio.indexOf(keep) !== -1) el.gio.value = keep;

        var optGhe = el.loai.querySelector('option[value="ghe"]');
        var optGiuong = el.loai.querySelector('option[value="giuong"]');
        optGhe.disabled = !r.ghe;
        optGiuong.disabled = !r.giuong;
        if (el.loai.selectedOptions[0].disabled) el.loai.value = r.giuong ? 'giuong' : 'ghe';

        el.hint.textContent = (!r.giuong) ? 'Tuyến này chỉ có xe ghế ngồi.' : '';
    }

    function buildSeat(id) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'seat';
        b.dataset.id = id;
        b.textContent = id.replace(/^[A-Z]/, '');
        b.setAttribute('aria-label', 'Chỗ ' + id);
        b.setAttribute('aria-pressed', selected.has(id) ? 'true' : 'false');
        return b;
    }
    function gap() { var s = document.createElement('span'); s.className = 'gap'; s.setAttribute('aria-hidden', 'true'); return s; }

    function renderSeats() {
        var r = route();
        var type = el.loai.value;
        var context = [el.tuyen.value, el.ngay.value, el.gio.value, type, chieu()].join('|');
        if (context !== lastContext && lastContext !== '') selected.clear();
        lastContext = context;

        el.map.innerHTML = '';

        if (type === 'ghe') {
            // 8 hàng x 4 chỗ (2 - lối đi - 2) = 32 chỗ
            var deck = document.createElement('div'); deck.className = 'deck';
            deck.innerHTML = '<h3>Xe ghế ngồi (32 chỗ)</h3>';
            var grid = document.createElement('div'); grid.className = 'deck-grid sit';
            var n = 1;
            for (var row = 0; row < 8; row++) {
                for (var col = 0; col < 4; col++) {
                    if (col === 2) grid.appendChild(gap());
                    grid.appendChild(buildSeat('G' + pad(n)));
                    n++;
                }
            }
            deck.appendChild(grid);
            el.map.appendChild(deck);
        } else {
            // 2 tầng, mỗi tầng 6 hàng x 3 giường (1 - lối đi - 2) = 36 giường
            [['D', 'Tầng dưới'], ['T', 'Tầng trên']].forEach(function (f) {
                var deck = document.createElement('div'); deck.className = 'deck';
                deck.innerHTML = '<h3>' + f[1] + '</h3>';
                var grid = document.createElement('div'); grid.className = 'deck-grid bed';
                var n = 1;
                for (var row = 0; row < 6; row++) {
                    for (var col = 0; col < 3; col++) {
                        if (col === 1) grid.appendChild(gap());
                        grid.appendChild(buildSeat(f[0] + pad(n)));
                        n++;
                    }
                }
                deck.appendChild(grid);
                el.map.appendChild(deck);
            });
        }
        update();
    }

    function update() {
        var r = route();
        var type = el.loai.value;
        var price = r[type] || 0;
        var list = Array.from(selected).sort();

        el.ghe.value = list.join(',');
        el.sRoute.textContent = chieu() === 've' ? r.ten + ' – Bạc Liêu' : 'Bạc Liêu – ' + r.ten;
        var d = el.ngay.value ? el.ngay.value.split('-').reverse().join('/') : '—';
        el.sDate.textContent = d;
        el.sTime.textContent = el.gio.value || '—';
        el.sType.textContent = type === 'ghe' ? 'Ghế ngồi' : 'Giường nằm';
        el.sSeats.textContent = list.length ? list.join(', ') : 'Chưa chọn';
        el.sPrice.textContent = money(price);
        el.sTotal.textContent = money(price * list.length);
        if (list.length) el.err.hidden = true;
    }

    /* Bấm chọn / bỏ chọn chỗ */
    el.map.addEventListener('click', function (e) {
        var b = e.target.closest('.seat');
        if (!b || b.disabled) return;
        var id = b.dataset.id;
        if (selected.has(id)) {
            selected.delete(id);
            b.setAttribute('aria-pressed', 'false');
        } else {
            if (selected.size >= 10) {
                el.err.textContent = 'Mỗi lần đặt tối đa 10 chỗ.';
                el.err.hidden = false;
                return;
            }
            selected.add(id);
            b.setAttribute('aria-pressed', 'true');
        }
        update();
    });

    el.tuyen.addEventListener('change', function () { refreshRoute(); renderSeats(); });
    [el.ngay, el.gio, el.loai].forEach(function (x) { x.addEventListener('change', renderSeats); });
    form.querySelectorAll('input[name="chieu"]').forEach(function (x) { x.addEventListener('change', renderSeats); });

    /* Chặn gửi form khi chưa chọn chỗ */
    form.addEventListener('submit', function (e) {
        if (selected.size !== INIT.want) {
            e.preventDefault();
            el.err.textContent = selected.size < INIT.want
                ? 'Vui lòng chọn đủ ' + INIT.want + ' chỗ theo số hành khách.'
                : 'Bạn đã chọn nhiều hơn số hành khách. Vui lòng bỏ bớt chỗ.';
            el.err.hidden = false;
            el.map.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    refreshRoute();
    renderSeats();

    /* Nếu đến từ trang chủ với số khách > 1, nhắc người dùng chọn đủ số chỗ */
    if (INIT.want > 1 && selected.size === 0) {
        el.hint.textContent = (el.hint.textContent + ' Bạn dự định đi ' + INIT.want + ' người, hãy chọn ' + INIT.want + ' chỗ.').trim();
    }
})();
