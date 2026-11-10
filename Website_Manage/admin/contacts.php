<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/support/contacts.php' . ($query === '' ? '' : '?' . $query), true, 307);
exit;
