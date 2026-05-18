<?php
declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'test';
$dbName = 'jingleworks_db';

$mysqli = null;
$dbError = null;

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, 3306);

if (!$connection || $connection->connect_errno) {
    $dbError = 'Database is not available right now. Please try again later.';
} else {
    $mysqli = $connection;
    $mysqli->set_charset('utf8mb4');
}
