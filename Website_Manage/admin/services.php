<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/services/services.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
