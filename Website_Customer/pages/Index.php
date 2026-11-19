<?php
require_once __DIR__ . '/../backend/bootstrap.php';
require_once __DIR__ . '/../backend/data/loader.php';
$defaultRoute = $tuyenDuongModel->findRoute('tp-hcm', $routes) ?? ($routes[0] ?? null);
$routeForResults = $defaultRoute ? $defaultRoute : null;
$promotions = array_slice(array_values(array_filter($news, function ($item) {
    return $item['loai'] === 'khuyen-mai';
})), 0, 3);
$routeDestinations = implode(', ', array_column($routes, 'ten'));
$pageTitle = 'Trang chủ | Đặt vé xe khách trực tuyến Bạc Liêu';
$pageDesc = 'Đặt vé xe khách từ Bạc Liêu đi ' . $routeDestinations . ' trực tuyến. Xem lịch trình và giá vé được cập nhật từ hệ thống.';
$homeRoutesJson = json_encode(array_map(function ($route) {
    return [
        'slug' => $route['slug'],
        'name' => $route['ten'],
        'km' => (float)$route['km'],
        'duration' => duration($route['gio']),
        'seatPrice' => (float)$route['ghe'],
        'bedPrice' => $route['giuong'] === null ? null : (float)$route['giuong'],
        'departures' => $route['khoi_hanh'],
    ];
}, $routes), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$extraScripts = ['../assets/js/home.js'];
include __DIR__ . '/../includes/header.php';
?>

    <!-- ================= HERO & BOOKING SEARCH BOX ================= -->
    <section class="hero-section">
        <div class="hero-bg-shapes"></div>
        <div class="container">
            <div class="hero-banner-content">
                <div class="hero-badge-futa">
                    <span class="dot"></span>
                    <span>Tài Nguyên Bus Lines — Chất Lượng Là Danh Dự</span>
                </div>
                <h1 class="hero-title">Đồng Hành An Toàn Trên Mọi Chặng Đường</h1>
                <p class="hero-subtitle">Đặt vé trực tuyến tiện lợi như Vexere, dịch vụ chu đáo chuẩn Futa Phương Trang. Đội xe Limousine đời mới đón trả tận nhà miễn phí tại Bạc Liêu.</p>
            </div>

            <!-- ================= THE HYBRID BOOKING SEARCH CARD ================= -->
            <div class="booking-search-card">
                <!-- 1. Tabs chọn loại hình dịch vụ -->
                <div class="search-tabs">
                    <button class="search-tab-btn active" data-tab="bus">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6v6M15 6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3M9 18h5"/><circle cx="7" cy="18" r="2"/><circle cx="16" cy="18" r="2"/></svg>
                        <span>Xe khách tuyến cố định</span>
                    </button>
                    <button class="search-tab-btn" data-tab="parcel">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.5 9.4 7.5 4.21M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16zM3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"/></svg>
                        <span>Tài Nguyên Express (Gửi hàng)</span>
                    </button>
                    <button class="search-tab-btn" data-tab="rent">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2M7 17h10"/>
                            <circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                        <span>Thuê xe hợp đồng du lịch</span>
                    </button>
                </div>

                <!-- 2. Chọn Một chiều / Khứ hồi -->
                <div class="trip-mode-radios">
                    <label class="trip-radio-item">
                        <input type="radio" name="tripMode" value="oneway" checked>
                        <span>Một chiều</span>
                    </label>
                    <label class="trip-radio-item">
                        <input type="radio" name="tripMode" value="roundtrip">
                        <span>Khứ hồi</span>
                    </label>
                </div>

                <!-- 3. Thanh tìm kiếm 4 khối kiểu Vexere -->
                <div class="search-inputs-grid">
                    <!-- Điểm đi -->
                    <div class="search-field-box" id="fieldBoxFrom" title="Bấm để chọn điểm đi">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f26522" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>Điểm xuất phát</span>
                        </label>
                        <div class="field-val" id="fromCityVal">Bạc Liêu</div>
                        <div class="field-sub">Bến xe Bạc Liêu / Đón tận nhà</div>
                    </div>

                    <!-- Nút đảo chiều ⇄ -->
                    <button class="btn-swap-locations" id="btnSwapLocations" type="button" title="Đổi chiều điểm đi / điểm đến">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3 4 7l4 4M4 7h16M16 21l4-4-4-4M20 17H4"/>
                        </svg>
                    </button>

                    <!-- Điểm đến -->
                    <div class="search-field-box" id="fieldBoxTo" title="Bấm để chọn điểm đến">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0d5cb6" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>Điểm đến</span>
                        </label>
                        <div class="field-val" id="toCityVal"><?= e($defaultRoute['ten'] ?? 'Chưa có tuyến') ?></div>
                        <div class="field-sub">BX Miền Tây / VP Quận 5</div>
                    </div>

                    <div class="search-divider"></div>

                    <!-- Ngày đi -->
                    <div class="search-field-box" id="fieldBoxDepart" role="button" tabindex="0">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <span>Ngày đi</span>
                        </label>
                        <div class="field-val" id="departDateVal">Hôm nay</div>
                        <div class="field-sub">Thứ 3, 17/11/2026</div>
                        <input type="date" id="departDateInput" style="position:absolute; opacity:0; pointer-events:none; width:0; height:0;">
                    </div>

                    <!-- Ngày về (Khứ hồi) -->
                    <div class="search-field-box" id="returnDateBox" style="opacity:0.5;">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <span>Ngày về</span>
                        </label>
                        <div class="field-val" id="returnDateVal">+ Thêm ngày về</div>
                        <div class="field-sub">Chọn ngày khứ hồi</div>
                    </div>

                    <div class="search-divider"></div>

                    <!-- Số lượng vé -->
                    <div class="search-field-box">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <span>Số khách</span>
                        </label>
                        <div class="field-val" id="passengersVal">1 khách</div>
                        <div class="field-sub">Người lớn</div>
                    </div>

                    <!-- Nút TÌM CHUYẾN XE (Màu cam FUTA bắt mắt) -->
                    <button class="btn-search-action" id="btnMainSearch" type="button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <span>TÌM CHUYẾN</span>
                    </button>
                </div>

                <!-- 4. Gợi ý tuyến hot nhanh -->
                <div class="quick-tags-row">
                    <span class="quick-tags-label">Gợi ý tuyến hot:</span>
                    <?php foreach (array_slice($routes, 0, 5) as $route): ?>
                        <button type="button" class="quick-tag-pill" data-route-slug="<?= e($route['slug']) ?>">Bạc Liêu ➔ <?= e($route['ten']) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FEATURES STRIP (CAM KẾT UY TÍN KIỂU FUTA) ================= -->
    <section class="features-strip">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div class="feature-info">
                        <h3>100% Giữ Đúng Chỗ</h3>
                        <p>Chọn chính xác vị trí ghế/giường mong muốn trực tuyến, cam kết không nhồi nhét, không bắt khách dọc đường.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2M7 17h10"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    </div>
                    <div class="feature-info">
                        <h3>Đón Tận Nhà Miễn Phí</h3>
                        <p>Xe trung chuyển đón trả tận cửa trong nội ô TP. Bạc Liêu và các trạm liên kết hoàn toàn miễn phí.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <div class="feature-info">
                        <h3>Xuất Bến Đúng Giờ</h3>
                        <p>Lộ trình chạy thẳng cao tốc, giờ chạy chuẩn xác, kiểm định an toàn kỹ thuật nghiêm ngặt trước mỗi chuyến đi.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div class="feature-info">
                        <h3>Tổng Đài 24/7 Tận Tâm</h3>
                        <p>Đội ngũ trực điện thoại chuyên nghiệp, thân thiện, giải quyết đổi vé, hoàn vé và gửi hàng nhanh chóng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= BỘ LỌC & DANH SÁCH CHUYẾN XE (VEXERE INTERACTIVE ENGINE) ================= -->
    <section class="trip-results-section" id="tripResultsSection">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-header-title">Lịch khởi hành: <span id="resultsRouteHeader" style="color:var(--futa-orange);">Bạc Liêu ➔ <?= e($defaultRoute['ten'] ?? '') ?></span></h2>
                    <p class="section-header-desc">Khởi hành hôm nay · Chọn chuyến ưng ý và bấm <strong>"Chọn chuyến"</strong> để chọn sơ đồ giường nằm trực tuyến.</p>
                </div>
            </div>

            <div class="results-layout">
                <!-- SIDEBAR BỘ LỌC (Vexere Filter Sidebar) -->
                <aside class="filter-card">
                    <div class="filter-card-header">
                        <h3>Bộ lọc tìm kiếm</h3>
                        <button type="button" class="btn-reset-filter" id="btnResetFilters">Xóa tất cả</button>
                    </div>

                    <!-- Khung giờ xuất bến -->
                    <div class="filter-group">
                        <div class="filter-group-title">Giờ khởi hành</div>
                        <div class="filter-options-list">
                            <label class="filter-checkbox-item">
                                <input type="checkbox" name="filterTime" value="early">
                                <span>Sáng sớm (00:00 - 06:00)</span>
                            </label>
                            <label class="filter-checkbox-item">
                                <input type="checkbox" name="filterTime" value="morning">
                                <span>Buổi sáng (06:00 - 12:00)</span>
                            </label>
                            <label class="filter-checkbox-item">
                                <input type="checkbox" name="filterTime" value="afternoon">
                                <span>Buổi chiều (12:00 - 18:00)</span>
                            </label>
                            <label class="filter-checkbox-item">
                                <input type="checkbox" name="filterTime" value="night">
                                <span>Buổi tối (18:00 - 24:00)</span>
                            </label>
                        </div>
                    </div>

                </aside>

                <!-- CỘT DANH SÁCH CHUYẾN XE (DYNAMIC) -->
                <div class="trips-main-col">
                    <!-- Thanh sắp xếp -->
                    <div class="trips-sorting-bar">
                        <div class="trips-count-label">Giờ khởi hành trong dữ liệu: <strong id="resultsCountNumber"><?= count($routeForResults['khoi_hanh'] ?? []) ?></strong></div>
                        <div class="sorting-chips-group">
                            <button type="button" class="sort-chip active" data-sort="time-asc">Giờ đi sớm nhất</button>
                        </div>
                    </div>

                    <div id="tripsListContainer" style="display:grid; gap:16px;">
                        <?php if ($routeForResults): ?>
                            <?php foreach (array_slice($routeForResults['khoi_hanh'], 0, 8) as $gioKhoiHanh): ?>
                                <article class="trip-card" data-departure="<?= e($gioKhoiHanh) ?>" data-route="<?= e($routeForResults['slug']) ?>">
                                    <div class="trip-card-main">
                                        <div class="trip-info-left">
                                            <span class="trip-bus-type-tag">Ghế ngồi / Giường nằm</span>
                                            <div class="trip-timeline">
                                                <div class="timeline-point">
                                                    <span class="time"><?= e(substr($gioKhoiHanh, 0, 5)) ?></span>
                                                    <span class="place">Bạc Liêu</span>
                                                </div>
                                                <div class="timeline-duration-indicator">
                                                    <span><?= duration($routeForResults['gio']) ?></span>
                                                    <div class="timeline-bar"></div>
                                                    <span style="font-size:0.75rem; color:#10b981;">✓ Chạy thẳng</span>
                                                </div>
                                                <div class="timeline-point" style="text-align:right;">
                                                    <span class="time">Theo lịch thực tế</span>
                                                    <span class="place"><?= e($routeForResults['ten']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="trip-action-right">
                                            <span class="trip-seat-availability">Giá từ <?= money(min(array_filter([$routeForResults['ghe'], $routeForResults['giuong']], fn($p) => $p !== null))) ?></span>
                                            <a class="btn btn-primary btn-block" href="DatVe.php?tuyen=<?= e($routeForResults['slug']) ?>&ngay=<?= date('Y-m-d') ?>&gio=<?= e(substr($gioKhoiHanh, 0, 5)) ?>">
                                                Chọn chuyến
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= TUYẾN ĐƯỜNG PHỔ BIẾN (VEXERE + FUTA CARDS) ================= -->
    <section class="routes-section" id="tuyen-duong" style="background:#ffffff;">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-header-title">Tuyến Đường Phổ Biến Của Tài Nguyên</h2>
                    <p class="section-header-desc">Khởi hành hàng ngày từ TP. Bạc Liêu với nhiều chuyến liên tục sáng - chiều - đêm.</p>
                </div>
            </div>

            <div class="routes-cards-grid">
                <?php foreach ($routes as $route): ?>
                    <?php
                    $prices = array_filter([$route['ghe'], $route['giuong']], function ($price) {
                        return $price !== null && $price > 0;
                    });
                    $startingPrice = $prices ? min($prices) : 0;
                    ?>
                    <article class="route-display-card">
                        <div class="route-card-content">
                            <div class="route-header-info">
                                <h3>Bạc Liêu ➔ <?= e($route['ten']) ?></h3>
                                <span><?= (int)$route['km'] ?> km · <?= e(duration($route['gio'])) ?></span>
                            </div>
                            <div class="route-specs-list">
                                <div class="route-spec-item">
                                    <span>Giờ chạy:</span>
                                    <strong><?= $route['khoi_hanh'] ? e(implode(', ', array_slice($route['khoi_hanh'], 0, 8))) : 'Chưa cập nhật' ?></strong>
                                </div>
                                <div class="route-spec-item">
                                    <span>Ghế ngồi:</span>
                                    <strong><?= $route['ghe'] ? money($route['ghe']) : 'Chưa cập nhật' ?></strong>
                                </div>
                                <?php if ($route['giuong'] !== null): ?>
                                <div class="route-spec-item">
                                    <span>Giường nằm:</span>
                                    <strong><?= money($route['giuong']) ?></strong>
                                </div>
                                <?php endif; ?>
                                <div class="route-spec-item">
                                    <span>Giá vé chỉ từ:</span>
                                    <span class="price-highlight"><?= money($startingPrice) ?></span>
                                </div>
                            </div>
                            <a class="btn btn-primary btn-block" href="DatVe.php?tuyen=<?= e($route['slug']) ?>">
                                Đặt vé đi <?= e($route['ten']) ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= PROMOTIONS & VOUCHERS ================= -->
    <section class="promos-section" id="khuyen-mai">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-header-title">Ưu Đãi & Khuyến Mãi Nóng</h2>
                    <p class="section-header-desc">Tiết kiệm hơn khi đặt vé xe Tài Nguyên trực tuyến.</p>
                </div>
            </div>

            <div class="promo-cards-grid">
                <?php if ($promotions): ?>
                    <?php foreach ($promotions as $promotion): ?>
                    <article class="promo-card">
                        <div class="promo-img-box">
                            <span class="promo-badge-tag"><?= e($news_types[$promotion['loai']] ?? 'Khuyến mãi') ?></span>
                            <div style="padding:24px;color:#ffffff;height:100%;display:flex;flex-direction:column;justify-content:center;">
                                <h3 style="font-size:1.4rem;font-weight:800;"><?= e($promotion['tieu_de']) ?></h3>
                                <p style="font-size:0.9rem;opacity:0.9;"><?= e($promotion['tom_tat']) ?></p>
                            </div>
                        </div>
                        <div class="promo-card-body">
                            <h4><?= e($promotion['tieu_de']) ?></h4>
                            <p><?= e($promotion['tom_tat']) ?></p>
                            <div class="promo-card-footer">
                                <span><?= date('d/m/Y', strtotime($promotion['ngay'])) ?></span>
                                <a class="promo-code-pill" href="TinTuc.php?id=<?= (int)$promotion['id'] ?>">Xem chi tiết</a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">Hiện chưa có chương trình khuyến mãi đang được đăng.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ================= DỊCH VỤ TÀI NGUYÊN EXPRESS ================= -->
    <section class="section" id="dich-vu" style="padding:56px 0; background:#ffffff;">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-header-title">Hệ Sinh Thái Dịch Vụ Nhà Xe Tài Nguyên</h2>
                    <p class="section-header-desc">Giải pháp vận tải toàn diện đáp ứng mọi nhu cầu đi lại và gửi đồ của bạn.</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:24px;">
                <?php foreach ($services as $service): ?>
                <article style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:32px 24px; text-align:center;">
                    <div style="width:64px; height:64px; border-radius:50%; background:var(--futa-orange-soft); color:var(--futa-orange); display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                        <?= $service['loai'] === 'trung_chuyen' ? icon('car', 32) : icon('box', 32) ?>
                    </div>
                    <h3 style="font-size:1.25rem; font-weight:700; color:var(--brand-navy); margin-bottom:10px;"><?= e($service['ten']) ?></h3>
                    <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.6; margin-bottom:12px;"><?= e($service['mota']) ?></p>
                    <?php if ($service['gia'] > 0): ?>
                        <p class="price-highlight">Từ <?= money($service['gia']) ?></p>
                    <?php endif; ?>
                    <a href="DatDichVu.php?service=<?= e($service['loai']) ?>" class="btn btn-outline-orange btn-block">Đặt dịch vụ</a>
                </article>
                <?php endforeach; ?>
                <?php if (!$services): ?>
                    <p class="empty">Hiện chưa có dịch vụ nào được mở.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ================= MODAL: CHỌN THÀNH PHỐ (VEXERE STYLE) ================= -->
    <div class="modal-backdrop" id="cityModal">
        <div class="modal-content-card">
            <div class="modal-header">
                <h3 id="cityModalTitle">Chọn Tỉnh / Thành phố</h3>
                <button class="modal-close-btn" id="closeCityModal" type="button" aria-label="Đóng">&times;</button>
            </div>
            <div class="modal-search-input-box">
                <input type="text" id="citySearchInput" placeholder="Tìm kiếm nhanh tỉnh, thành phố (ví dụ: Sài Gòn, Đà Lạt...)">
            </div>
            <div class="modal-cities-grid" id="cityModalList">
                <!-- Được sinh bằng JS -->
            </div>
        </div>
    </div>

    <!-- ================= TOAST THÔNG BÁO ================= -->
    <div class="toast-notice" id="toastNotice">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f26522" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span id="toastMessage">Thông báo</span>
    </div>

    <script>
        window.TAI_NGUYEN_ROUTES = <?= $homeRoutesJson ?: '[]' ?>;
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
