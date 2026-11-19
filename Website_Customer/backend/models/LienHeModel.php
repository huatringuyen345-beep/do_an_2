<?php
/*
 * LienHeModel.php
 * Model quan ly phan hoi va tin nhan lien he cua khach hang
 */

class LienHeModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Gui mot tin nhan lien he moi
     */
    public function guiLienHe(
        ?int $userId,
        string $hoTen,
        string $email,
        string $soDienThoai,
        string $tieuDe,
        string $noiDung
    ): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO lien_he (ma_nguoi_dung, ho_ten, email, so_dien_thoai, tieu_de, noi_dung, trang_thai)
             VALUES (?, ?, ?, ?, ?, ?, 'moi')"
        );
        $stmt->bind_param('isssss', $userId, $hoTen, $email, $soDienThoai, $tieuDe, $noiDung);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Lay danh sach tin nhan lien he cua mot nguoi dung
     */
    public function layLienHeTheoUser(int $userId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT ma_lien_he, tieu_de, noi_dung, trang_thai, noi_dung_phan_hoi, ngay_gui, ngay_phan_hoi
             FROM lien_he
             WHERE ma_nguoi_dung = ?
             ORDER BY ngay_gui DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }
}
