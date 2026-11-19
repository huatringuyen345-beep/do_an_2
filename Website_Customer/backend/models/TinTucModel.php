<?php

class TinTucModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function layDanhSach(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT ma_tin_tuc, loai_tin, DATE_FORMAT(COALESCE(ngay_dang, ngay_tao), '%Y-%m-%d') AS ngay,
                    ngay_ket_thuc, tieu_de, tom_tat, noi_dung
             FROM tin_tuc
             WHERE trang_thai = 'da_dang'
               AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())
             ORDER BY COALESCE(ngay_dang, ngay_tao) DESC, ma_tin_tuc DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $content = preg_replace('/<\/p>\s*<p\b[^>]*>|<br\s*\/?>/i', "\n", (string)$row['noi_dung']);
            $content = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $paragraphs = preg_split('/\R+/', $content);
            $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), function ($paragraph) {
                return $paragraph !== '';
            }));

            $news[] = [
                'id' => (int)$row['ma_tin_tuc'],
                'loai' => str_replace('_', '-', $row['loai_tin']),
                'ngay' => $row['ngay'],
                'ket_thuc' => $row['ngay_ket_thuc'],
                'tieu_de' => $row['tieu_de'],
                'tom_tat' => $row['tom_tat'] ?? '',
                'noi_dung' => $paragraphs,
            ];
        }
        $stmt->close();

        return $news;
    }
}
