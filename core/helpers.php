<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Builds absolute filesystem paths from the project root.
function root_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

// Simple flash-message helper: pass a message to set, omit it to consume.
function flash(string $key, ?string $message = null): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return '';
    }

    $value = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);

    return (string) $value;
}

// Validates HTML date input values before DateTime comparisons are made.
function is_valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}
