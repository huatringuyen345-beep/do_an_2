<?php
/*
 * Nap tuyen xe va gio khoi hanh tu co so du lieu.
 * Cac trang su dung cung mot cau truc du lieu de hien thi va xu ly dat ve.
 */
require_once __DIR__ . '/../database/config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new RuntimeException('Khong the tai tuyen xe vi chua ket noi duoc co so du lieu.');
}

$routes = [];

try {
    $cauLenh = $conn->prepare(
        "SELECT td.slug, td.ten_tuyen, td.nhom_tuyen, td.khoang_cach_km,
                td.thoi_gian_phut, td.gia_ve_co_ban, td.gia_ve_giuong_nam,
                TIME_FORMAT(gkh.gio_chay, '%H:%i') AS gio_chay
         FROM tuyen_duong td
         INNER JOIN diem_dung diem_di ON diem_di.ma_diem_dung = td.ma_diem_di
         LEFT JOIN gio_khoi_hanh_tuyen gkh ON gkh.ma_tuyen = td.ma_tuyen
         WHERE diem_di.tinh_thanh = ? AND td.trang_thai = 'hoat_dong'
           AND td.slug IS NOT NULL AND td.slug <> ''
         ORDER BY td.ma_tuyen, gkh.gio_chay"
    );
    $tinhKhoiHanh = 'Bạc Liêu';
    $cauLenh->bind_param('s', $tinhKhoiHanh);
    $cauLenh->execute();
    $ketQua = $cauLenh->get_result();

    while ($dong = $ketQua->fetch_assoc()) {
        $slug = $dong['slug'];
        if (!isset($routes[$slug])) {
            $tenTuyen = (string) $dong['ten_tuyen'];
            $viTriPhanCach = strrpos($tenTuyen, ' - ');
            $tenDiemDen = $viTriPhanCach === false
                ? $tenTuyen
                : substr($tenTuyen, $viTriPhanCach + 3);

            $routes[$slug] = [
                'slug' => $slug,
                'ten' => $tenDiemDen,
                'nhom' => $dong['nhom_tuyen'] ?? '',
                'km' => (float) $dong['khoang_cach_km'],
                'gio' => (float) $dong['thoi_gian_phut'] / 60,
                'ghe' => (float) $dong['gia_ve_co_ban'],
                'giuong' => $dong['gia_ve_giuong_nam'] === null
                    ? null
                    : (float) $dong['gia_ve_giuong_nam'],
                'khoi_hanh' => [],
            ];
        }

        if ($dong['gio_chay'] !== null) {
            $routes[$slug]['khoi_hanh'][] = $dong['gio_chay'];
        }
    }

    $cauLenh->close();
    $routes = array_values($routes);
    if (!$routes) {
        throw new RuntimeException('Khong tim thay tuyen hoat dong xuat phat tu Bac Lieu.');
    }
} catch (Throwable $loi) {
    error_log('Loi khi tai tuyen xe tu co so du lieu: ' . $loi->getMessage());
    throw new RuntimeException('Khong the tai danh sach tuyen xe tu co so du lieu.', 0, $loi);
}

function find_route($slug)
{
    global $routes;

    foreach ($routes as $tuyenDuong) {
        if ($tuyenDuong['slug'] === $slug) {
            return $tuyenDuong;
        }
    }

    return null;
}

function min_price($type)
{
    global $routes;

    if (!in_array($type, ['ghe', 'giuong'], true)) {
        return 0;
    }

    $cacMucGia = array_filter(array_column($routes, $type), function ($mucGia) {
        return $mucGia !== null;
    });

    return $cacMucGia ? min($cacMucGia) : 0;
}