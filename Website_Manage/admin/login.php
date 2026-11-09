<?php
/*
 * Chuyển yêu cầu đăng nhập khu quản lý đến trang đăng nhập chung
 * ở website khách hàng với chế độ area=staff.
 */

header('Location: ../../Website_Customer/frontend/login.php?area=staff', true, 302);
exit;
