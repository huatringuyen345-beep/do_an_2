<?php

class ThanhToanModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function layPhuongThuc(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT ma_phuong_thuc, ten_phuong_thuc, mo_ta, icon
             FROM phuong_thuc_thanh_toan
             WHERE trang_thai = 'hoat_dong'
             ORDER BY ma_phuong_thuc"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[$row['ma_phuong_thuc']] = [
                'ten' => $row['ten_phuong_thuc'],
                'mota' => $row['mo_ta'] ?? '',
                'icon' => $row['icon'] ?? '',
            ];
        }
        $stmt->close();

        return $payments;
    }

    public function taoThanhToan(int $bookingId, string $transactionCode, string $method, float $amount, string $note): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO thanh_toan (ma_dat_ve_id, ma_giao_dich, phuong_thuc, so_tien, trang_thai, ghi_chu)
             VALUES (?, ?, ?, ?, 'cho_thanh_toan', ?)"
        );
        $stmt->bind_param('issds', $bookingId, $transactionCode, $method, $amount, $note);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function capNhatThanhToan(int $bookingId, string $transactionCode, string $method, float $amount, string $status, string $note): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE thanh_toan
             SET ma_giao_dich = ?, phuong_thuc = ?, so_tien = ?, trang_thai = ?, ghi_chu = ?, thoi_gian = NOW()
             WHERE ma_dat_ve_id = ?
             ORDER BY ma_thanh_toan DESC
             LIMIT 1'
        );
        $stmt->bind_param('ssdssi', $transactionCode, $method, $amount, $status, $note, $bookingId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function ghiLichSu(int $bookingId, string $transactionCode, string $method, string $oldStatus, string $newStatus, float $amount, string $note): bool
    {
        $check = $this->conn->query("SHOW TABLES LIKE 'lich_su_thanh_toan'");
        if (!$check || $check->num_rows === 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO lich_su_thanh_toan (ma_dat_ve_id, ma_giao_dich, phuong_thuc, trang_thai_cu, trang_thai_moi, so_tien, ghi_chu)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issssds', $bookingId, $transactionCode, $method, $oldStatus, $newStatus, $amount, $note);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function phuongThucLuu(string $method): string
    {
        $aliases = [
            'quay' => ['quay', 'tien_mat'],
            'ck' => ['ck', 'chuyen_khoan'],
            'the' => ['the', 'the_ngan_hang'],
            'vi' => ['vi', 'vi_dien_tu'],
        ];
        $candidates = $aliases[$method] ?? [$method];
        $result = $this->conn->query("SHOW COLUMNS FROM `thanh_toan` LIKE 'phuong_thuc'");
        $column = $result ? $result->fetch_assoc() : null;
        $columnType = (string)($column['Type'] ?? '');

        if (str_starts_with(strtolower($columnType), 'enum(')) {
            foreach ($candidates as $candidate) {
                if (str_contains($columnType, "'" . $candidate . "'")) {
                    return $candidate;
                }
            }
        }

        return $method;
    }
}
