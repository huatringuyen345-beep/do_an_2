<?php

function customer_auth_next($page)
{
    $allowedPages = ['DatVe.php', 'DatDichVu.php', 'LienHe.php', 'TuyenDung.php', 'TaiKhoan.php'];

    return is_string($page) && in_array($page, $allowedPages, true) ? $page : 'TaiKhoan.php';
}

function customer_require_account($next)
{
    if (!empty($_SESSION['account']['id'])) {
        return;
    }

    $_SESSION['account_flash'] = ['error' => 'Vui lòng đăng nhập hoặc tạo tài khoản để thực hiện thao tác này.'];
    header('Location: Login.php?next=' . rawurlencode(customer_auth_next($next)), true, 303);
    exit;
}
