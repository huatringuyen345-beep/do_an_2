<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/users/profile.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
