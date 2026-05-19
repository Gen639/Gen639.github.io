<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

final class Database
{
    public static function connection(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $config = Config::database();
        $connection = new mysqli(
            $config['host'],
            $config['user'],
            $config['password'],
            $config['name'],
            $config['port']
        );
        $connection->set_charset('utf8mb4');

        return $connection;
    }
}
