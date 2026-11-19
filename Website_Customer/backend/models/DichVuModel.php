<?php
/*
 * DichVuModel.php
 * Model quan ly dich vu van chuyen hang hoa, trung chuyen va don dat dich vu
 */

class DichVuModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Lay danh sach tat ca cac dich vu dang hoat dong
     */
    public function layDanhSachDichVuHoatDong(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT ma_dich_vu, ten_dich_vu, loai_dich_vu, mo_ta, gia_tham_khao
             FROM dich_vu
             WHERE trang_thai = 'hoat_dong'
             ORDER BY ma_dich_vu"
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * Lay danh sach cac dich vu dang hoat dong loc theo danh sach phan loai
     */
    public function layDanhSachTheoLoai(array $loaiDichVu): array
    {
        if (empty($loaiDichVu)) {
            return $this->layDanhSachDichVuHoatDong();
        }

        $placeholders = implode(',', array_fill(0, count($loaiDichVu), '?'));
        $types = str_repeat('s', count($loaiDichVu));

        $stmt = $this->conn->prepare(
            "SELECT ma_dich_vu, ten_dich_vu, loai_dich_vu, gia_tham_khao, mo_ta
             FROM dich_vu
             WHERE trang_thai = 'hoat_dong' AND loai_dich_vu IN ($placeholders)
             ORDER BY ma_dich_vu"
        );
        $stmt->bind_param($types, ...$loaiDichVu);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * Tim mot dich vu cu the theo ID, co the loc theo phan loai cho phep
     */
    public function timDichVuTheoId(int $maDichVu, array $loaiChoPhep = []): ?array
    {
        if (!empty($loaiChoPhep)) {
            $placeholders = implode(',', array_fill(0, count($loaiChoPhep), '?'));
            $types = 'i' . str_repeat('s', count($loaiChoPhep));
            $params = array_merge([$maDichVu], $loaiChoPhep);

            $stmt = $this->conn->prepare(
                "SELECT ma_dich_vu, ten_dich_vu, loai_dich_vu, gia_tham_khao, mo_ta
                 FROM dich_vu
                 WHERE ma_dich_vu = ? AND trang_thai = 'hoat_dong' AND loai_dich_vu IN ($placeholders)
                 LIMIT 1"
            );
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->conn->prepare(
                'SELECT ma_dich_vu, ten_dich_vu, loai_dich_vu, gia_tham_khao, mo_ta
                 FROM dich_vu
                 WHERE ma_dich_vu = ? AND trang_thai = "hoat_dong"
                 LIMIT 1'
            );
            $stmt->bind_param('i', $maDichVu);
        }

        $stmt->execute();
        $dichVu = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $dichVu ?: null;
    }

    /**
     * Tao don dat dich vu moi
     */
    public function taoDonDatDichVu(
        string $maDatDichVu,
        ?int $userId,
        int $maDichVu,
        string $hoTen,
        string $soDienThoai,
        string $ngaySuDung,
        int $soLuong,
        float $tongTien,
        string $diaChiDon,
        string $diaChiNhan,
        string $ghiChu
    ): int {
        $stmt = $this->conn->prepare(
            'INSERT INTO dat_dich_vu (ma_dat_dich_vu, ma_nguoi_dung, ma_dich_vu, ho_ten, so_dien_thoai, ngay_su_dung, so_luong, tong_tien, dia_chi_don, dia_chi_nhan, trang_thai, ghi_chu)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "cho_xu_ly", ?)'
        );
        $stmt->bind_param('siisssidsss', $maDatDichVu, $userId, $maDichVu, $hoTen, $soDienThoai, $ngaySuDung, $soLuong, $tongTien, $diaChiDon, $diaChiNhan, $ghiChu);
        $stmt->execute();
        $id = (int)$this->conn->insert_id;
        $stmt->close();

        return $id;
    }

    /**
     * Tao chi tiet kien hang van chuyen
     */
    public function taoChiTietHang(
        int $maDatDichVuId,
        string $tenNguoiGui,
        string $sdtNguoiGui,
        string $tenNguoiNhan,
        string $sdtNguoiNhan,
        string $loaiHang,
        string $moTaHang,
        float $khoiLuongKg,
        float $giaTriKhaiBao,
        string $maVanDon
    ): bool {
        $stmt = $this->conn->prepare(
            'INSERT INTO chi_tiet_hang (ma_dat_dich_vu_id, ten_nguoi_gui, sdt_nguoi_gui, ten_nguoi_nhan, sdt_nguoi_nhan, loai_hang, mo_ta_hang, khoi_luong_kg, gia_tri_khai_bao, ma_van_don, trang_thai_van_don)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "cho_lay_hang")'
        );
        $stmt->bind_param('issssssdds', $maDatDichVuId, $tenNguoiGui, $sdtNguoiGui, $tenNguoiNhan, $sdtNguoiNhan, $loaiHang, $moTaHang, $khoiLuongKg, $giaTriKhaiBao, $maVanDon);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
