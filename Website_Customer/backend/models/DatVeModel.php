<?php
/*
 * DatVeModel.php
 * Model quan ly don dat ve va giao dich thanh toan
 */

class DatVeModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Tao mot don dat ve moi
     */
    public function taoDonDatVe(
        string $maDatVe,
        ?int $userId,
        string $tenKhachHang,
        string $sdtKhachHang,
        string $emailKhachHang,
        float $tongTien,
        string $ghiChu
    ): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO dat_ve (ma_dat_ve, ma_nguoi_dung, ten_khach_hang, sdt_khach_hang, email_khach_hang, tong_tien, thanh_tien, trang_thai, ghi_chu)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan', ?)"
        );
        $stmt->bind_param('sisssdds', $maDatVe, $userId, $tenKhachHang, $sdtKhachHang, $emailKhachHang, $tongTien, $tongTien, $ghiChu);
        $stmt->execute();
        $id = (int)$this->conn->insert_id;
        $stmt->close();

        return $id;
    }

    public function layThongTinChoNgoi(string $routeSlug, string $ngay, string $gio, string $loai): array
    {
        $gioKhoiHanh = $ngay . ' ' . $gio . ':00';
        $stmt = $this->conn->prepare(
            "SELECT cx.ma_chuyen, cx.trang_thai, lx.so_ghe AS suc_chua
             FROM chuyen_xe cx
             INNER JOIN tuyen_duong td ON td.ma_tuyen = cx.ma_tuyen
             INNER JOIN xe x ON x.ma_xe = cx.ma_xe
             INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
             WHERE td.slug = ? AND cx.gio_khoi_hanh = ?
               AND ((? = 'ghe' AND (LOWER(lx.ten_loai_xe) LIKE '%ghế%' OR LOWER(lx.ten_loai_xe) LIKE '%limousine%'))
                    OR (? = 'giuong' AND (LOWER(lx.ten_loai_xe) LIKE '%giường%' OR LOWER(lx.ten_loai_xe) LIKE '%phòng%')))
             ORDER BY cx.ma_chuyen
             LIMIT 1"
        );
        $stmt->bind_param('ssss', $routeSlug, $gioKhoiHanh, $loai, $loai);
        $stmt->execute();
        $trip = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($trip) {
            $tripId = (int)$trip['ma_chuyen'];
            $capacity = (int)$trip['suc_chua'];
            $status = $trip['trang_thai'];
            $taken = $this->layMaGheDaDat($tripId);
        } else {
            $vehicleStmt = $this->conn->prepare(
                "SELECT lx.so_ghe AS suc_chua
                 FROM xe x
                 INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
                 WHERE x.trang_thai = 'hoat_dong'
                   AND ((? = 'ghe' AND (LOWER(lx.ten_loai_xe) LIKE '%ghế%' OR LOWER(lx.ten_loai_xe) LIKE '%limousine%'))
                        OR (? = 'giuong' AND (LOWER(lx.ten_loai_xe) LIKE '%giường%' OR LOWER(lx.ten_loai_xe) LIKE '%phòng%')))
                 ORDER BY x.ma_xe
                 LIMIT 1"
            );
            $vehicleStmt->bind_param('ss', $loai, $loai);
            $vehicleStmt->execute();
            $vehicle = $vehicleStmt->get_result()->fetch_assoc();
            $vehicleStmt->close();
            if (!$vehicle) {
                throw new RuntimeException('Không tìm thấy xe phù hợp để xác định sức chứa.');
            }

            $tripId = null;
            $capacity = (int)$vehicle['suc_chua'];
            $status = 'mo_ban';
            $taken = [];
        }

        return [
            'ma_chuyen' => $tripId,
            'trang_thai' => $status,
            'suc_chua' => $capacity,
            'so_ghe_da_dat' => count($taken),
            'so_ghe_con_trong' => max(0, $capacity - count($taken)),
            'ghe_da_dat' => $taken,
        ];
    }

    private function layMaGheDaDat(int $tripId, bool $lock = false): array
    {
        $stmt = $this->conn->prepare(
            "SELECT gx.so_ghe
             FROM chi_tiet_ve ct
             INNER JOIN ghe_xe gx ON gx.ma_ghe = ct.ma_ghe
             WHERE ct.ma_chuyen = ? AND ct.trang_thai IN ('giu_cho', 'da_xac_nhan')
             ORDER BY gx.so_ghe" . ($lock ? ' FOR UPDATE' : '')
        );
        $stmt->bind_param('i', $tripId);
        $stmt->execute();
        $result = $stmt->get_result();
        $seats = [];
        while ($row = $result->fetch_assoc()) {
            $seats[] = $row['so_ghe'];
        }
        $stmt->close();

        return $seats;
    }

    public function kiemTraChoNgoiTrong(int $tripId, array $seatCodes): int
    {
        $stmt = $this->conn->prepare(
            'SELECT cx.trang_thai, lx.so_ghe AS suc_chua
             FROM chuyen_xe cx
             INNER JOIN xe x ON x.ma_xe = cx.ma_xe
             INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
             WHERE cx.ma_chuyen = ?
             FOR UPDATE'
        );
        $stmt->bind_param('i', $tripId);
        $stmt->execute();
        $trip = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$trip) {
            throw new RuntimeException('Không tìm thấy chuyến xe.');
        }
        if (!in_array($trip['trang_thai'], ['mo_ban', 'het_cho'], true)) {
            throw new DomainException('Chuyến xe không còn mở bán.');
        }

        $taken = $this->layMaGheDaDat($tripId, true);
        if (array_intersect($seatCodes, $taken)) {
            throw new DomainException('Một hoặc nhiều ghế vừa được khách khác đặt. Vui lòng chọn lại.');
        }
        $capacity = (int)$trip['suc_chua'];
        if (count($taken) + count($seatCodes) > $capacity) {
            throw new DomainException('Chuyến xe không còn đủ chỗ. Vui lòng tải lại sơ đồ ghế.');
        }

        return $capacity;
    }

    public function capNhatTrangThaiChoNgoi(int $tripId): void
    {
        $stmt = $this->conn->prepare(
            'SELECT cx.trang_thai, lx.so_ghe AS suc_chua
             FROM chuyen_xe cx
             INNER JOIN xe x ON x.ma_xe = cx.ma_xe
             INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
             WHERE cx.ma_chuyen = ?'
        );
        $stmt->bind_param('i', $tripId);
        $stmt->execute();
        $trip = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$trip) {
            throw new RuntimeException('Không tìm thấy chuyến xe để cập nhật tình trạng chỗ.');
        }

        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT ct.ma_ghe) AS so_ghe_da_dat
             FROM chi_tiet_ve ct
             WHERE ct.ma_chuyen = ? AND ct.trang_thai IN ('giu_cho', 'da_xac_nhan')"
        );
        $stmt->bind_param('i', $tripId);
        $stmt->execute();
        $booked = (int)$stmt->get_result()->fetch_assoc()['so_ghe_da_dat'];
        $stmt->close();

        $currentStatus = $trip['trang_thai'];
        $nextStatus = $currentStatus;
        if ($booked >= (int)$trip['suc_chua']) {
            $nextStatus = 'het_cho';
        } elseif ($currentStatus === 'het_cho') {
            $nextStatus = 'mo_ban';
        }
        if ($nextStatus !== $currentStatus) {
            $stmt = $this->conn->prepare('UPDATE chuyen_xe SET trang_thai = ? WHERE ma_chuyen = ?');
            $stmt->bind_param('si', $nextStatus, $tripId);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function taoHoacLayChuyen(string $routeSlug, string $ngay, string $gio, string $loai, float $giaVe): int
    {
        $gioKhoiHanh = $ngay . ' ' . $gio . ':00';

        $stmt = $this->conn->prepare(
            "SELECT cx.ma_chuyen, cx.trang_thai
             FROM chuyen_xe cx
             INNER JOIN tuyen_duong td ON td.ma_tuyen = cx.ma_tuyen
             INNER JOIN xe x ON x.ma_xe = cx.ma_xe
             INNER JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
             WHERE td.slug = ? AND cx.gio_khoi_hanh = ?
               AND ((? = 'ghe' AND (LOWER(lx.ten_loai_xe) LIKE '%ghế%' OR LOWER(lx.ten_loai_xe) LIKE '%limousine%'))
                    OR (? = 'giuong' AND (LOWER(lx.ten_loai_xe) LIKE '%giường%' OR LOWER(lx.ten_loai_xe) LIKE '%phòng%')))
             ORDER BY cx.ma_chuyen
             LIMIT 1"
        );
        $stmt->bind_param('ssss', $routeSlug, $gioKhoiHanh, $loai, $loai);
        $stmt->execute();
        $trip = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($trip) {
            if (!in_array($trip['trang_thai'], ['mo_ban', 'het_cho'], true)) {
                throw new DomainException('Chuyến xe không còn mở bán.');
            }
            return (int) $trip['ma_chuyen'];
        }

        $stmt = $this->conn->prepare(
            "SELECT td.ma_tuyen, x.ma_xe
             FROM tuyen_duong td
             JOIN xe x ON x.trang_thai = 'hoat_dong'
             JOIN loai_xe lx ON lx.ma_loai_xe = x.ma_loai_xe
             WHERE td.slug = ?
               AND ((? = 'ghe' AND (LOWER(lx.ten_loai_xe) LIKE '%ghế%' OR LOWER(lx.ten_loai_xe) LIKE '%limousine%'))
                    OR (? = 'giuong' AND (LOWER(lx.ten_loai_xe) LIKE '%giường%' OR LOWER(lx.ten_loai_xe) LIKE '%phòng%')))
             ORDER BY x.ma_xe
             LIMIT 1"
        );
        $stmt->bind_param('sss', $routeSlug, $loai, $loai);
        $stmt->execute();
        $fleet = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$fleet) {
            throw new RuntimeException('Không tìm thấy phương tiện phù hợp cho tuyến này.');
        }

        $arrival = date('Y-m-d H:i:s', strtotime($gioKhoiHanh) + 6 * 3600);
        $status = 'mo_ban';
        $stmt = $this->conn->prepare(
            "INSERT INTO chuyen_xe (ma_tuyen, ma_xe, gio_khoi_hanh, gio_den_du_kien, gia_ve, trang_thai)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iissds', $fleet['ma_tuyen'], $fleet['ma_xe'], $gioKhoiHanh, $arrival, $giaVe, $status);
        $stmt->execute();
        $tripId = (int) $this->conn->insert_id;
        $stmt->close();

        return $tripId;
    }

    public function layHoacTaoMaGhe(int $tripId, string $seatCode): int
    {
        $stmt = $this->conn->prepare(
            "SELECT x.ma_xe FROM chuyen_xe cx INNER JOIN xe x ON x.ma_xe = cx.ma_xe WHERE cx.ma_chuyen = ?"
        );
        $stmt->bind_param('i', $tripId);
        $stmt->execute();
        $trip = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$trip) {
            throw new RuntimeException('Không tìm thấy chuyến xe.');
        }

        $vehicleId = (int) $trip['ma_xe'];
        $stmt = $this->conn->prepare('SELECT ma_ghe FROM ghe_xe WHERE ma_xe = ? AND so_ghe = ? LIMIT 1');
        $stmt->bind_param('is', $vehicleId, $seatCode);
        $stmt->execute();
        $seat = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($seat) {
            return (int) $seat['ma_ghe'];
        }

        $floor = str_starts_with($seatCode, 'T') ? 2 : 1;
        $stmt = $this->conn->prepare('INSERT INTO ghe_xe (ma_xe, so_ghe, tang) VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $vehicleId, $seatCode, $floor);
        $stmt->execute();
        $seatId = (int) $this->conn->insert_id;
        $stmt->close();
        return $seatId;
    }

    public function taoChiTietVe(int $bookingId, int $tripId, int $seatId, float $giaVe): void
    {
        $status = 'giu_cho';
        $stmt = $this->conn->prepare(
            'INSERT INTO chi_tiet_ve (ma_dat_ve_id, ma_chuyen, ma_ghe, gia_ve, trang_thai)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiids', $bookingId, $tripId, $seatId, $giaVe, $status);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Tim don dat ve theo ma dat ve (kem thong tin thanh toan)
     */
    public function timDonTheoMa(string $maDatVe): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT d.ma_dat_ve_id, d.ma_dat_ve, d.ma_nguoi_dung, d.ten_khach_hang, d.sdt_khach_hang,
                    d.email_khach_hang, d.tong_tien, d.thanh_tien, d.trang_thai, d.trang_thai AS trang_thai_don,
                    d.ghi_chu, d.ngay_dat,
                    t.ma_thanh_toan, t.ma_giao_dich, t.phuong_thuc, t.trang_thai AS payment_status,
                    t.trang_thai AS trang_thai_thanh_toan, t.so_tien, t.ghi_chu, t.ghi_chu AS ghi_chu_thanh_toan,
                    t.thoi_gian AS ngay_thanh_toan
             FROM dat_ve d
             LEFT JOIN thanh_toan t ON t.ma_dat_ve_id = d.ma_dat_ve_id
             WHERE d.ma_dat_ve = ?
             ORDER BY t.ma_thanh_toan DESC
             LIMIT 1"
        );
        $stmt->bind_param('s', $maDatVe);
        $stmt->execute();
        $donHang = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $donHang ?: null;
    }

    /**
     * Lay lich su dat ve cua khach hang (theo ma_nguoi_dung hoac email)
     */
    public function layLichSuTheoUser(int $userId, string $email = ''): array
    {
        $stmt = $this->conn->prepare(
            'SELECT ma_dat_ve_id, ma_dat_ve, ten_khach_hang, sdt_khach_hang, email_khach_hang,
                    tong_tien, thanh_tien, trang_thai, ghi_chu, ngay_dat
             FROM dat_ve
             WHERE ma_nguoi_dung = ? OR (email_khach_hang = ? AND email_khach_hang <> "")
             ORDER BY ngay_dat DESC, ma_dat_ve_id DESC'
        );
        $stmt->bind_param('is', $userId, $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * Cap nhat trang thai don dat ve
     */
    public function capNhatTrangThaiDon(int $maDatVeId, string $trangThai): bool
    {
        $stmt = $this->conn->prepare('UPDATE dat_ve SET trang_thai = ? WHERE ma_dat_ve_id = ?');
        $stmt->bind_param('si', $trangThai, $maDatVeId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

}
