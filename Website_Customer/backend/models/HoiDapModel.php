<?php

class HoiDapModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function layDanhSach(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT cau_hoi, cau_tra_loi
             FROM hoi_dap
             WHERE trang_thai = 'hien_thi'
             ORDER BY thu_tu, ma_faq"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = ['q' => $row['cau_hoi'], 'a' => $row['cau_tra_loi']];
        }
        $stmt->close();

        return $faqs;
    }
}
