<?php

require_once __DIR__ . '/../backend/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['account']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Vui lòng đăng nhập để xem tình trạng ghế.']);
    exit;
}

$route = $_GET['route'] ?? '';
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$type = $_GET['type'] ?? '';
if (!is_string($route) || !is_string($date) || !is_string($time)
    || !is_string($type) || !in_array($type, ['ghe', 'giuong'], true)
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
    || !preg_match('/^\d{2}:\d{2}$/', $time)) {
    http_response_code(400);
    echo json_encode(['error' => 'Thông tin chuyến xe không hợp lệ.']);
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(503);
    echo json_encode(['error' => 'Không thể kết nối cơ sở dữ liệu.']);
    exit;
}

try {
    require_once __DIR__ . '/../backend/models/DatVeModel.php';
    $model = new DatVeModel($conn);
    $availability = $model->layThongTinChoNgoi($route, $date, $time, $type);
    echo json_encode($availability, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Seat availability lookup failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Không thể tải tình trạng ghế. Vui lòng thử lại.']);
}
