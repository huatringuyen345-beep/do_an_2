<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/trips/trips.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
