<?php
/*
 * Nap noi dung khach hang tu co so du lieu va giu cau truc du lieu
 * dang duoc cac trang va controller su dung.
 */
require_once __DIR__ . '/../database/config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new RuntimeException('Khong the tai noi dung vi chua ket noi duoc co so du lieu.');
}

$payments = [];
$news_types = [
    'tin-tuc' => 'Tin tức',
    'su-kien' => 'Sự kiện',
    'khuyen-mai' => 'Khuyến mãi',
    'thong-bao' => 'Thông báo',
];
$news = [];
$jobs = [];
$faqs = [];

try {
    $cauLenh = $conn->prepare(
        "SELECT ma_phuong_thuc, ten_phuong_thuc, mo_ta, icon
         FROM phuong_thuc_thanh_toan
         WHERE trang_thai = 'hoat_dong'
         ORDER BY ma_phuong_thuc"
    );
    $cauLenh->execute();
    $ketQua = $cauLenh->get_result();
    while ($dong = $ketQua->fetch_assoc()) {
        $payments[$dong['ma_phuong_thuc']] = [
            'ten' => $dong['ten_phuong_thuc'],
            'mota' => $dong['mo_ta'] ?? '',
            'icon' => $dong['icon'] ?? '',
        ];
    }
    $cauLenh->close();

    $cauLenh = $conn->prepare(
        "SELECT ma_tin_tuc, loai_tin, DATE_FORMAT(COALESCE(ngay_dang, ngay_tao), '%Y-%m-%d') AS ngay,
                ngay_ket_thuc, tieu_de, tom_tat, noi_dung
         FROM tin_tuc
         WHERE trang_thai = 'da_dang'
           AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())
         ORDER BY COALESCE(ngay_dang, ngay_tao) DESC, ma_tin_tuc DESC"
    );
    $cauLenh->execute();
    $ketQua = $cauLenh->get_result();
    while ($dong = $ketQua->fetch_assoc()) {
        $noiDung = preg_replace('/<\/p>\s*<p\b[^>]*>|<br\s*\/?>/i', "\n", (string) $dong['noi_dung']);
        $noiDung = html_entity_decode(strip_tags($noiDung), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cacDoan = preg_split('/\R+/', $noiDung);
        $cacDoan = array_values(array_filter(array_map('trim', $cacDoan), function ($doan) {
            return $doan !== '';
        }));

        $news[] = [
            'id' => (int) $dong['ma_tin_tuc'],
            'loai' => str_replace('_', '-', $dong['loai_tin']),
            'ngay' => $dong['ngay'],
            'ket_thuc' => $dong['ngay_ket_thuc'],
            'tieu_de' => $dong['tieu_de'],
            'tom_tat' => $dong['tom_tat'] ?? '',
            'noi_dung' => $cacDoan,
        ];
    }
    $cauLenh->close();

    $cauLenh = $conn->prepare(
        "SELECT slug, ten_vi_tri, so_luong, dia_diem, muc_luong, mo_ta, yeu_cau
         FROM tuyen_dung
         WHERE trang_thai = 'mo_tuyen'
         ORDER BY ma_tuyen_dung"
    );
    $cauLenh->execute();
    $ketQua = $cauLenh->get_result();
    while ($dong = $ketQua->fetch_assoc()) {
        $yeuCau = json_decode($dong['yeu_cau'], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($yeuCau)) {
            throw new UnexpectedValueException('Danh sach yeu cau tuyen dung khong hop le.');
        }
        foreach ($yeuCau as $mucYeuCau) {
            if (!is_string($mucYeuCau)) {
                throw new UnexpectedValueException('Yeu cau tuyen dung phai la chuoi van ban.');
            }
        }

        $jobs[] = [
            'ma' => $dong['slug'],
            'ten' => $dong['ten_vi_tri'],
            'sl' => (int) $dong['so_luong'],
            'noi' => $dong['dia_diem'],
            'luong' => $dong['muc_luong'] ?? '',
            'mota' => $dong['mo_ta'],
            'yeu_cau' => array_values($yeuCau),
        ];
    }
    $cauLenh->close();

    $cauLenh = $conn->prepare(
        "SELECT cau_hoi, cau_tra_loi
         FROM hoi_dap
         WHERE trang_thai = 'hien_thi'
         ORDER BY thu_tu, ma_faq"
    );
    $cauLenh->execute();
    $ketQua = $cauLenh->get_result();
    while ($dong = $ketQua->fetch_assoc()) {
        $faqs[] = [
            'q' => $dong['cau_hoi'],
            'a' => $dong['cau_tra_loi'],
        ];
    }
    $cauLenh->close();
} catch (Throwable $loi) {
    error_log('Loi khi tai noi dung khach hang tu co so du lieu: ' . $loi->getMessage());
    throw new RuntimeException('Khong the tai noi dung khach hang tu co so du lieu.', 0, $loi);
}

function find_news($id)
{
    global $news;

    foreach ($news as $mucTin) {
        if ((int) $mucTin['id'] === (int) $id) {
            return $mucTin;
        }
    }

    return null;
}