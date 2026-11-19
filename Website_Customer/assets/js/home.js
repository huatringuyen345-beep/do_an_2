(function () {
    'use strict';

    var routes = Array.isArray(window.TAI_NGUYEN_ROUTES) ? window.TAI_NGUYEN_ROUTES : [];
    var today = new Date();
    var state = {
        fromCity: 'Bạc Liêu',
        toCity: '',
        departDate: [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0')
        ].join('-'),
        passengers: 1
    };

    function formatMoney(amount) {
        return Number(amount).toLocaleString('vi-VN') + 'đ';
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[character];
        });
    }

    function findRouteByName(name) {
        var normalized = name.toLowerCase();
        return routes.find(function (route) {
            return route.name.toLowerCase().indexOf(normalized) !== -1;
        });
    }

    function findRouteByCityName(name) {
        return findRouteByName(name);
    }

    function goToBooking(route) {
        if (!route) return;
        var params = new URLSearchParams();
        params.set('tuyen', route.slug);
        params.set('ngay', state.departDate);
        params.set('sl', String(state.passengers));
        window.location.href = 'DatVe.php?' + params.toString();
    }

    function showToast(message, type) {
        var toast = document.getElementById('toastNotice');
        var text = document.getElementById('toastMessage');
        if (!toast || !text) return;
        text.textContent = message;
        toast.className = 'toast-notice show ' + (type || 'success');
        window.setTimeout(function () { toast.classList.remove('show'); }, 3000);
    }

    function scrollToResults() {
        var section = document.getElementById('tripResultsSection');
        if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderTripsForRoute(route) {
        var container = document.getElementById('tripsListContainer');
        var count = document.getElementById('resultsCountNumber');
        var routeHeader = document.getElementById('resultsRouteHeader');
        if (!container || !route) return;

        routeHeader.textContent = 'Bạc Liêu ➔ ' + route.name;
        count.textContent = String(route.departures.length);
        container.innerHTML = '';

        if (!route.departures.length) {
            container.innerHTML = '<div style="padding:32px;text-align:center;background:#fff;border:1px dashed #cbd5e1;border-radius:12px;">Chưa có giờ khởi hành trong cơ sở dữ liệu cho tuyến này.</div>';
            return;
        }

        route.departures.slice(0, 12).forEach(function (departure) {
            var card = document.createElement('article');
            card.className = 'trip-card';
            card.innerHTML =
                '<div class="trip-card-main">' +
                    '<div class="trip-info-left">' +
                        '<span class="trip-bus-type-tag">Dữ liệu lịch chạy từ cơ sở dữ liệu</span>' +
                        '<div class="trip-timeline">' +
                            '<div class="timeline-point"><span class="time">' + escapeHtml(departure.substring(0, 5)) + '</span><span class="place">Bạc Liêu</span></div>' +
                            '<div class="timeline-duration-indicator"><span>' + escapeHtml(route.duration) + '</span><div class="timeline-bar"></div><span style="font-size:.75rem;color:#10b981">✓ Chạy thẳng</span></div>' +
                            '<div class="timeline-point" style="text-align:right"><span class="time">Khởi hành</span><span class="place">' + escapeHtml(route.name) + '</span></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="trip-action-right">' +
                        '<span class="trip-seat-availability">Giá từ ' + formatMoney(route.seatPrice) + '</span>' +
                        '<button type="button" class="btn btn-primary btn-block btn-select-trip">Chọn chuyến</button>' +
                    '</div>' +
                '</div>';
            card.querySelector('.btn-select-trip').addEventListener('click', function () { goToBooking(route); });
            container.appendChild(card);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (event) {
                var target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    event.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        var departInput = document.getElementById('departDateInput');
        if (departInput) {
            departInput.value = state.departDate;
            departInput.min = state.departDate;
            departInput.addEventListener('change', function () {
                state.departDate = departInput.value;
                var dateLabel = document.getElementById('departDateVal');
                if (dateLabel) dateLabel.textContent = departInput.value.split('-').reverse().join('/');
            });
            var departBox = document.getElementById('fieldBoxDepart');
            if (departBox) {
                var openDatePicker = function () {
                    if (typeof departInput.showPicker === 'function') departInput.showPicker();
                    else departInput.focus();
                };
                departBox.addEventListener('click', openDatePicker);
                departBox.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openDatePicker();
                    }
                });
            }
        }
        var selectedCity = document.getElementById('toCityVal');
        if (selectedCity) state.toCity = selectedCity.textContent.trim();

        var swapButton = document.getElementById('btnSwapLocations');
        if (swapButton) {
            swapButton.addEventListener('click', function () {
                var route = findRouteByCityName(state.toCity);
                var params = new URLSearchParams({ chieu: 've' });
                if (route) params.set('tuyen', route.slug);
                window.location.href = 'DatVe.php?' + params.toString();
            });
        }

        document.querySelectorAll('.search-tab-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                if (button.dataset.tab === 'parcel') {
                    window.location.href = 'DatDichVu.php?service=van_chuyen_hang';
                } else if (button.dataset.tab === 'rent') {
                    window.location.href = 'LienHe.php';
                }
            });
        });

        var searchButton = document.getElementById('btnMainSearch');
        if (searchButton) {
            searchButton.addEventListener('click', function () {
                var route = findRouteByCityName(state.toCity);
                if (route) {
                    goToBooking(route);
                } else {
                    showToast('Không tìm thấy tuyến trong cơ sở dữ liệu.', 'error');
                }
            });
        }

        document.querySelectorAll('.quick-tag-pill').forEach(function (pill) {
            pill.addEventListener('click', function () {
                var route = routes.find(function (item) { return item.slug === pill.dataset.routeSlug; });
                if (route) goToBooking(route);
            });
        });

        var modal = document.getElementById('cityModal');
        var modalTitle = document.getElementById('cityModalTitle');
        var cityList = document.getElementById('cityModalList');
        var searchInput = document.getElementById('citySearchInput');
        function renderCities(query) {
            if (!cityList) return;
            cityList.innerHTML = '';
            routes.filter(function (route) {
                return !query || route.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            }).forEach(function (route) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'city-pick-item';
                item.textContent = route.name;
                item.addEventListener('click', function () {
                    state.toCity = route.name;
                    var label = document.getElementById('toCityVal');
                    if (label) label.textContent = route.name;
                    renderTripsForRoute(route);
                    modal.classList.remove('open');
                    scrollToResults();
                });
                cityList.appendChild(item);
            });
        }

        var fieldTo = document.getElementById('fieldBoxTo');
        if (fieldTo && modal) {
            fieldTo.addEventListener('click', function () {
                modalTitle.textContent = 'Chọn điểm đến';
                renderCities('');
                searchInput.value = '';
                modal.classList.add('open');
            });
        }

        var closeModal = document.getElementById('closeCityModal');
        if (closeModal) closeModal.addEventListener('click', function () { modal.classList.remove('open'); });
        if (searchInput) searchInput.addEventListener('input', function () { renderCities(searchInput.value); });
        if (modal) modal.addEventListener('click', function (event) { if (event.target === modal) modal.classList.remove('open'); });

        document.querySelectorAll('.sort-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                document.querySelectorAll('.sort-chip').forEach(function (item) { item.classList.remove('active'); });
                chip.classList.add('active');
                var container = document.getElementById('tripsListContainer');
                if (!container) return;
                var cards = Array.from(container.querySelectorAll('.trip-card'));
                cards.sort(function (a, b) {
                    return (chip.dataset.sort === 'price-asc') ? 0 : (a.dataset.departure || '').localeCompare(b.dataset.departure || '');
                });
                cards.forEach(function (card) { container.appendChild(card); });
            });
        });

        var reset = document.getElementById('btnResetFilters');
        if (reset) {
            reset.addEventListener('click', function () {
                document.querySelectorAll('.filter-card input[type="checkbox"]').forEach(function (input) { input.checked = false; });
                var defaultRoute = routes.find(function (route) { return route.slug === 'tp-hcm'; }) || routes[0];
                if (defaultRoute) renderTripsForRoute(defaultRoute);
            });
        }

        document.querySelectorAll('input[name="filterTime"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var container = document.getElementById('tripsListContainer');
                var selected = Array.from(document.querySelectorAll('input[name="filterTime"]:checked')).map(function (input) { return input.value; });
                if (!container) return;
                var shown = 0;
                Array.from(container.querySelectorAll('.trip-card')).forEach(function (card) {
                    var departure = card.dataset.departure || card.querySelector('.time');
                    var time = typeof departure === 'string' ? departure.substring(0, 5) : (departure ? departure.textContent : '00:00');
                    var hour = parseInt(time.substring(0, 2), 10);
                    var match = !selected.length ||
                        (selected.indexOf('early') !== -1 && hour < 6) ||
                        (selected.indexOf('morning') !== -1 && hour >= 6 && hour < 12) ||
                        (selected.indexOf('afternoon') !== -1 && hour >= 12 && hour < 18) ||
                        (selected.indexOf('night') !== -1 && hour >= 18);
                    card.hidden = !match;
                    if (match) shown++;
                });
                var count = document.getElementById('resultsCountNumber');
                if (count) count.textContent = String(shown);
            });
        });

        var defaultRoute = routes.find(function (route) { return route.slug === 'tp-hcm'; }) || routes[0];
        if (defaultRoute) renderTripsForRoute(defaultRoute);
    });
})();
