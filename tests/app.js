/**
 * TÀI NGUYÊN BUS LINES - JAVASCRIPT ENGINE
 * Tích hợp tính năng Vexere (Tìm kiếm tức thì, lọc đa chiều, sơ đồ chọn ghế)
 * và FutaBus (Dịch vụ đón tận nhà, Express, nhận diện thương hiệu)
 */

(function () {
    'use strict';

    // ================= DỮ LIỆU CHUYẾN XE MẪU (MOCK DATA) =================
    const CITIES = [
        { id: 'BL', name: 'Bạc Liêu', province: 'Bạc Liêu', isHot: true },
        { id: 'SG', name: 'TP. Hồ Chí Minh', province: 'TP.HCM', isHot: true },
        { id: 'CT', name: 'Cần Thơ', province: 'Cần Thơ', isHot: true },
        { id: 'VT', name: 'Vũng Tàu', province: 'Bà Rịa - Vũng Tàu', isHot: true },
        { id: 'DL', name: 'Đà Lạt', province: 'Lâm Đồng', isHot: true },
        { id: 'CM', name: 'Cà Mau', province: 'Cà Mau', isHot: true },
        { id: 'BD', name: 'Bình Dương', province: 'Bình Dương', isHot: true },
        { id: 'NT', name: 'Nha Trang', province: 'Khánh Hòa', isHot: true },
        { id: 'RG', name: 'Rạch Giá', province: 'Kiên Giang', isHot: false },
        { id: 'ST', name: 'Sóc Trăng', province: 'Sóc Trăng', isHot: false }
    ];

    const MOCK_TRIPS = [
        {
            id: 'TN-01',
            from: 'Bạc Liêu',
            to: 'TP. Hồ Chí Minh',
            departTime: '07:30',
            arriveTime: '13:00',
            duration: '5 giờ 30 phút',
            busType: 'Limousine VIP 34 Phòng',
            price: 220000,
            originalPrice: 250000,
            availableSeats: 12,
            rating: 4.9,
            reviewsCount: 1420,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu (Trụ sở chính)', 'Đón tận nhà nội ô Bạc Liêu (Miễn phí)', 'VP Hộ Phòng', 'VP Giá Rai'],
            dropPoints: ['Bến xe Miền Tây (Quầy 28)', 'VP Kinh Dương Vương (Q. Bình Tân)', 'VP Lê Hồng Phong (Quận 5)'],
            takenSeats: ['A02', 'A05', 'A06', 'B01', 'B03', 'B08']
        },
        {
            id: 'TN-02',
            from: 'Bạc Liêu',
            to: 'TP. Hồ Chí Minh',
            departTime: '09:00',
            arriveTime: '14:30',
            duration: '5 giờ 30 phút',
            busType: 'Giường Nằm 40 Chỗ',
            price: 180000,
            originalPrice: 200000,
            availableSeats: 18,
            rating: 4.8,
            reviewsCount: 980,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu (Trụ sở chính)', 'Đón tận nhà nội ô Bạc Liêu (Miễn phí)'],
            dropPoints: ['Bến xe Miền Tây (Quầy 28)', 'VP An Sương'],
            takenSeats: ['A01', 'A04', 'B02', 'B04']
        },
        {
            id: 'TN-03',
            from: 'Bạc Liêu',
            to: 'TP. Hồ Chí Minh',
            departTime: '13:30',
            arriveTime: '19:00',
            duration: '5 giờ 30 phút',
            busType: 'Limousine VIP 34 Phòng',
            price: 220000,
            originalPrice: 250000,
            availableSeats: 8,
            rating: 4.9,
            reviewsCount: 1850,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu', 'Đón tận nhà nội ô Bạc Liêu'],
            dropPoints: ['Bến xe Miền Tây', 'VP Lê Hồng Phong (Quận 5)'],
            takenSeats: ['A01', 'A02', 'A03', 'B01', 'B02', 'B05', 'B06']
        },
        {
            id: 'TN-04',
            from: 'Bạc Liêu',
            to: 'TP. Hồ Chí Minh',
            departTime: '21:00',
            arriveTime: '02:30',
            duration: '5 giờ 30 phút (Chạy đêm)',
            busType: 'Limousine VIP 34 Phòng',
            price: 230000,
            originalPrice: 260000,
            availableSeats: 5,
            rating: 5.0,
            reviewsCount: 2300,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu', 'Đón tận nhà nội ô Bạc Liêu'],
            dropPoints: ['Bến xe Miền Tây', 'Bệnh viện Chợ Rẫy (Đón trả sáng sớm)'],
            takenSeats: ['A01', 'A02', 'A03', 'A04', 'A05', 'B01', 'B02', 'B03', 'B04']
        },
        {
            id: 'TN-05',
            from: 'Bạc Liêu',
            to: 'TP. Hồ Chí Minh',
            departTime: '22:30',
            arriveTime: '04:00',
            duration: '5 giờ 30 phút (Chạy đêm)',
            busType: 'Giường Nằm 40 Chỗ',
            price: 180000,
            originalPrice: 200000,
            availableSeats: 15,
            rating: 4.8,
            reviewsCount: 840,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu'],
            dropPoints: ['Bến xe Miền Tây', 'VP Quận 5'],
            takenSeats: ['A03', 'B05']
        },
        {
            id: 'TN-06',
            from: 'Bạc Liêu',
            to: 'Cần Thơ',
            departTime: '06:00',
            arriveTime: '08:30',
            duration: '2 giờ 30 phút',
            busType: 'Ghế Ngồi Cao Cấp 32 Chỗ',
            price: 110000,
            originalPrice: 120000,
            availableSeats: 16,
            rating: 4.8,
            reviewsCount: 650,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu', 'Đón tận nhà nội ô'],
            dropPoints: ['Bến xe Trung tâm Cần Thơ', 'Đại học Cần Thơ', 'Bệnh viện Đa khoa TW'],
            takenSeats: ['G01', 'G02', 'G05']
        },
        {
            id: 'TN-07',
            from: 'Bạc Liêu',
            to: 'Vũng Tàu',
            departTime: '19:30',
            arriveTime: '03:30',
            duration: '8 giờ',
            busType: 'Limousine VIP 34 Phòng',
            price: 260000,
            originalPrice: 290000,
            availableSeats: 9,
            rating: 4.9,
            reviewsCount: 420,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu', 'Đón tận nhà nội ô'],
            dropPoints: ['Bến xe Vũng Tàu (Nam Kỳ Khởi Nghĩa)', 'Bãi Sau', 'Bãi Trước'],
            takenSeats: ['A01', 'A02', 'B03']
        },
        {
            id: 'TN-08',
            from: 'Bạc Liêu',
            to: 'Đà Lạt',
            departTime: '18:00',
            arriveTime: '05:30',
            duration: '11 giờ 30 phút',
            busType: 'Limousine VIP 34 Phòng',
            price: 380000,
            originalPrice: 420000,
            availableSeats: 7,
            rating: 4.9,
            reviewsCount: 890,
            shuttle: true,
            pickupPoints: ['Bến xe Bạc Liêu', 'Đón tận nhà nội ô'],
            dropPoints: ['Bến xe Liên tỉnh Đà Lạt', 'Trung chuyển nội thành Đà Lạt (Miễn phí)'],
            takenSeats: ['A03', 'A04', 'B01', 'B02']
        }
    ];

    // ================= STATE QUẢN LÝ ỨNG DỤNG =================
    const state = {
        activeTab: 'bus', // 'bus', 'parcel', 'rent'
        tripMode: 'oneway', // 'oneway', 'roundtrip'
        fromCity: 'Bạc Liêu',
        toCity: 'TP. Hồ Chí Minh',
        departDate: new Date().toISOString().split('T')[0],
        returnDate: '',
        passengers: 1,
        filterTimes: [], // ['early', 'morning', 'afternoon', 'night']
        filterBusTypes: [], // ['limousine', 'bed', 'sit']
        filterShuttle: false,
        sortBy: 'time-asc',
        activeTrip: null, // Chuyến đang mở chọn ghế
        selectedSeats: new Set(),
        pickupLocation: '',
        dropLocation: ''
    };

    // ================= TIỆN ÍCH HELPER =================
    function formatMoney(amount) {
        return Number(amount).toLocaleString('vi-VN') + 'đ';
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toastNotice');
        const text = document.getElementById('toastMessage');
        if (!toast || !text) return;
        text.textContent = message;
        toast.className = `toast-notice show ${type}`;
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3600);
    }

    // ================= KHỞI TẠO DOM & SỰ KIỆN =================
    document.addEventListener('DOMContentLoaded', () => {
        initHeaderEvents();
        initSearchBoxEvents();
        initCityModal();
        initFiltersAndSort();
        initCheckoutModal();
        renderTripsList();
    });

    // 1. Header & Menu
    function initHeaderEvents() {
        // Cuộn mượt khi nhấn nút trên header
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // 2. Search Box Interactive (Tabs, Swap, Dates)
    function initSearchBoxEvents() {
        // Tab chọn loại hình (Xe khách / Gửi hàng / Thuê xe)
        const tabBtns = document.querySelectorAll('.search-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                state.activeTab = btn.dataset.tab;

                if (state.activeTab === 'parcel') {
                    showToast('Đã chuyển sang chế độ: Tài Nguyên Express - Vận chuyển hàng hóa hỏa tốc!', 'info');
                } else if (state.activeTab === 'rent') {
                    showToast('Đã chuyển sang: Cho thuê xe hợp đồng du lịch 16 - 29 - 45 chỗ!', 'info');
                }
            });
        });

        // Chọn Một chiều / Khứ hồi
        const tripRadios = document.querySelectorAll('input[name="tripMode"]');
        const returnBox = document.getElementById('returnDateBox');
        tripRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                state.tripMode = radio.value;
                if (returnBox) {
                    returnBox.style.opacity = state.tripMode === 'roundtrip' ? '1' : '0.5';
                }
            });
        });

        // Nút đảo chiều điểm đi ⇄ điểm đến
        const swapBtn = document.getElementById('btnSwapLocations');
        if (swapBtn) {
            swapBtn.addEventListener('click', () => {
                const temp = state.fromCity;
                state.fromCity = state.toCity;
                state.toCity = temp;

                document.getElementById('fromCityVal').textContent = state.fromCity;
                document.getElementById('toCityVal').textContent = state.toCity;
                renderTripsList();
                showToast(`Đã đổi chiều chuyến xe: ${state.fromCity} ➔ ${state.toCity}`);
            });
        }

        // Chọn ngày đi
        const departInput = document.getElementById('departDateInput');
        if (departInput) {
            departInput.value = state.departDate;
            departInput.min = state.departDate;
            departInput.addEventListener('change', (e) => {
                state.departDate = e.target.value;
                const formatted = e.target.value.split('-').reverse().join('/');
                document.getElementById('departDateVal').textContent = formatted;
            });
        }

        // Quick Tag Pills dưới search bar
        document.querySelectorAll('.quick-tag-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const [from, to] = pill.dataset.route.split('-');
                state.fromCity = from;
                state.toCity = to;
                document.getElementById('fromCityVal').textContent = state.fromCity;
                document.getElementById('toCityVal').textContent = state.toCity;
                renderTripsList();
                scrollToResults();
            });
        });

        // Nút Tìm chuyến xe chính
        const searchBtn = document.getElementById('btnMainSearch');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                renderTripsList();
                scrollToResults();
                showToast(`Tìm thấy chuyến xe phù hợp từ ${state.fromCity} đi ${state.toCity}!`);
            });
        }
    }

    function scrollToResults() {
        const resultsSec = document.getElementById('tripResultsSection');
        if (resultsSec) {
            resultsSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // 3. Modal chọn Tỉnh / Thành phố (Vexere Pop-up Style)
    function initCityModal() {
        const modal = document.getElementById('cityModal');
        const closeBtn = document.getElementById('closeCityModal');
        const modalTitle = document.getElementById('cityModalTitle');
        const cityList = document.getElementById('cityModalList');
        const searchInput = document.getElementById('citySearchInput');
        let pickingTarget = 'from'; // 'from' hoặc 'to'

        function renderCities(filterText = '') {
            cityList.innerHTML = '';
            const normalized = filterText.toLowerCase().trim();
            const filtered = CITIES.filter(c => 
                c.name.toLowerCase().includes(normalized) || 
                c.province.toLowerCase().includes(normalized)
            );

            filtered.forEach(city => {
                const item = document.createElement('div');
                item.className = 'city-pick-item';
                item.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>${city.name}</span>
                `;
                item.addEventListener('click', () => {
                    if (pickingTarget === 'from') {
                        state.fromCity = city.name;
                        document.getElementById('fromCityVal').textContent = city.name;
                    } else {
                        state.toCity = city.name;
                        document.getElementById('toCityVal').textContent = city.name;
                    }
                    modal.classList.remove('open');
                    renderTripsList();
                });
                cityList.appendChild(item);
            });
        }

        // Mở modal khi bấm vào ô Điểm đi
        document.getElementById('fieldBoxFrom').addEventListener('click', () => {
            pickingTarget = 'from';
            modalTitle.textContent = 'Chọn Tỉnh / Thành phố xuất phát';
            renderCities();
            modal.classList.add('open');
            searchInput.value = '';
            searchInput.focus();
        });

        // Mở modal khi bấm vào ô Điểm đến
        document.getElementById('fieldBoxTo').addEventListener('click', () => {
            pickingTarget = 'to';
            modalTitle.textContent = 'Chọn Tỉnh / Thành phố đến';
            renderCities();
            modal.classList.add('open');
            searchInput.value = '';
            searchInput.focus();
        });

        closeBtn.addEventListener('click', () => modal.classList.remove('open'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('open');
        });

        searchInput.addEventListener('input', (e) => {
            renderCities(e.target.value);
        });
    }

    // 4. Bộ lọc & Sắp xếp chuyến xe
    function initFiltersAndSort() {
        // Lọc theo khung giờ
        document.querySelectorAll('input[name="filterTime"]').forEach(cb => {
            cb.addEventListener('change', () => {
                state.filterTimes = Array.from(document.querySelectorAll('input[name="filterTime"]:checked')).map(i => i.value);
                renderTripsList();
            });
        });

        // Lọc theo loại xe
        document.querySelectorAll('input[name="filterBusType"]').forEach(cb => {
            cb.addEventListener('change', () => {
                state.filterBusTypes = Array.from(document.querySelectorAll('input[name="filterBusType"]:checked')).map(i => i.value);
                renderTripsList();
            });
        });

        // Lọc xe trung chuyển
        const shuttleCb = document.getElementById('filterShuttle');
        if (shuttleCb) {
            shuttleCb.addEventListener('change', () => {
                state.filterShuttle = shuttleCb.checked;
                renderTripsList();
            });
        }

        // Nút đặt lại bộ lọc
        const resetBtn = document.getElementById('btnResetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                document.querySelectorAll('.filter-card input[type="checkbox"]').forEach(c => c.checked = false);
                state.filterTimes = [];
                state.filterBusTypes = [];
                state.filterShuttle = false;
                renderTripsList();
                showToast('Đã xóa tất cả bộ lọc');
            });
        }

        // Chip sắp xếp
        document.querySelectorAll('.sort-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.sort-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                state.sortBy = chip.dataset.sort;
                renderTripsList();
            });
        });
    }

    // 5. Hiển thị danh sách chuyến xe (Trips List Rendering)
    function renderTripsList() {
        const container = document.getElementById('tripsListContainer');
        const countLabel = document.getElementById('resultsCountNumber');
        const routeTitle = document.getElementById('resultsRouteHeader');
        if (!container) return;

        // Cập nhật tiêu đề tuyến
        if (routeTitle) {
            routeTitle.textContent = `${state.fromCity} ➔ ${state.toCity}`;
        }

        // Lọc danh sách chuyến
        let trips = MOCK_TRIPS.filter(trip => {
            // Khớp điểm đi / điểm đến (hoặc hiển thị các chuyến xuất phát từ Bạc Liêu nếu tìm kiếm chung)
            const matchRoute = (trip.from === state.fromCity && trip.to === state.toCity) ||
                               (state.fromCity === 'Bạc Liêu' && trip.from === 'Bạc Liêu');
            if (!matchRoute) return false;

            // Lọc theo giờ
            if (state.filterTimes.length > 0) {
                const hour = parseInt(trip.departTime.split(':')[0], 10);
                const isEarly = state.filterTimes.includes('early') && (hour >= 0 && hour < 6);
                const isMorning = state.filterTimes.includes('morning') && (hour >= 6 && hour < 12);
                const isAfternoon = state.filterTimes.includes('afternoon') && (hour >= 12 && hour < 18);
                const isNight = state.filterTimes.includes('night') && (hour >= 18 && hour <= 24);
                if (!isEarly && !isMorning && !isAfternoon && !isNight) return false;
            }

            // Lọc theo loại xe
            if (state.filterBusTypes.length > 0) {
                const isLimo = state.filterBusTypes.includes('limousine') && trip.busType.includes('Limousine');
                const isBed = state.filterBusTypes.includes('bed') && trip.busType.includes('Giường Nằm');
                const isSit = state.filterBusTypes.includes('sit') && trip.busType.includes('Ghế Ngồi');
                if (!isLimo && !isBed && !isSit) return false;
            }

            // Lọc trung chuyển
            if (state.filterShuttle && !trip.shuttle) return false;

            return true;
        });

        // Sắp xếp
        if (state.sortBy === 'time-asc') {
            trips.sort((a, b) => a.departTime.localeCompare(b.departTime));
        } else if (state.sortBy === 'time-desc') {
            trips.sort((a, b) => b.departTime.localeCompare(a.departTime));
        } else if (state.sortBy === 'price-asc') {
            trips.sort((a, b) => a.price - b.price);
        } else if (state.sortBy === 'price-desc') {
            trips.sort((a, b) => b.price - a.price);
        } else if (state.sortBy === 'rating-desc') {
            trips.sort((a, b) => b.rating - a.rating);
        }

        if (countLabel) {
            countLabel.textContent = trips.length;
        }

        container.innerHTML = '';

        if (trips.length === 0) {
            container.innerHTML = `
                <div style="background:#ffffff; border:1.5px dashed var(--border-color); border-radius:var(--radius-lg); padding:48px 24px; text-align:center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" style="margin-bottom:12px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <h3 style="font-size:1.2rem; color:var(--brand-navy); margin-bottom:6px;">Chưa tìm thấy chuyến xe phù hợp</h3>
                    <p style="color:var(--text-muted); font-size:0.92rem; max-width:440px; margin:0 auto 18px;">Vui lòng thử điều chỉnh lại bộ lọc thời gian, loại xe hoặc liên hệ hotline <strong>0291 3 000 000</strong> để nhân viên xếp chuyến tăng cường.</p>
                    <button class="btn btn-outline" onclick="document.getElementById('btnResetFilters').click()">Đặt lại bộ lọc</button>
                </div>
            `;
            return;
        }

        trips.forEach(trip => {
            const card = document.createElement('div');
            card.className = 'trip-card';
            card.id = `tripCard_${trip.id}`;

            card.innerHTML = `
                <div class="trip-card-main">
                    <div class="trip-info-left">
                        <div class="trip-badge-row">
                            <span class="trip-operator-tag">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f26522" stroke-width="2"><path d="M8 6v6M15 6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3M9 18h5"/><circle cx="7" cy="18" r="2"/><circle cx="16" cy="18" r="2"/></svg>
                                Tài Nguyên Bus Lines
                            </span>
                            <span class="trip-bus-type-tag">${trip.busType}</span>
                            ${trip.shuttle ? '<span class="trip-shuttle-tag">Đón tận nhà Bạc Liêu</span>' : ''}
                        </div>

                        <div class="trip-timeline">
                            <div class="timeline-point">
                                <span class="time">${trip.departTime}</span>
                                <span class="place">${trip.from}</span>
                            </div>

                            <div class="timeline-duration-indicator">
                                <span>${trip.duration}</span>
                                <div class="timeline-bar"></div>
                                <span style="font-size:0.75rem; color:#10b981;">✓ Chạy thẳng</span>
                            </div>

                            <div class="timeline-point" style="text-align:right;">
                                <span class="time">${trip.arriveTime}</span>
                                <span class="place">${trip.to}</span>
                            </div>
                        </div>

                        <div class="trip-amenities-row">
                            <span class="amenity-item" title="Đánh giá chất lượng">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <strong>${trip.rating}</strong> (${trip.reviewsCount})
                            </span>
                            <span>•</span>
                            <span class="amenity-item">Wifi 5G tốc độ cao</span>
                            <span>•</span>
                            <span class="amenity-item">Nước suối & khăn lạnh</span>
                            <span>•</span>
                            <span class="amenity-item">Cổng sạc điện thoại</span>
                        </div>
                    </div>

                    <div class="trip-action-right">
                        ${trip.originalPrice ? `<span class="trip-original-price">${formatMoney(trip.originalPrice)}</span>` : ''}
                        <span class="trip-final-price">${formatMoney(trip.price)}</span>
                        <span class="trip-seat-availability">Còn ${trip.availableSeats} chỗ trống</span>
                        <button class="btn btn-primary btn-block btn-select-trip" data-trip-id="${trip.id}">
                            <span>Chọn chuyến</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- KHU VỰC SƠ ĐỒ CHỌN CHỖ CỦA CHUYẾN NÀY (DRAWER) -->
                <div class="trip-seat-drawer" id="seatDrawer_${trip.id}">
                    <div class="seat-drawer-grid">
                        <div class="seat-picker-wrap">
                            <div class="seat-legend-box">
                                <span class="seat-legend-item"><i class="i-empty"></i> Chỗ trống</span>
                                <span class="seat-legend-item"><i class="i-selected"></i> Đang chọn</span>
                                <span class="seat-legend-item"><i class="i-taken"></i> Đã bán</span>
                            </div>
                            <div class="decks-container" id="decksContainer_${trip.id}">
                                <!-- Được sinh tự động bằng hàm buildSeatDeck -->
                            </div>
                        </div>

                        <!-- Bảng tóm tắt chọn ghế & điểm đón trả -->
                        <div class="seat-summary-panel">
                            <h4 class="seat-summary-title">Thông tin giữ chỗ</h4>
                            
                            <div>
                                <span style="font-size:0.84rem; color:var(--text-muted); display:block; margin-bottom:4px;">Ghế đã chọn:</span>
                                <div class="selected-seats-badge-row" id="selectedSeatsRow_${trip.id}">
                                    <span style="font-size:0.86rem; color:var(--text-light); font-style:italic;">Chưa chọn ghế</span>
                                </div>
                            </div>

                            <div class="pickup-selector-group">
                                <label for="pickupSel_${trip.id}">Điểm đón khách:</label>
                                <select id="pickupSel_${trip.id}">
                                    ${trip.pickupPoints.map(p => `<option value="${p}">${p}</option>`).join('')}
                                </select>
                            </div>

                            <div class="pickup-selector-group">
                                <label for="dropSel_${trip.id}">Điểm trả khách:</label>
                                <select id="dropSel_${trip.id}">
                                    ${trip.dropPoints.map(p => `<option value="${p}">${p}</option>`).join('')}
                                </select>
                            </div>

                            <div class="drawer-price-calc">
                                <div class="calc-row">
                                    <span style="color:var(--text-muted);">Đơn giá:</span>
                                    <span>${formatMoney(trip.price)} / vé</span>
                                </div>
                                <div class="calc-row">
                                    <span style="color:var(--text-muted);">Số lượng vé:</span>
                                    <span id="seatCountText_${trip.id}">0 vé</span>
                                </div>
                                <div class="calc-total-row">
                                    <span>Tổng cộng:</span>
                                    <strong id="seatTotalText_${trip.id}">0đ</strong>
                                </div>
                            </div>

                            <button class="btn btn-primary btn-block btn-lg btn-proceed-checkout" data-trip-id="${trip.id}">
                                <span>Tiếp tục đặt vé</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(card);
        });

        // Gắn sự kiện click mở Sơ đồ ghế
        document.querySelectorAll('.btn-select-trip').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tripId = btn.dataset.tripId;
                toggleSeatDrawer(tripId);
            });
        });

        // Gắn sự kiện nút "Tiếp tục đặt vé"
        document.querySelectorAll('.btn-proceed-checkout').forEach(btn => {
            btn.addEventListener('click', () => {
                const tripId = btn.dataset.tripId;
                openCheckoutModal(tripId);
            });
        });
    }

    // 6. Sơ đồ chọn chỗ chi tiết (Seat Drawer Controller)
    function toggleSeatDrawer(tripId) {
        const drawer = document.getElementById(`seatDrawer_${tripId}`);
        if (!drawer) return;

        const isOpen = drawer.classList.contains('open');

        // Đóng các drawer khác đang mở
        document.querySelectorAll('.trip-seat-drawer.open').forEach(d => {
            d.classList.remove('open');
        });

        if (!isOpen) {
            drawer.classList.add('open');
            state.activeTrip = MOCK_TRIPS.find(t => t.id === tripId);
            state.selectedSeats.clear();
            renderSeatsMap(tripId);
            updateDrawerSummary(tripId);
            drawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            state.activeTrip = null;
        }
    }

    function renderSeatsMap(tripId) {
        const container = document.getElementById(`decksContainer_${tripId}`);
        const trip = state.activeTrip;
        if (!container || !trip) return;

        container.innerHTML = '';

        const isLimousine = trip.busType.includes('Limousine');
        
        // Cấu hình 2 tầng: Tầng dưới (A) và Tầng trên (B)
        const floors = [
            { id: 'floor1', name: 'Tầng Dưới', prefix: 'A', rows: 6 },
            { id: 'floor2', name: 'Tầng Trên', prefix: 'B', rows: 6 }
        ];

        floors.forEach(floor => {
            const col = document.createElement('div');
            col.className = 'deck-col';
            col.innerHTML = `
                <div class="deck-col-header">
                    <span class="deck-steering-icon">🚗 ĐẦU XE</span>
                    <span>${floor.name}</span>
                </div>
                <div class="deck-seats-grid" id="grid_${tripId}_${floor.prefix}"></div>
            `;
            container.appendChild(col);

            const grid = col.querySelector(`#grid_${tripId}_${floor.prefix}`);
            let seatIndex = 1;

            for (let r = 0; r < floor.rows; r++) {
                // Ghế trái
                const seatCodeL = `${floor.prefix}${seatIndex < 10 ? '0' + seatIndex : seatIndex}`;
                grid.appendChild(createSeatElement(seatCodeL, trip));
                seatIndex++;

                // Lối đi ở giữa
                const gap = document.createElement('div');
                gap.className = 'walk-gap';
                grid.appendChild(gap);

                // Ghế phải
                const seatCodeR = `${floor.prefix}${seatIndex < 10 ? '0' + seatIndex : seatIndex}`;
                grid.appendChild(createSeatElement(seatCodeR, trip));
                seatIndex++;
            }
        });
    }

    function createSeatElement(seatCode, trip) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'seat-btn';
        btn.textContent = seatCode;
        btn.dataset.seat = seatCode;

        if (trip.takenSeats.includes(seatCode)) {
            btn.classList.add('taken');
            btn.disabled = true;
            btn.title = `Chỗ ${seatCode} đã có khách đặt`;
        } else {
            btn.addEventListener('click', () => {
                if (state.selectedSeats.has(seatCode)) {
                    state.selectedSeats.delete(seatCode);
                    btn.classList.remove('selected');
                } else {
                    if (state.selectedSeats.size >= 6) {
                        showToast('Bạn chỉ có thể đặt tối đa 6 chỗ trong một giao dịch!', 'error');
                        return;
                    }
                    state.selectedSeats.add(seatCode);
                    btn.classList.add('selected');
                }
                updateDrawerSummary(trip.id);
            });
        }

        return btn;
    }

    function updateDrawerSummary(tripId) {
        const trip = state.activeTrip;
        if (!trip) return;

        const row = document.getElementById(`selectedSeatsRow_${tripId}`);
        const count = document.getElementById(`seatCountText_${tripId}`);
        const total = document.getElementById(`seatTotalText_${tripId}`);

        const seats = Array.from(state.selectedSeats).sort();

        if (row) {
            if (seats.length === 0) {
                row.innerHTML = `<span style="font-size:0.86rem; color:var(--text-light); font-style:italic;">Chưa chọn ghế</span>`;
            } else {
                row.innerHTML = seats.map(s => `<span class="selected-seat-chip">${s}</span>`).join('');
            }
        }

        if (count) {
            count.textContent = `${seats.length} vé`;
        }

        if (total) {
            total.textContent = formatMoney(seats.length * trip.price);
        }
    }

    // 7. Modal Đặt Vé & Thanh toán Xác Nhận
    function initCheckoutModal() {
        const modal = document.getElementById('checkoutModal');
        const closeBtn = document.getElementById('closeCheckoutModal');
        const form = document.getElementById('checkoutForm');

        if (closeBtn && modal) {
            closeBtn.addEventListener('click', () => modal.classList.remove('open'));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('open');
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const name = document.getElementById('custName').value.trim();
                const phone = document.getElementById('custPhone').value.trim();
                const email = document.getElementById('custEmail').value.trim();
                const orderCode = 'TN-' + Math.floor(100000 + Math.random() * 900000);

                modal.classList.remove('open');
                showToast(`🎉 Đặt vé thành công! Mã đơn: ${orderCode}. Nhân viên Tài Nguyên sẽ gọi số ${phone} để xác nhận chỗ.`);
                
                // Reset selected seats
                state.selectedSeats.clear();
                if (state.activeTrip) {
                    toggleSeatDrawer(state.activeTrip.id);
                }
            });
        }
    }

    function openCheckoutModal(tripId) {
        const trip = state.activeTrip;
        if (!trip || state.selectedSeats.size === 0) {
            showToast('Vui lòng chọn ít nhất một ghế ngồi hoặc giường nằm trước khi tiếp tục!', 'error');
            return;
        }

        const modal = document.getElementById('checkoutModal');
        const preview = document.getElementById('checkoutTicketPreview');
        const totalVal = document.getElementById('checkoutTotalValue');

        const seats = Array.from(state.selectedSeats).sort().join(', ');
        const totalMoney = state.selectedSeats.size * trip.price;

        const pickup = document.getElementById(`pickupSel_${tripId}`).value;
        const drop = document.getElementById(`dropSel_${tripId}`).value;

        preview.innerHTML = `
            <div class="preview-row">
                <span>Tuyến đường:</span>
                <strong>${trip.from} ➔ ${trip.to}</strong>
            </div>
            <div class="preview-row">
                <span>Khởi hành:</span>
                <strong>${trip.departTime} (${state.departDate.split('-').reverse().join('/')})</strong>
            </div>
            <div class="preview-row">
                <span>Loại xe:</span>
                <strong>${trip.busType}</strong>
            </div>
            <div class="preview-row">
                <span>Ghế / Giường đã chọn:</span>
                <strong style="color:var(--futa-orange);">${seats} (${state.selectedSeats.size} chỗ)</strong>
            </div>
            <div class="preview-row">
                <span>Điểm đón khách:</span>
                <strong>${pickup}</strong>
            </div>
            <div class="preview-row">
                <span>Điểm trả khách:</span>
                <strong>${drop}</strong>
            </div>
        `;

        totalVal.textContent = formatMoney(totalMoney);
        modal.classList.add('open');
    }

    // Xuất ra window cho các hàm inline nếu cần
    window.selectQuickRoute = function (from, to) {
        state.fromCity = from;
        state.toCity = to;
        document.getElementById('fromCityVal').textContent = from;
        document.getElementById('toCityVal').textContent = to;
        renderTripsList();
        scrollToResults();
    };

})();
