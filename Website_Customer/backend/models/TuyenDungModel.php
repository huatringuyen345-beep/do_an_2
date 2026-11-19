<?php

class TuyenDungModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function layDanhSach(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT slug, ten_vi_tri, so_luong, dia_diem, muc_luong, mo_ta, yeu_cau
             FROM tuyen_dung
             WHERE trang_thai = 'mo_tuyen'
             ORDER BY ma_tuyen_dung"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $requirements = json_decode($row['yeu_cau'], true, 512, JSON_THROW_ON_ERROR);
            $jobs[] = [
                'ma' => $row['slug'],
                'ten' => $row['ten_vi_tri'],
                'sl' => (int)$row['so_luong'],
                'noi' => $row['dia_diem'],
                'luong' => $row['muc_luong'] ?? '',
                'mota' => $row['mo_ta'],
                'yeu_cau' => is_array($requirements) ? array_values($requirements) : [],
            ];
        }
        $stmt->close();

        return $jobs;
    }

    public function luuHoSoUngTuyen(string $jobSlug, string $fullName, string $phone, string $email, string $introduction): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO ho_so_ung_tuyen (slug_vi_tri, ho_ten, so_dien_thoai, email, gioi_thieu)
             SELECT slug, ?, ?, ?, ?
             FROM tuyen_dung
             WHERE slug = ? AND trang_thai = "mo_tuyen"
             LIMIT 1'
        );
        $stmt->bind_param('sssss', $fullName, $phone, $email, $introduction, $jobSlug);
        $success = $stmt->execute();
        $inserted = $stmt->affected_rows === 1;
        $stmt->close();

        if (!$success || !$inserted) {
            throw new RuntimeException('Không lưu được hồ sơ hoặc vị trí tuyển dụng đã đóng.');
        }

        return true;
    }
}
