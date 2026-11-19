<?php

$host = getenv('WEBSITE_DB_HOST') ?: '127.0.0.1';
$username = getenv('WEBSITE_DB_USER') ?: 'root';
$password = getenv('WEBSITE_DB_PASSWORD') ?: '';
$database = getenv('WEBSITE_DB_NAME') ?: 'website_tainguyen';

if (!isset($conn) || !($conn instanceof mysqli)) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli($host, $username, $password, $database);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $error) {
        $conn = null;
        error_log('Database connection failed: ' . $error->getMessage());
    }
}
