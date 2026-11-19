<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this installer from the command line.\n");
}

$host = getenv('WEBSITE_DB_HOST') ?: '127.0.0.1';
$username = getenv('WEBSITE_DB_USER') ?: 'root';
$password = getenv('WEBSITE_DB_PASSWORD') ?: '';
$database = getenv('WEBSITE_DB_NAME') ?: 'website_tainguyen';
if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    throw new InvalidArgumentException('WEBSITE_DB_NAME may contain only letters, numbers, and underscores.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli($host, $username, $password);
$connection->set_charset('utf8mb4');
$connection->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$connection->select_db($database);

$tables = $connection->query("SHOW TABLES LIKE 'vai_tro'");
if (!$tables || $tables->num_rows === 0) {
    $dumpPath = __DIR__ . '/../../../database/website_tainguyen.sql';
    $dump = file_get_contents($dumpPath);
    if ($dump === false) {
        throw new RuntimeException('Could not read the initial database dump.');
    }

    $connection->multi_query($dump);
    do {
        if ($result = $connection->store_result()) {
            $result->free();
        }
        if (!$connection->more_results()) {
            break;
        }
    } while ($connection->next_result());
    if ($connection->errno) {
        throw new RuntimeException('Database import failed: ' . $connection->error);
    }
}

$migrationPath = __DIR__ . '/../../../database/migrations/008_recruitment_applications.sql';
$migration = file_get_contents($migrationPath);
if ($migration === false) {
    throw new RuntimeException('Could not read recruitment applications migration.');
}
$connection->multi_query($migration);
while ($connection->more_results() && $connection->next_result()) {
    if ($result = $connection->store_result()) {
        $result->free();
    }
}
if ($connection->errno) {
    throw new RuntimeException('Database migration failed: ' . $connection->error);
}

echo "Database installed and migrations applied.\n";
