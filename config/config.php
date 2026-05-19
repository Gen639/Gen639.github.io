<?php
declare(strict_types=1);

final class Config
{
    private static ?array $env = null;

    public static function get(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if (!is_string($value) || $value === '') {
            $env = self::env();
            $value = $env[$key] ?? null;
        }

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function database(): array
    {
        return [
            'host' => self::get('DB_HOST', '127.0.0.1'),
            'user' => self::get('DB_USERNAME', 'root'),
            'password' => self::get('DB_PASSWORD', ''),
            'name' => self::get('DB_DATABASE', 'jingleworks_db'),
            'port' => (int) self::get('DB_PORT', '3306'),
        ];
    }

    private static function env(): array
    {
        if (self::$env !== null) {
            return self::$env;
        }

        $path = dirname(__DIR__) . '/.env';
        $parsed = is_file($path) ? parse_ini_file($path, false, INI_SCANNER_RAW) : [];
        self::$env = is_array($parsed) ? $parsed : [];

        return self::$env;
    }
}
