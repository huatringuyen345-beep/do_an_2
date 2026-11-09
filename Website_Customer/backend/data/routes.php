<?php
/* Dữ liệu tuyến xe tham khảo; cần đối chiếu với lịch chạy và giá thực tế. */
$routes = [
    [
        'slug' => 'ca-mau', 'ten' => 'Cà Mau', 'nhom' => 'Đồng bằng sông Cửu Long',
        'km' => 65, 'gio' => 1.5, 'ghe' => 60000, 'giuong' => null,
        'khoi_hanh' => ['05:30', '07:30', '09:30', '11:30', '13:30', '15:30', '17:30'],
    ],
    [
        'slug' => 'soc-trang', 'ten' => 'Sóc Trăng', 'nhom' => 'Đồng bằng sông Cửu Long',
        'km' => 60, 'gio' => 1.5, 'ghe' => 60000, 'giuong' => null,
        'khoi_hanh' => ['05:00', '07:00', '09:00', '11:00', '13:00', '15:00', '17:00'],
    ],
    [
        'slug' => 'can-tho', 'ten' => 'Cần Thơ', 'nhom' => 'Đồng bằng sông Cửu Long',
        'km' => 120, 'gio' => 2.5, 'ghe' => 110000, 'giuong' => null,
        'khoi_hanh' => ['05:00', '07:00', '09:00', '11:00', '13:00', '15:00', '17:00'],
    ],
    [
        'slug' => 'rach-gia', 'ten' => 'Rạch Giá', 'nhom' => 'Đồng bằng sông Cửu Long',
        'km' => 190, 'gio' => 4, 'ghe' => 140000, 'giuong' => null,
        'khoi_hanh' => ['05:30', '08:00', '11:00', '14:00'],
    ],
    [
        'slug' => 'tp-hcm', 'ten' => 'TP. Hồ Chí Minh', 'nhom' => 'Đông Nam Bộ',
        'km' => 280, 'gio' => 6, 'ghe' => 200000, 'giuong' => 250000,
        'khoi_hanh' => ['05:00', '07:30', '10:00', '13:00', '17:00', '20:30', '22:00'],
    ],
    [
        'slug' => 'binh-duong', 'ten' => 'Bình Dương', 'nhom' => 'Đông Nam Bộ',
        'km' => 320, 'gio' => 7, 'ghe' => 230000, 'giuong' => 280000,
        'khoi_hanh' => ['06:00', '09:00', '13:30', '19:00', '21:30'],
    ],
    [
        'slug' => 'vung-tau', 'ten' => 'Vũng Tàu', 'nhom' => 'Đông Nam Bộ',
        'km' => 360, 'gio' => 8, 'ghe' => 260000, 'giuong' => 310000,
        'khoi_hanh' => ['05:30', '09:30', '18:00', '21:00'],
    ],
    [
        'slug' => 'da-lat', 'ten' => 'Đà Lạt', 'nhom' => 'Tây Nguyên và Nam Trung Bộ',
        'km' => 530, 'gio' => 12, 'ghe' => 330000, 'giuong' => 400000,
        'khoi_hanh' => ['17:00', '19:00', '20:30'],
    ],
    [
        'slug' => 'nha-trang', 'ten' => 'Nha Trang', 'nhom' => 'Tây Nguyên và Nam Trung Bộ',
        'km' => 700, 'gio' => 15, 'ghe' => 380000, 'giuong' => 450000,
        'khoi_hanh' => ['15:00', '17:00', '18:30'],
    ],
];

function find_route($slug)
{
    global $routes;
    foreach ($routes as $route) {
        if ($route['slug'] === $slug) {
            return $route;
        }
    }
    return null;
}

function min_price($type)
{
    global $routes;
    $prices = array_filter(array_column($routes, $type), function ($price) {
        return $price !== null;
    });
    return $prices ? min($prices) : 0;
}
