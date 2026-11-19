<?php
/*
 * TuyenDuongModel.php
 * Model quan ly tuyen duong, diem den va gio khoi hanh
 */

class TuyenDuongModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Lay tat ca tuyen xe hoat dong xuat phat tu mot tinh/thanh (mac dinh: Bac Lieu)
     */
    public function layTatCaTuyen(string $tinhKhoiHanh = 'Bạc Liêu'): array
    {
        $stmt = $this->conn->prepare(
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
        $stmt->bind_param('s', $tinhKhoiHanh);
        $stmt->execute();
        $ketQua = $stmt->get_result();

        $routes = [];
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
        $stmt->close();

        return array_values($routes);
    }

    public function findRoute(string $slug, array $routes): ?array
    {
        foreach ($routes as $route) {
            if ($route['slug'] === $slug) {
                return $route;
            }
        }

        return null;
    }

    public function minPrice(string $type, array $routes): float
    {
        if (!in_array($type, ['ghe', 'giuong'], true)) {
            return 0;
        }

        $prices = array_filter(array_column($routes, $type), function ($price) {
            return $price !== null;
        });

        return $prices ? (float)min($prices) : 0;
    }
}
