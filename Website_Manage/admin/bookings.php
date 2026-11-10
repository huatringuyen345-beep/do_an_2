<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/bookings/bookings.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
