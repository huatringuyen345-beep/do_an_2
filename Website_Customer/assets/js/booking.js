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
        availability: document.getElementById('seatAvailability'),
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
    var taken = Array.isArray(INIT.taken) ? INIT.taken : [];
    var capacity = Number(INIT.capacity || 0);
    var booked = Number(INIT.booked || 0);
    var available = Number(INIT.available || 0);
    var tripStatus = INIT.status || '';
    var lastContext = '';
    var availabilityRequest = 0;
    var availabilityReady = capacity > 0 && ['mo_ban', 'het_cho'].indexOf(tripStatus) !== -1;

    function money(n) { return Number(n).toLocaleString('vi-VN') + 'đ'; }
    function chieu() {
        var c = form.querySelector('input[name="chieu"]:checked');
        return c ? c.value : 'di';
    }
    function route() { return ROUTES[el.tuyen.value]; }

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function updateAvailabilityLabel() {
        if (capacity < 1) {
            el.availability.textContent = 'Chưa xác định được sức chứa xe.';
            return;
        }
        if (tripStatus !== 'mo_ban' && tripStatus !== 'het_cho') {
            el.availability.textContent = 'Chuyến xe không còn mở bán.';
            return;
        }
        el.availability.textContent = 'Đã đặt ' + booked + ' / ' + capacity + ' chỗ · Còn trống ' + available + ' chỗ.';
    }

    function setSubmitEnabled(enabled) {
        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = !enabled;
        });
    }

    function loadAvailability() {
        var requestId = ++availabilityRequest;
        var params = new URLSearchParams({
            route: el.tuyen.value,
            date: el.ngay.value,
            time: el.gio.value,
            type: el.loai.value
        });

        selected.clear();
        availabilityReady = false;
        setSubmitEnabled(false);
        el.ghe.value = '';
        el.availability.textContent = 'Đang cập nhật tình trạng ghế...';
        el.map.textContent = 'Đang tải sơ đồ chỗ ngồi...';

        fetch(INIT.availabilityUrl + '?' + params.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) throw new Error(data.error || 'Không thể tải tình trạng ghế.');
                return data;
            });
        }).then(function (data) {
            if (requestId !== availabilityRequest) return;
            taken = Array.isArray(data.ghe_da_dat) ? data.ghe_da_dat : [];
            capacity = Number(data.suc_chua || 0);
            booked = Number(data.so_ghe_da_dat || 0);
            available = Number(data.so_ghe_con_trong || 0);
            tripStatus = data.trang_thai || '';
            availabilityReady = capacity > 0 && ['mo_ban', 'het_cho'].indexOf(tripStatus) !== -1;
            updateAvailabilityLabel();
            renderSeats();
            setSubmitEnabled(availabilityReady && available > 0);
        }).catch(function (error) {
            if (requestId !== availabilityRequest) return;
            capacity = 0;
            booked = 0;
            available = 0;
            tripStatus = '';
            taken = [];
            availabilityReady = false;
            updateAvailabilityLabel();
            el.map.textContent = error.message;
            setSubmitEnabled(false);
        });
    }

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
        if (!availabilityReady || available < 1 || taken.indexOf(id) !== -1) {
            b.disabled = true;
            if (taken.indexOf(id) !== -1) {
                b.className += ' is-taken';
                b.setAttribute('aria-label', 'Chỗ ' + id + ' đã có người đặt');
            }
            b.setAttribute('aria-pressed', 'false');
        }
        return b;
    }
    function gap() { var s = document.createElement('span'); s.className = 'gap'; s.setAttribute('aria-hidden', 'true'); return s; }

    function renderSeats() {
        var r = route();
        var type = el.loai.value;
        var context = [el.tuyen.value, el.ngay.value, el.gio.value, type, chieu()].join('|');
        if (context !== lastContext && lastContext !== '') selected.clear();
        lastContext = context;
        taken.forEach(function (id) { selected.delete(id); });

        el.map.innerHTML = '';

        if (type === 'ghe') {
            var deck = document.createElement('div'); deck.className = 'deck';
            deck.innerHTML = '<h3>Xe ghế ngồi (' + capacity + ' chỗ)</h3>';
            var grid = document.createElement('div'); grid.className = 'deck-grid sit';
            for (var n = 1; n <= capacity; n++) {
                var col = (n - 1) % 4;
                if (col === 2) grid.appendChild(gap());
                grid.appendChild(buildSeat('G' + pad(n)));
            }
            deck.appendChild(grid);
            el.map.appendChild(deck);
        } else {
            var lowerCapacity = Math.ceil(capacity / 2);
            var upperCapacity = Math.floor(capacity / 2);
            [['D', 'Tầng dưới', lowerCapacity], ['T', 'Tầng trên', upperCapacity]].forEach(function (f) {
                var deck = document.createElement('div'); deck.className = 'deck';
                deck.innerHTML = '<h3>' + f[1] + ' (' + f[2] + ' chỗ)</h3>';
                var grid = document.createElement('div'); grid.className = 'deck-grid bed';
                for (var n = 1; n <= f[2]; n++) {
                    if ((n - 1) % 3 === 1) grid.appendChild(gap());
                    grid.appendChild(buildSeat(f[0] + pad(n)));
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
            if (selected.size >= available) {
                el.err.textContent = 'Chỉ còn ' + available + ' chỗ trống trên chuyến này.';
                el.err.hidden = false;
                return;
            }
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

    el.tuyen.addEventListener('change', function () { refreshRoute(); loadAvailability(); });
    [el.ngay, el.gio, el.loai].forEach(function (x) { x.addEventListener('change', loadAvailability); });
    form.querySelectorAll('input[name="chieu"]').forEach(function (x) { x.addEventListener('change', renderSeats); });

    /* Chặn gửi form khi chưa chọn chỗ */
    form.addEventListener('submit', function (e) {
        if (!availabilityReady || available < selected.size) {
            e.preventDefault();
            el.err.textContent = 'Không thể xác nhận vì tình trạng ghế chưa sẵn sàng hoặc không còn đủ chỗ.';
            el.err.hidden = false;
            return;
        }
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
    updateAvailabilityLabel();
    renderSeats();
    setSubmitEnabled(availabilityReady && available > 0);

    /* Nếu đến từ trang chủ với số khách > 1, nhắc người dùng chọn đủ số chỗ */
    if (INIT.want > 1 && selected.size === 0) {
        el.hint.textContent = (el.hint.textContent + ' Bạn dự định đi ' + INIT.want + ' người, hãy chọn ' + INIT.want + ' chỗ.').trim();
    }
})();
