<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/fleet/fleet.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
