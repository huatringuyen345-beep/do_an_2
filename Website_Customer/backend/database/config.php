<?php
// Ket noi den co so du lieu MySQL.
$mayChu = getenv('WEBSITE_DB_HOST') ?: '127.0.0.1';
$tenNguoiDung = getenv('WEBSITE_DB_USER') ?: 'root';
$matKhau = getenv('WEBSITE_DB_PASSWORD') ?: '';
$tenCoSoDuLieu = getenv('WEBSITE_DB_NAME') ?: 'website_tainguyen';

if (!isset($conn) || !($conn instanceof mysqli)) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($mayChu, $tenNguoiDung, $matKhau, $tenCoSoDuLieu);
        $conn->set_charset('utf8mb4');
    } catch (Throwable $loi) {
        // Neu co so du lieu chua ton tai, thu tao va nap schema dung chung o thu muc goc.
        try {
            $ketNoiTam = new mysqli($mayChu, $tenNguoiDung, $matKhau);
            $ketNoiTam->set_charset('utf8mb4');
            $ketNoiTam->query("CREATE DATABASE IF NOT EXISTS `$tenCoSoDuLieu` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $ketNoiTam->select_db($tenCoSoDuLieu);

            $ketQuaKiemTra = $ketNoiTam->query("SHOW TABLES LIKE 'vai_tro'");
            if ($ketQuaKiemTra && $ketQuaKiemTra->num_rows === 0) {
                $duongDanTepSql = __DIR__ . '/../../../database/website_tainguyen.sql';
                if (file_exists($duongDanTepSql)) {
                    $noiDungSql = file_get_contents($duongDanTepSql);
                    $ketNoiTam->multi_query($noiDungSql);
                    while ($ketNoiTam->more_results() && $ketNoiTam->next_result()) {;}
                }
            }
            $conn = $ketNoiTam;
        } catch (Throwable $loiKetNoiTam) {
            $conn = null;
            error_log("Loi ket noi co so du lieu: " . $loiKetNoiTam->getMessage());
        }
    }
}
