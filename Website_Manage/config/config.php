<?php
$servername = getenv('WEBSITE_DB_HOST') ?: '127.0.0.1';
$username = getenv('WEBSITE_DB_USER') ?: 'root';
$password = getenv('WEBSITE_DB_PASSWORD') ?: '';
$dbname = getenv('WEBSITE_DB_NAME') ?: 'website_tainguyen';

if (!isset($conn) || !($conn instanceof mysqli)) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset('utf8mb4');
    } catch (Throwable $e) {
        // Nếu DB chưa tồn tại trên MySQL, thử tạo và nạp schema trong thư mục database.
        try {
            $temp = new mysqli($servername, $username, $password);
            $temp->set_charset('utf8mb4');
            $temp->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $temp->select_db($dbname);
            $chk = $temp->query("SHOW TABLES LIKE 'vai_tro'");
            if ($chk && $chk->num_rows === 0) {
                $sqlFile = __DIR__ . '/../database/website-tainguyen.sql';
                if (!file_exists($sqlFile)) {
                    throw new RuntimeException('Database schema file was not found: ' . $sqlFile);
                }
                $sql = file_get_contents($sqlFile);
                if ($sql === false) {
                    throw new RuntimeException('Database schema file could not be read: ' . $sqlFile);
                }
                $temp->multi_query($sql);
                while ($temp->more_results()) {
                    $temp->next_result();
                }
            }
            $conn = $temp;
        } catch (Throwable $ex) {
            $conn = null;
            error_log("Database connection error: " . $ex->getMessage());
        }
    }
}
