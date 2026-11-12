<?php

class NguoiDungModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function findForAuthentication($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT nd.ma_nguoi_dung, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.dia_chi, nd.mat_khau, nd.trang_thai, vt.ten_vai_tro
             FROM nguoi_dung nd
             INNER JOIN vai_tro vt ON vt.ma_vai_tro = nd.ma_vai_tro
             WHERE nd.email = ? AND vt.ten_vai_tro IN ('quan_tri', 'nhan_vien', 'khach_hang')
             LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $nguoiDung = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $nguoiDung ?: null;
    }

    public function updateLegacyPassword($userId, $oldPasswordHash, $newPasswordHash)
    {
        $stmt = $this->conn->prepare(
            'UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ? AND mat_khau = ?'
        );
        $stmt->bind_param('sis', $newPasswordHash, $userId, $oldPasswordHash);
        $stmt->execute();
        $stmt->close();
    }

    public function emailExists($email)
    {
        $stmt = $this->conn->prepare(
            'SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function findRoleId($roleName)
    {
        $stmt = $this->conn->prepare(
            'SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = ? LIMIT 1'
        );
        $stmt->bind_param('s', $roleName);
        $stmt->execute();
        $role = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $role ? (int) $role['ma_vai_tro'] : null;
    }

    public function createCustomer($roleId, $name, $email, $phone, $passwordHash)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO nguoi_dung (ma_vai_tro, ho_ten, email, so_dien_thoai, mat_khau, trang_thai)
             VALUES (?, ?, ?, ?, ?, "hoat_dong")'
        );
        $stmt->bind_param('issss', $roleId, $name, $email, $phone, $passwordHash);
        $stmt->execute();
        $userId = (int) $this->conn->insert_id;
        $stmt->close();

        return $userId;
    }

    public function updateProfile($userId, $name, $phone, $address)
    {
        $stmt = $this->conn->prepare(
            'UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE ma_nguoi_dung = ?'
        );
        $stmt->bind_param('sssi', $name, $phone, $address, $userId);
        $stmt->execute();
        $stmt->close();
    }

    public function findPasswordHash($userId)
    {
        $stmt = $this->conn->prepare(
            'SELECT mat_khau FROM nguoi_dung WHERE ma_nguoi_dung = ? LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $nguoiDung = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $nguoiDung ? $nguoiDung['mat_khau'] : null;
    }

    public function updatePasswordHash($userId, $passwordHash)
    {
        $stmt = $this->conn->prepare(
            'UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?'
        );
        $stmt->bind_param('si', $passwordHash, $userId);
        $stmt->execute();
        $stmt->close();
    }
}
